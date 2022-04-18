<?php
/*
        数据库操作对象类 创建于2020-05-10
*/

define("db_username", "root");
define("db_password", "@Ktn2b");
define("db_database", "pingpang");
define("db_host", "127.0.0.1");
session_start();

//用户类
class user
{
    var $conn;

    //构造函数
    public function __construct()
    {
        $this->conn = new mysqli(db_host, db_username, db_password, db_database);
    }

    //析构函数
    public function __destruct()
    {
        mysqli_close($this->conn);
    }


    /*
            @创建新用户
            @验证码错误[-1],用户名或密码格式不符合要求[-2],用户名已被注册[-3],未知的错误[-4],注册成功[1]
        */
    public function registerUser($stu_number, $username, $password, $group_id, $sex, $qq_number)
    {
        $username = $this->conn->real_escape_string($username); //防止SQL注入
        $password = $this->conn->real_escape_string($password); //防止SQL注入
        $stu_number = $this->conn->real_escape_string($stu_number); //防止SQL注入
        $group_id = intval($group_id); //参数化
        $sex = intval($sex); //参数化
        $qq_number = $this->conn->real_escape_string($qq_number); //QQ号

        //检查该学号选手是否已被注册
        $result = $this->conn->query("SELECT * FROM `users` WHERE `stu_number`='$stu_number';");
        if ($result->num_rows > 0) {
            return Tool::makeResponseTpl('error', '添加失败, 该学号选手已经存在!');
        }

        //检查该选手名次是否与已有选手名次冲突
        $result = $this->conn->query("SELECT `user_id` FROM `users` WHERE `group_id` = '$group_id' AND `rank_num` = '$rank_num' AND `rank_num`!='0'");
        if ($result->num_rows > 0){
            return Tool::makeResponseTpl('error', '添加失败，待添加选手名次与该院已有选手冲突！');
        }

        //进行参数合法性校验
        if ($stu_number == "" || $username == "" || $password == "" || !in_array($sex, [0, 1]) || $qq_number == "")
        {
            return Tool::makeResponseTpl('error', '添加失败，部分用户参数无效，请检查!');
        }

        //判断学院组是否存在
        $result = $this->conn->query("SELECT `group_name` FROM `groups` WHERE `group_id` = '$group_id'");

        if ($result->num_rows == 0){
            return Tool::makeResponseTpl('error', '添加失败，选定了不存在的学院组!');
        }

        //获取学院组名称
        $group_name = $result->fetch_assoc()['group_name'];

        //自动生成选手id
        $result = $this->conn->query("SELECT MAX(`user_id`) + 1 AS `new_user_id` FROM `users`");
        if ($result->num_rows == 0){
            return Tool::makeResponseTpl('error', '添加失败，生成选手id时出现错误!');
        }

        $new_user_id = $result->fetch_assoc()['new_user_id'];

        //写入数据库
        if (!$this->conn->query("INSERT INTO `users` (`user_id`, `stu_number`, `username`, `password`, `status`, `role_id`, `group_id`, `sex`, `score`, `qq_number`) VALUES ('$new_user_id','$stu_number','$username','$password','0','1','$group_id','$sex', '0', '{$qq_number}')")) {
            return Tool::makeResponseTpl('error', '添加失败，写入数据库时发生未知的错误!', );
        }

        //更新缓存
        $Cache = new Redis_query();
        $Cache->initScoreInfo();
        $Cache->updateRankNum();

        //待返回数据值
        $sex_name = ['女', '男'];
//        $rank_num_name = ['未上榜', '一', '二', '三', '四', '五', '六', '七', '八'];

        $response_data = array(
                'user_id' => $new_user_id,
                'password' => $password,
                'stu_number' => $stu_number,
                'username' => $username,
                'group_name' => $group_name,
                'sex' => $sex_name[$sex],
            );

        return Tool::makeResponseTpl('success', '添加新用户成功!<br>生成的选手编号为：<span style="color: red">'. $new_user_id .'</span>', $response_data);
    }


    //设置用户密码
    public function changeUserPassword($user_id,$new_password)
    {
        $user_id=intval($user_id);
        $new_password=$this->conn->real_escape_string($new_password);
        if(mysqli_query($this->conn,"UPDATE `users` SET `password`='$new_password' WHERE `user_id`='$user_id';"))
        {
            return true;
        }
        return false;
    }


    //判断用户密码是否正确
    public function checkUserPassword($user_id,$check_pwd)
    {
        $user_id=intval($user_id);
        $check_pwd=$this->conn->real_escape_string($check_pwd);
        $result=$this->conn->query("SELECT `password` FROM `users` WHERE `user_id`='$user_id';");
        if($result->num_rows>0)
        {
            $row=$result->fetch_assoc();
            if($check_pwd==$row["password"])
            {
                return true;
            }
        }
        return false;
    }

    //用户注销登录
    public function loginOut()
    {
        session_destroy();
    }


    /*
            @用户登录验证
            @return json数据格式
     */
    public function checkLogin($stu_number, $password, $code)
    {
        if (strtolower($code) != strtolower($_SESSION["code"])) {
            $_SESSION["code"] = rand(1000, 9999);
            return Tool::makeResponseTpl('error', '验证码输入错误!');
        }

        if ($stu_number == "" || $password == "") {
            $_SESSION["code"] = rand(1000, 9999);
            return Tool::makeResponseTpl('error', '学号或密码错误!');
        }

        $stu_number = $this->conn->real_escape_string($stu_number); //防止SQL注入
        $result = $this->conn->query("SELECT * FROM `users` WHERE `stu_number`='$stu_number'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row["password"] == $password) {
                if ($row["status"] == 2) {
                    return Tool::makeResponseTpl('error', '该用户已被管理员锁定,无法登录!');
                } else {
                    $this->saveUserLoginInfo($row["user_id"], $row["username"], $row["group_id"], $row["role_id"]);
//                    $this->LogUserLogin($row["user_id"],$_SERVER["REMOTE_ADDR"]);
                    return Tool::makeResponseTpl('success', '登陆成功,页面即将跳转!');
                }
            } else {
                $_SESSION["code"] = rand(1000, 9999);
                return Tool::makeResponseTpl('error', '学号或密码错误！');
            }
        }
        $_SESSION["code"] = rand(1000, 9999);
        return Tool::makeResponseTpl('error', '学号或密码错误！');
    }


    //将登录用户基本信息保存到Session会话
    public function saveUserLoginInfo($uid, $username, $group_id, $role_id)
    {
        $_SESSION["user_id"] = $uid;
        $_SESSION["username"] = htmlentities($username); //HTML转义用户名,防止XSS攻击
        $_SESSION["group_id"] = $group_id;
        $_SESSION["role_id"] = $role_id;
    }


    /*
            @修改用户权限
            @学生[1],社团用户[2],学校管理员[3]
     */
    public function changeUserRole($user_id,$new_role_id)
    {
        $user_id=intval($user_id);
        $new_role_id=intval($new_role_id);
        if(mysqli_query($this->conn,"UPDATE `users` SET `role_id`='$new_role_id' WHERE `user_id`='$user_id';"))
        {
            return true;
        }
        return false;
    }


    /*
        @判断用户是否允许发帖(未被禁止发帖)
        @允许发帖--true,禁止发帖--false
    */
    public function judgeUserPostStatus($user_id)
    {
        $result = $this->conn->query("SELECT `status` FROM `users` WHERE `user_id`='$user_id'");
        if($result->num_rows>0)
        {
            $row=$result->fetch_assoc();
            if($row["status"]==1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        return false;
    }

    //判断用户是否拥有该路由访问权限
    public function JudgeUserAccessPermission()
    {
    }

    /*
        @设置用户账户权限
        @正常-[1] 被禁言[0] 被封禁[-1]
    */
    public function setUserStatus($user_id,$user_status)
    {
        $user_id=intval($user_id);
        $user_status=intval($user_status);
        if(mysqli_query($this->conn,"UPDATE `users` SET `status`='$user_status' WHERE `user_id`='$user_id'"))
        {
            return true;
        }
        return false;
    }


    //记录用户本次登录信息
    public function LogUserLogin($user_id,$ip)
    {
        $time=time();
        if(mysqli_query($this->conn,"INSERT INTO `login_log` (`user_id`,`login_time`,`login_ip`) VALUES ('$user_id','$time','$ip');"))
        {
            return true;
        }
        return false;
    }

    //获取用户上次登录时间
    public function getUserLastLoginTime($user_id,&$last_login_time)
    {
        $result=$this->conn->query("SELECT `login_time` FROM `login_log` WHERE `user_id`=$user_id ORDER BY `log_id` DESC LIMIT 1");
        if($result->num_rows>0)
        {
            $row=$result->fetch_assoc();
            $last_login_time=date("Y-m-d H:i",$row["login_time"]);
        }
        return false;
    }

    //根据ID获取用户账户状态
    public function getUserStatusByUserId($user_id,&$status)
    {
        $result=$this->conn->query("SELECT `status` FROM `users` WHERE `user_id`='$user_id'");
        if($result->num_rows>0)
        {
            $row=$result->fetch_assoc();
            $status=$row["status"];
            return true;
        }
        return false;
    }

    //根据ID获取用户名
    public function getUsernameByUserId($user_id,&$username)
    {
        $result=$this->conn->query("SELECT `username` FROM `users` WHERE `user_id`='$user_id'");
        if($result->num_rows>0)
        {
            $row=$result->fetch_assoc();
            $username=$row["username"];
            return true;
        }
        return false;
    }

    //根据ID获取用户注册时间
    public function getUserRegtimeByUserId($user_id,&$regtime)
    {
        $result=$this->conn->query("SELECT `register_time` FROM `users` WHERE `user_id`='$user_id'");
        if($result->num_rows>0)
        {
            $row=$result->fetch_assoc();
            $regtime=date("Y-m-d H:i",$row["register_time"]);
        }
        return false;
    }

    //根据ID获取用户角色类型ID
    public function getUserRoleByUserId($user_id,&$role_id)
    {
        $result=$this->conn->query("SELECT `role_id` FROM `users` WHERE `user_id`='$user_id'");
        if($result->num_rows>0)
        {
            $row=$result->fetch_assoc();
            $role_id=$row["role_id"];
        }
        return false;
    }

    //管理后台获取所有注册用户信息列表(除开当前管理员信息)
    //当group_id为-1时表示获取全部用户信息
    public function getAllUserList($group_id, &$total_num,&$user_info_list)
    {
        $total_num=0;
        $user_info_list = [];

        $group_id = intval($group_id);
        if ($group_id!=-1) {
            $sql_sentence = "AND `group_id` = '$group_id'";
        }
        else{
            $sql_sentence = "";
        }

        $result=$this->conn->query("SELECT `score`, `user_id`, `stu_number`, `username`, `status`, (SELECT `group_name` FROM `groups` AS b WHERE a.group_id = b.group_id) AS `group_name`, `sex` FROM `users` AS a WHERE `role_id` = '1' ". $sql_sentence ." ORDER BY `user_id` ASC");
        if($result->num_rows>0)
        {
            $user_info_list = $result->fetch_all(MYSQLI_ASSOC);
            $total_num = count($user_info_list);
            return true;
        }
        return false;
    }

    //查询用户信息
    public function getUserInfo($user_id)
    {
        $user_id = intval($user_id);
        $result = $this->conn->query("SELECT `email_address`, `email_status`, `stu_number`, `username`, `sex`, (SELECT `group_name` FROM `groups` AS b WHERE a.group_id = b.group_id) AS `group_name` FROM `users` AS a WHERE `user_id` = '$user_id' LIMIT 1");
        $row = array();
        if ($result->num_rows > 0){
            $row = $result->fetch_array(MYSQLI_ASSOC);
        }

        return $row;
    }

    //获取本周剩余挑战次数 第一届擂台赛方法
    /*
     * 先判断本周是否挑战过，若挑战过就判断挑战类型，若有未上榜挑战，则根据结果来判断，若为榜上挑战，则直接计算；
     * 若未挑战过，则根据当前排名来计算剩余次数
    */
    public function getUserRestTimes($user_id)
    {
        $weekBegin_time = date("Y-m-d H:i:s", Tool::getWeekBeginTimeStamp());
        $weekEnd_time = date("Y-m-d H:i:s", Tool::getWeekEndTimeStamp());
        $user_id = intval($user_id);
        $result = $this->conn->query("SELECT * FROM `tasks` WHERE `founder_id` = '$user_id' AND (`end_time` >= '$weekBegin_time' AND `end_time` <= '$weekEnd_time') AND (`error_code` != '3' or `error_code` is null)");

        $rest_times = 0; //剩余次数

        if ($result->num_rows > 0)
        {
            //若本周发起过挑战
            $task_list = $result->fetch_all(MYSQLI_ASSOC); //挑战记录列表
            $inRank_win_times = 0; //榜上比赛获胜次数
            $inRank_times = 0; //榜上比赛挑战次数
            $outRank_win_times = 0; //榜下挑战获胜次数
            $outRank_times = 0; //榜下挑战总次数

            foreach ($task_list as $row)
            {
                //榜内挑战
                if ($row['type'] == 1)
                {
                    $inRank_times++;

                    if ($row['winner_id'] == $row['founder_id'])
                    {
                        $inRank_win_times++;
                    }
                }
                //榜外挑战
                else
                {
                    $outRank_times++;

                    if ($row['winner_id'] == $row['founder_id'])
                    {
                        $outRank_win_times++;
                    }
                }
            }

            //判断是否被踢下榜单的
            if ($this->getUserNowRankNum($user_id) > 0)
            {
                $rest_times = 3;
            }
            else
            {
                $rest_times = 2;
            }

            if ($outRank_times > 0)
            {
                if ($outRank_win_times == 1)
                {
                    $rest_times = 1;
                }
                else
                {
                    $rest_times = 0;
                }
            }

            if ($inRank_times > 0)
            {
                //如果榜内发起挑战后获胜，则本周机会用完
                if ($inRank_win_times > 0)
                {
                    $rest_times = 0;
                }
                $rest_times = max(0, $rest_times - $inRank_times);
            }


        }
        else
        {
            //若未挑战过
            $user_nowRankNum = $this->getUserNowRankNum($user_id);

            if ($user_nowRankNum == 0)
            {
                $rest_times = 1;
            }
            else
            {
                $rest_times = 3;
            }
        }

        return $rest_times;
    }

    //获取选手当前排名
    public function getUserNowRankNum($user_id)
    {
        $cache = new Redis_query();
        return $cache->getUserRankNum($user_id);
    }

    //获取用户当前是否有未完成比赛
    public function HasUnfinishedTask($user_id)
    {
        $weekBegin_time = date("Y-m-d H:i:s", Tool::getWeekBeginTimeStamp());
        $weekEnd_time = date("Y-m-d H:i:s", Tool::getWeekEndTimeStamp());
        $user_id = intval($user_id);
        $result = $this->conn->query("SELECT `task_id` FROM `tasks` WHERE (`founder_id` = '$user_id' OR `target_id` = '$user_id') AND `status` = '1' LIMIT 1");

        if ($result->num_rows > 0)
        {
            return true;
        }

        return false;
    }

    /*
     * @method 用户发起挑战
     * @description 先判断是否存在该对手用户，再用户是否剩余比赛次数,
     * 再判断选手是否为自己本人，是否为统一院级同一性别，再判断双方用户当前是否处于比赛状态，
     * 再判断本周是否挑战过该选手, 再判断是否符合挑战排名级别资格
     * @return array ('status' => 'success/error', 'message' => '')
     */
//    TODO：对于比赛上报截止时间超过2021年5月13日凌晨截止的处理
    public function MakeChallenge($founder_id, $target_id)
    {
        //挑战发起于2021年5月13日发起的比赛，不能发起
        $closeDate = "2021-05-15 00:00:00";
        $time = time();
        if ($time > strtotime($closeDate) - 24 * 2 * 60 * 60) {
            return Tool::makeResponseTpl('error', '<span style="color: red">本学期的挑战赛已截止，敬请关注决赛信息!</span>');
        }

        //判断本周挑战时间段是否结束
//        $now_time = time();
//        if ($now_time >= Tool::getWeekBeginTimeStamp() + 6 * 24 * 60 * 60 + 17 * 60 * 60 && $now_time < Tool::getWeekEndTimeStamp())
//        {
//            return Tool::makeResponseTpl('error', '本周挑战时间段已结束<br><span style="color: red">下轮挑战将于'.date("Y-m-d H:i:s", Tool::getWeekEndTimeStamp() + 1).'开启</span>');
//        }

        //判断被挑战用户是否存在
        if (!$this->UserExists($target_id))
        {
            return Tool::makeResponseTpl('error', '被挑战用户不存在！');
        }

        //判断用户是否有剩余次数
//        if (!$this->getUserRestTimes($founder_id))
//        {
//            return Tool::makeResponseTpl('error', '抱歉，您本周发起挑战的次数已用完！');
//        }

        //判断是否为挑战本人
        if ($founder_id == $target_id)
        {
            return Tool::makeResponseTpl('error', '抱歉，您不能选择挑战自己哦！');
        }

        //判断是否为同一性别组
        if (!$this->InSameSexGroup($founder_id, $target_id))
        {
            return Tool::makeResponseTpl('error', '不能跨学院组或跨性别进行挑战哦！');
        }

        //自己是否有未完成比赛
        if ($this->HasUnfinishedTask($founder_id))
        {
            return Tool::makeResponseTpl('error', '当前您还有未完成的比赛，暂时不能发起挑战哦！');
        }

        //判断对手账户状态是否正常
        $this->getUserStatusByUserId($target_id, $target_status);

        if ($target_status != 1)
        {
            return Tool::makeResponseTpl('error', '对方当前账户状态异常，暂时无法挑战！');
        }

        //判断对手是否有未完成比赛
        if ($this->HasUnfinishedTask($target_id))
        {
            return Tool::makeResponseTpl('error', '对方当前还有未完成的比赛，暂时不能挑战哦！');
        }

        //判断本周是否已挑战过该选手
//        if ($this->HasChallengeSomeone($founder_id, $target_id))
//        {
//            return Tool::makeResponseTpl('error', '本周您已挑战过该选手，不能再次挑战！');
//        }

        //判断挑战名次差距是否符合比赛规则
        if (!$this->ChallengeValidRankNum($founder_id, $target_id))
        {
            return Tool::makeResponseTpl('error', '该选手与您的名次差距不符合比赛的挑战规则！');
        }

//        return Tool::makeResponseTpl('success', '发起挑战测试成功！');

        //写入比赛数据
        $founder_rank_num = $this->getUserNowRankNum($founder_id);
        $target_rank_num = $this->getUserNowRankNum($target_id);
        //比赛类型 1-榜内挑战 2-榜外挑战
        $task_type = $founder_rank_num > 0 ? 1 : 0;
        //记录挑战起始时间
        $now_time = time();
        $task_beginTime = date('Y-m-d H:i:s', $now_time);
        //记录挑战结束时间(7天后)
        $task_endtime_stamp = $now_time + 7 * 24 * 60 * 60;

        //若超过最迟上报时间超过截止日期，则取截止时间
        $task_endtime_stamp = min($task_endtime_stamp, strtotime($closeDate));

        $task_endTime = date('Y-m-d H:i:s', $task_endtime_stamp);
        //双方比赛确认码
        $founder_check_code = Tool::CreateTaskCheckCode();
        $target_check_code = Tool::CreateTaskCheckCode();
        //写入数据库
        $sql_status = $this->conn->query("INSERT INTO `tasks` (`founder_id`, `target_id`, `founder_rank_num_before`, `target_rank_num_before`, `type`, `status`,
                                    `begin_time`, `end_time`, `founder_check_code`, `target_check_code`)VALUES ('$founder_id', '$target_id', '$founder_rank_num', '$target_rank_num', '$task_type', '1', '$task_beginTime', '$task_endTime', '$founder_check_code', '$target_check_code');");

        if (!$sql_status)
        {
            return Tool::makeResponseTpl('error', '服务器发生了未知的错误');
        }

        //发送提示邮件
        Email::sendFoundTaskMail($founder_id, $target_id);

        return Tool::makeResponseTpl('success', '挑战发起成功，请等待对方选手进行确认！<br><span style="color: red">【需在截止时间前进行比赛并上报结果】</span>');

    }

    //判断用户是否上榜（男生榜单前48名，女生榜单前16名）
    public function isUserInRank($user_id)
    {
        $cache = new Redis_query();
        $sex = $cache->getUserSex($user_id);
        $max_rankNum = [16, 48];
        $rankNum_now = $cache->getUserRankNum($user_id);
        return $rankNum_now > 0 && $rankNum_now <= $max_rankNum[$sex];
    }

    //判断用户是否存在
    public function UserExists($user_id)
    {
        $user_id = intval($user_id);
        $result = $this->conn->query("SELECT `user_id` FROM `users` WHERE `user_id` = '$user_id' LIMIT 1");

        return $result->num_rows > 0;
    }

    //获取指定用户当前开幕赛信息
    public function getStartInfo($user_id)
    {
        $user_id = intval($user_id);
        $query = $this->conn->query("SELECT * FROM `divide_groups` WHERE `a_id` = '{$user_id}' OR `b_id` = '{$user_id}'");
        if ($query->num_rows > 0){
            $row = $query->fetch_assoc();
            if ($row['a_id'] == $user_id){
                $another_id = $row['b_id'];
            }
            else{
                $another_id = $row['a_id'];
            }
            return array(
                "another_id" => $another_id,
                "table_id" => $row['table_id']
            );
        }
        else{
            return null;
        }
    }

    //判断选手是否为同一性别组
    public function InSameSexGroup($userA_id, $userB_id)
    {
        $userA_id = intval($userA_id);
        $userB_id = intval($userB_id);

        //判断用户是否存在
        if (!$this->UserExists($userA_id) || !$this->UserExists($userB_id))
        {
            return false;
        }

        $result = $this->conn->query("SELECT (A.`sex` = B.`sex`) AS `in_same`  FROM `users` AS A, `users` AS B WHERE A.`user_id` = '$userA_id' AND B.`user_id` = '$userB_id' LIMIT 1;");

        return $result->fetch_assoc()['in_same'] == 1;
    }

    //判断本周是否挑战过某选手
    public function HasChallengeSomeone($founder_id, $target_id)
    {
        $weekBegin_time = date("Y-m-d H:i:s", Tool::getWeekBeginTimeStamp());
        $weekEnd_time = date("Y-m-d H:i:s", Tool::getWeekEndTimeStamp());
        $founder_id = intval($founder_id);
        $target_id = intval($target_id);
        $result = $this->conn->query("SELECT `task_id` FROM `tasks` WHERE `founder_id` = '$founder_id' AND `target_id` = '$target_id' AND `end_time` >= '$weekBegin_time' AND `end_time` <= '$weekEnd_time' AND (`error_code`!='3' or `error_code` is null) LIMIT 1;");

        return $result->num_rows > 0;
    }

    //判断是否具有按排名来挑战的资格 （未上榜选手只能挑战榜上后五名 上榜选手只能挑战前五名的选手）
    public function ChallengeValidRankNum($founder_id, $target_id)
    {
        $founder_rankNum = $this->getUserNowRankNum($founder_id);
        $target_rankNum = $this->getUserNowRankNum($target_id);

        //若未上榜，则根据男女组榜上最高名次进行判断
        $max_rankNum = [16, 48];
        $cache = new Redis_query();
        $sex = $cache->getUserSex($founder_id);

        //若为榜外挑战
        if ($founder_rankNum > $max_rankNum[$sex])
        {
           return $max_rankNum[$sex] - 5 < $target_rankNum && $target_rankNum <=  $max_rankNum[$sex];
        }

        //若为榜上挑战
        return ($target_rankNum >= $founder_rankNum - 5 && $target_rankNum < $founder_rankNum);
    }

     //获取个人的挑战记录信息
    public function getUserTaskInfo($user_id)
    {
        $result = $this->conn->query("SELECT * , (SELECT `username` FROM `users` WHERE `user_id` = T.target_id) AS `target_name`,(SELECT `username` FROM `users` WHERE `user_id` = T.founder_id) AS `founder_name` FROM `tasks` AS T WHERE `founder_id` = '$user_id' OR `target_id` = '$user_id' ORDER BY `begin_time` DESC ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //获取用户当前积分
    public function getUserScore($user_id)
    {
        $user_id = intval($user_id);
        $result = $this->conn->query("SELECT `score` FROM `users` WHERE `user_id` = '$user_id'");
        if ($result->num_rows > 0)
        {
            return $result->fetch_assoc()['score'];
        }
        return 0;
    }

    //获取指定性别组用户积分等信息
    public function getUserScoreInfoBySex($sex_type)
    {
        //校验性别类型是否有效
        if (!in_array($sex_type, [SexType::WOMAN, SexType::MAN])){
            return null;
        }

        //获取指定用户组的积分信息
        $result = $this->conn->query("SELECT `user_id`, `score` FROM `users` WHERE `sex` = '$sex_type'");

        //如果查询数据库出错，则返回空信息
        if ($result->num_rows){
             $data_list = $result->fetch_all(MYSQLI_ASSOC);
        }
        else{
            $data_list = null;
        }

        return $data_list;
    }

    //获取选手QQ号
    public function getUserQQNumber($user_id)
    {
        $query = $this->conn->query("SELECT `qq_number` FROM `users` WHERE `user_id` = '{$user_id}'");
        if ($query->num_rows > 0){
            return $query->fetch_assoc()['qq_number'];
        }

        return '';
    }

    //管理员获取所有学院挑战记录
    public function getAllTaskInfo()
    {
        $result = $this->conn->query("SELECT *, (SELECT `group_name` FROM `groups` AS c WHERE c.`group_id` = (SELECT `group_id` FROM `users` WHERE `user_id` = a.founder_id)) AS `group_name`, (SELECT `username` FROM `users` AS b WHERE a.founder_id = b.user_id) AS `founder_name` , (SELECT `username` FROM `users` AS b WHERE a.target_id = b.user_id) AS `target_name` FROM `tasks` AS a ORDER BY a.`task_id` DESC ;");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //用户接受挑战
    public function acceptChallenge($task_id, $user_id)
    {
        $task_id = intval($task_id);
        $user_id = intval($user_id);
        $sql_status = $this->conn->query("UPDATE `tasks` SET `confirm` = '1' WHERE `task_id` = '$task_id' AND `target_id` = '$user_id' AND `status` = '1'");

        if (!$sql_status)
        {
            return Tool::makeResponseTpl('error', '挑战接受失败, 发生了未知的错误!');
        }

        return Tool::makeResponseTpl('success', '挑战接受成功,请在截止时间前完成比赛并上报结果!');
    }

    //用户上报比赛挑战结果
    public function uploadResult($task_id, $winner_id, $check_code)
    {
        //转义字符，防止SQL注入
        $check_code = $this->conn->real_escape_string($check_code);
        //获取当前用户的比赛情况
        $result = $this->conn->query("SELECT * FROM `tasks` WHERE `task_id` = '$task_id' AND (`founder_id` = '$winner_id' OR `target_id` = '$winner_id') LIMIT 1");

        //若不是自己参与的比赛
        if ($result->num_rows == 0)
        {
            return Tool::makeResponseTpl('error', '你没有参与该比赛，无法上报数据！');
        }
        //获取挑战的详细信息
        $row = $result->fetch_assoc();
        //若比赛已经结束
        if ($row['status'] == 0)
        {
            return Tool::makeResponseTpl('error', '该比赛已经结束，无法上报数据！');
        }
        //判断比赛是否被确认
        if ($row['confirm'] == null)
        {
            return Tool::makeResponseTpl('error', '该比赛尚未被对手接受，无法上报数据！');
        }
        //判断校验码是否正确
        $check_code_status = false;  //验证码正误值状态

        if ($winner_id == $row['founder_id'])
        {
            $check_code_status = ($check_code == $row['target_check_code']);
            $opponent_id = $row['target_id'];   //对手id
        }
        else
        {
            $check_code_status = ($check_code == $row['founder_check_code']);
            $opponent_id = $row['founder_id'];  //对手id
        }

        if (!$check_code_status)
        {
            return Tool::makeResponseTpl('error', '无效的比赛确认码,请检查后重试!');
        }
        //进行比赛结果的更新
        mysqli_autocommit($this->conn,FALSE);
        $this->conn->query("UPDATE `tasks` SET `winner_id` = '$winner_id', `status` = '0' WHERE `task_id` = '$task_id'");

        //获胜方加上积分
        $this->updateWinnerScore($winner_id, $opponent_id, $row['founder_rank_num_before'], $row['target_rank_num_before'], $task_id);

        //获取双方新名次并记录
        $founder_rank_num_after = $this->getUserNowRankNum($row['founder_id']);
        $target_rank_num_after = $this->getUserNowRankNum($row['target_id']);

        //判断获胜者是否有晋级决赛资格
        if ($this->getUserNowRankNum($winner_id) <= 8)
        {
            if (!$this->hasFinalChance($winner_id))
            {
                $this->logFinalChance($winner_id);
            }
        }

        $this->conn->query("UPDATE `tasks` SET `founder_rank_num_after` = '$founder_rank_num_after', `target_rank_num_after` = '$target_rank_num_after' WHERE `task_id` = '$task_id'");
        if (!mysqli_commit($this->conn))
        {
            mysqli_rollback($this->conn);
            return Tool::makeResponseTpl('error', '服务端更新比赛结果时出现了错误,请稍后重试');
        }

        return Tool::makeResponseTpl('success', '比赛结果数据上报成功！');
    }

    //交换两个用户名次
    public function swapUserRankNum($userA_id, $userB_id)
    {
        $tmp_rankNum = $this->getUserNowRankNum($userA_id);
        $this->setUserRankNum($userA_id, $this->getUserNowRankNum($userB_id));
        $this->setUserRankNum($userB_id, $tmp_rankNum);
    }

    //设置用户名次
    public function setUserRankNum($user_id, $rank_num)
    {
        $user_id = intval($user_id);
        return $this->conn->query("UPDATE `users` SET `rank_num` = '$rank_num' WHERE `user_id` = '$user_id'");
    }

    //处理超时未上报或未接受的比赛
    public function processOvertimeTask()
    {
        $now_time_str = date('Y-m-d H:i:s', time());

        //选择当前超时未结束的比赛
        $result = $this->conn->query("SELECT * FROM `tasks` WHERE `status` = '1' AND `end_time`  < '$now_time_str'");

        if ($result->num_rows > 0){
            $task_list = $result->fetch_all(MYSQLI_ASSOC);
            //进行遍历判断各种情况
            foreach ($task_list as $each){
                //若属于对手未接受的情况
                if ($each['confirm'] == null){
                    $sql_status = $this->conn->query("UPDATE `tasks` SET `status` = '0', `winner_id` = `founder_id`, `error_code` = '1', `founder_rank_num_after` = `target_rank_num_before`, `target_rank_num_after` = `founder_rank_num_before`  WHERE `task_id` = '". $each['task_id']. "'");
                    if (!$sql_status) {
                        return Tool::makeResponseTpl('error', 'At condition 1: task_id = ' . $each['task_id']);
                    }
                    //!!!非常重要，将发起者视为成功!
                    $this->updateWinnerScore($each['founder_id'], $each['target_id'], $each['founder_rank_num_before'], $each['target_rank_num_before'], $each['task_id']);
                }
                //若双方超时未报
                else {
                    $sql_status = $this->conn->query("UPDATE `tasks` SET `status` = '0', `error_code` = '2', `founder_rank_num_after` = `founder_rank_num_before`, `target_rank_num_after` = `target_rank_num_before` WHERE `task_id` = '". $each['task_id'] . "'");
                    if (!$sql_status) {
                        return Tool::makeResponseTpl('error', 'At condition 2: task_id =' . $each['task_id']);
                    }
                }
            }
        }

        return Tool::makeResponseTpl('success', '数据处理成功!');
    }

    //更新获胜者积分
    public function updateWinnerScore($winner_id, $loser_id, $founder_rankNum, $target_rankNum, $task_id)
    {
        $cache = new Redis_query();
        $sex = $cache->getUserSex($winner_id);
        $get_score = $this->countSuccessScore($founder_rankNum, $target_rankNum, $sex);
        $this->conn->query("UPDATE `users` SET `score` = `score` + {$get_score} WHERE `user_id` = '$winner_id'");
        //记录用户积分领取信息
        $this->logScoreGetDetail($winner_id, $loser_id, $get_score, $cache->getUserScoreByUserId($winner_id) + $get_score);
        //及时更新Redis内的缓存，避免出现错误
        $cache->setUserScoreInfo($sex, $winner_id, $cache->getUserScoreByUserId($winner_id) + $get_score);
        //更新选手Redis中的排名信息
        $cache->updateRankNum();
    }

    //记录领取积分详情
    public function logScoreGetDetail($master_id, $target_id, $get_score, $rest_score)
    {
        $cache = new Redis_query();
        $master_rankNum = $cache->getUserRankNum($master_id);
        $target_rankNum = $cache->getUserRankNum($target_id);
        $time_stamp = date("Y-m-d H:i:s", time());
        $this->conn->query("INSERT INTO `score_details` (`master_id`, `target_id`, `get_score`, `get_time`, `master_ranknum`, `target_ranknum`, `rest_score`) VALUES ('{$master_id}', '{$target_id}', '{$get_score}', '{$time_stamp}', '{$master_rankNum}', '{$target_rankNum}', '{$rest_score}')");
    }

    //晋级过前八进行决赛报名资格记录
    public function logFinalChance($user_id)
    {
        $user_id = $this->conn->real_escape_string($user_id);
        $time = time();
        $time_stamp = date("Y-m-d H:i:s", $time);
        return $this->conn->query("INSERT INTO `final_list` (`user_id`, `time`) VALUES ('{$user_id}', '$time_stamp')");
    }

    //判断是否有决赛报名资格
    public function hasFinalChance($user_id)
    {
        $user_id = $this->conn->real_escape_string($user_id);
        $query = $this->conn->query("SELECT `user_id` FROM `final_list` WHERE `user_id` = '{$user_id}' LIMIT 1");
        return $query->num_rows == 1;
    }

    //获取积分详情
    public function getScoreDetail($user_id)
    {
        $user_id = intval($user_id);
        $query = $this->conn->query("SELECT *,(select `username` FROM `users` WHERE `user_id` = `target_id`) as `target_name` FROM `score_details` WHERE `master_id` = '{$user_id}' ORDER BY `detail_id` DESC");
        if ($query->num_rows > 0 ){
            return $query->fetch_all(MYSQLI_ASSOC);
        }
        else{
            return null;
        }
    }


    //计算比赛挑战成功获得的积分
    public function countSuccessScore($winner_rankNum, $opponent_rankNum, $sex)
    {
        //如果获胜者在榜单上（即为榜单挑战时）
        if ($this->isInRankNumByNum($winner_rankNum, $sex)) {
            $rankNum_diff = abs($winner_rankNum - $opponent_rankNum);
        }
        else {
            $rankNum_diff = abs($this->getMaxRankNumBySex($sex) + 1 - $opponent_rankNum);
        }
        return 5 + $rankNum_diff - 1;
    }

    //根据排名和性别判断选手是否在榜上
    public function isInRankNumByNum($rank_num, $sex)
    {
        return !($rank_num > $this->getMaxRankNumBySex($sex));
    }

    //获取当前性别组的榜单内限制最高名次
    public function getMaxRankNumBySex($sex)
    {
        $max_rankNum = [16, 48];
        return $max_rankNum[$sex];
    }

    //管理员取消比赛
    public function cancelTask($task_id)
    {
        $task_id = intval($task_id);

        $result = $this->conn->query("SELECT `status` FROM `tasks` WHERE `task_id` = '{$task_id}' LIMIT 1;");

        //判断比赛是否存在
        if ($result->num_rows == 0){
            return Tool::makeResponseTpl('error', '该比赛不存在!');
        }

        //判断当前比赛是否结束
        $task_status = $result->fetch_assoc()['status'];
        if ($task_status == 0){
            return Tool::makeResponseTpl('error', '该比赛已结束，不能进行取消操作！');
        }

        //进行取消操作
        $sql_status = $this->conn->query("UPDATE `tasks` SET `status` = '0', `error_code` = '3', `founder_rank_num_after` = `founder_rank_num_before`, `target_rank_num_after` = `target_rank_num_before` WHERE `task_id` = '{$task_id}'");

        if (!$sql_status){
            return Tool::makeResponseTpl('error', '服务端数据库操作时出现错误!');
        }

        return Tool::makeResponseTpl('success', '比赛取消成功!');

    }

    //获取用户是否有待确认比赛
    public function hasUnconfirmedTask($user_id)
    {
        $user_id = intval($user_id);
        $result = $this->conn->query("SELECT `task_id` FROM `tasks` WHERE `status` = '1' AND `confirm` is null AND `target_id` = '$user_id'");
        if ($result->num_rows == 0){
            return false;
        }

        return true;
    }

}


//榜单类
class Rank
{
    var $conn;

      //构造函数
    public function __construct()
    {
        $this->conn = new mysqli(db_host, db_username, db_password, db_database);
    }

    //析构函数
    public function __destruct()
    {
        mysqli_close($this->conn);
    }

    //获取个人挑战选手信息列表
    public function getTaskRankList($user_id)
    {
//        $rank_list = array();
//        $user_id = intval($user_id);
//        $result = $this->conn->query("SELECT `rank_num`, `username`, `user_id` FROM `users` WHERE `sex` = (SELECT `sex` FROM `users` WHERE`user_id` = '$user_id' LIMIT 1) AND `group_id` = (SELECT `group_id` FROM `users` WHERE `user_id` = '$user_id' LIMIT 1) AND `rank_num` > 0 ORDER BY `rank_num` ASC");
//        if ($result->num_rows > 0) {
//            $rank_list = $result->fetch_all(MYSQLI_ASSOC);
//        }
//        return $rank_list;
    }

    //获取学院组信息列表
    function getGroupsInfo()
    {
        $result = $this->conn->query("SELECT * FROM `groups` ORDER BY `group_id` ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //获取某一学院组榜单信息
    function getRankListByGroupId($group_id)
    {
        $group_id = intval($group_id);

        //榜单结果表
        $rank_list = array(
            'man' => [],
            'woman' => []
        );

        //女子组信息
        $result = $this->conn->query("SELECT * FROM `users` WHERE `group_id` = '$group_id' AND `rank_num` > 0 AND `sex` = '0' ORDER BY `rank_num` ASC");
        if ($result->num_rows > 0)
        {
            $rank_list['woman'] = $result->fetch_all(MYSQLI_ASSOC);
        }
        //男子组信息
        $result = $this->conn->query("SELECT * FROM `users` WHERE `group_id` = '$group_id' AND `rank_num` > 0 AND `sex` = '1' ORDER BY `rank_num` ASC");
        if ($result->num_rows > 0)
        {
            $rank_list['man'] = $result->fetch_all(MYSQLI_ASSOC);
        }

        return $rank_list;
    }


}

//操作杂类
class Tool
{
    //获取本周一（周一凌晨00:00）时间戳
    public static function getWeekBeginTimeStamp()
    {
        //当前时间戳
        $current_time = time();

        //获取当前是星期几（周日为0）
        $week_day = intval(date("w", $current_time));

        //转换周日的值
        if ($week_day == 0)
            $week_day = 7;

        //今日0点时间戳
        $beginTime_today = strtotime(date("Y-m-d", $current_time));

        //返回本周一时间戳
        return $beginTime_today - ($week_day - 1) * 24 * 60 * 60;
    }

    //获取本周日(周日晚23:59:59)时间戳
    public static function getWeekEndTimeStamp()
    {
        return static::getWeekBeginTimeStamp() + 6 * 24 * 60 * 60 + 23 * 60 * 60 + 59 * 60 + 59;
    }

    //相应状态结果码模板
    public static function makeResponseTpl($status, $message, $data = null)
    {
        return array(
            'status' => $status,
            'message' => $message,
            'data' => $data
            );
    }

    //输出JSON响应流数据
    public static function sendResponseJson($response_tpl)
    {
        header('Content-type: application/json');
        echo json_encode($response_tpl);
    }

    //生成随机的比赛确认码(8位)
    public static function CreateTaskCheckCode()
    {
        //样例字符串数组
        $letters = array();
        $start_ch = ['a', 'A', '0'];
        $end_ch = ['A', 'Z', '9'];
        //填充大小写字母和数字字符
        for ($i = 0; $i < count($start_ch); $i++)
        {
            for ($j = ord($start_ch[$i]); $j <= ord($end_ch[$i]); $j++)
            {
                array_push($letters, chr($j));
            }
        }

        //开始随机生成8位确认码
        $check_code = "";

        for ($i = 0; $i < 8; $i++)
        {
            $check_code .= $letters[rand(0, count($letters))];
        }

        return $check_code;
    }

    //发送POST请求
    public static function send_json_post($url, $post_data)
    {
      $postdata = json_encode($post_data);
      $options = array(
        'http' => array(
          'method' => 'POST',
          'header' => 'Content-type:application/json',
          'content' => $postdata,
          'timeout' => 15 * 60 // 超时时间（单位:s）
        )
      );
      $context = stream_context_create($options);
      $result = file_get_contents($url, false, $context);

      return $result;
    }

}

//邮件发送类
class Email
{
    var $conn;

    //构造函数
    public function __construct()
    {
        $this->conn = new mysqli(db_host, db_username, db_password, db_database);
    }

    //析构函数
    public function __destruct()
    {
        mysqli_close($this->conn);
    }

    //执行发送邮件操作
    //判断是否为合法的邮箱地址
    public function sendMail($email_address, $title, $content)
    {
        //判断是否为合法的邮箱地址
        if (!$this->isValidAddress($email_address)){
            return Tool::makeResponseTpl('error', '邮箱地址不合法!');
        }

        //检查内容是否为空
        if ($content == "" || $title == ""){
            return Tool::makeResponseTpl('error', '邮件标题或内容不能为空!');
        }

        Tool::send_json_post('http://127.0.0.1:7899',array(
            'action' => 'send_mail',
            'email_address' => $email_address,
            'title' => $title,
            'content' => $content
        ));

//        system("python ../lib/popen.py \"$email_address\" \"$title\" \"$content\"");

        return Tool::makeResponseTpl('success', '邮件已发送到邮箱, 请注意查收!');
    }

    //验证邮箱地址是否合法
    public function isValidAddress($email_address)
    {
        return filter_var($email_address, FILTER_VALIDATE_EMAIL);
    }

    //生成操作验证码
    public function produceCode($user_id)
    {
        $user_id = intval($user_id);
        $now_time = time();
        $now_time_str = date('Y-m-d H:i:s', $now_time);

        //获取上次生成验证码时间
        if ($_SESSION['last_code_time'] == null){
            $_SESSION['last_code_time'] = time();
        }
        else {
            //同一会话下操作验证码5分钟内不能重复获取
            if ($now_time - $_SESSION['last_code_time'] < 60 * 5){
                $rest_sec = 5 *60 - ($now_time - $_SESSION['last_code_time']);
                return Tool::makeResponseTpl('wait', "访问频率太快，请等待{$rest_sec}秒稍后再试吧!", $rest_sec);
            }
        }

        $_SESSION['last_code_time'] = time();

        //从验证码表中获取未过期的验证码
        $result = $this->conn->query("SELECT `code` FROM `security_code` WHERE `user_id` = '$user_id' AND `expire_time` >= '$now_time_str' ORDER BY `id` DESC LIMIT 1");

        if ($result->num_rows > 0){
            return Tool::makeResponseTpl('success', '验证码获取成功', $result->fetch_assoc()['code']);
        }

        $expire_time = date('Y-m-d H:i:s', $now_time + 5 * 60);
        $new_code = substr(Tool::CreateTaskCheckCode(), 0, 5);
        $sql_status = $this->conn->query("INSERT INTO `security_code` (`user_id`, `code`, `expire_time`) VALUES ('$user_id', '$new_code', '$expire_time')");


        if (!$sql_status){
            return Tool::makeResponseTpl('error', '操作验证码生成失败!');
        }

        return Tool::makeResponseTpl('success', '验证码生成成功!', $new_code);
    }

    //校验操作验证码是否正确
    public function checkCode($user_id, $code)
    {
        $user_id = intval($user_id);
        $code = $this->conn->real_escape_string($code);

        $now_time = date('Y-m-d H:i:s', time());
        $result = $this->conn->query("SELECT `code` FROM `security_code` WHERE `user_id` = '$user_id' AND `expire_time` >= '$now_time' ORDER BY `id` DESC LIMIT 1");

        if ($result->num_rows == 0){
            return Tool::makeResponseTpl('error', '该验证码已过期，请重新获取!');
        }

        //获取正确的验证码
        $real_code = $result->fetch_assoc()['code'];

        if ($code != $real_code){
            return Tool::makeResponseTpl('error', '无效的验证码,请重新输入!');
        }

        return Tool::makeResponseTpl('success', '验证码正确!');

    }

    //获取用户邮箱地址
    public function getUserEmailAddress($user_id)
    {
        $user_id = intval($user_id);
        $result = $this->conn->query("SELECT `email_address` FROM `users` WHERE `user_id` = '$user_id'");

        if ($result->num_rows == 0){
            return null;
        }

        return $result->fetch_assoc()['email_address'];
    }

    //设置用户邮箱地址
    public function setUserEmailAddress($user_id, $email_address)
    {
        $user_id = intval($user_id);
        $email_address = $this->conn->real_escape_string($email_address);

        if (!$this->isValidAddress($email_address)){
            return Tool::makeResponseTpl('error', '邮箱地址格式有误!');
        }

        $sql_status = $this->conn->query("UPDATE `users` SET `email_address` = '$email_address' WHERE `user_id` = '$user_id'");
        if (!$sql_status){
            return Tool::makeResponseTpl('error', '写入数据库时出现了错误!');
        }

        return Tool::makeResponseTpl('success', '设置用户邮箱地址成功!');
    }

    //判断用户是否绑定邮箱
    public function checkUserEmailStatus($user_id)
    {
        $user_id = intval($user_id);
        $result = $this->conn->query("SELECT `email_status` FROM `users` WHERE `user_id` = '$user_id'");

        if ($result->num_rows == 0){
            return false;
        }

        return $result->fetch_assoc()['email_status'] == 1;
    }

    //设置用户邮箱状态
    public function setUserEmailStatus($user_id, $email_status)
    {
        $user_id = intval($user_id);
        $email_status = intval($email_status);

        $sql_status = $this->conn->query("UPDATE `users` SET `email_status` = '$email_status' WHERE `user_id` = '$user_id'");
        if (!$sql_status){
            return Tool::makeResponseTpl('error', '写入数据库时出现了错误!');
        }

        return Tool::makeResponseTpl('success', '设置用户邮箱状态!');
    }

    //设置用户邮箱信息
    public function setUserEmailInfo($user_id, $email_Address , $email_status)
    {
        $action_status = $this->setUserEmailStatus($user_id, $email_status);

        if ($action_status['status'] == 'error'){
            return $action_status;
        }

        $action_status = $this->setUserEmailAddress($user_id, $email_Address);

        if ($action_status['status'] == 'error'){
            return $action_status;
        }

        return Tool::makeResponseTpl('success', '邮箱信息设置成功!');
    }

    //发起挑战提示邮件
    public static function sendFoundTaskMail($founder_id, $target_id)
    {
        $Email = new Email();
        $User = new user();

        //若对方尚未绑定邮箱，则不发送
        if (!$Email->checkUserEmailStatus($target_id)){
            return;
        }

        //构造邮件内容
        $founder_info = $User->getUserInfo($founder_id);
        $target_info = $User->getUserInfo($target_id);

        $founder_name = $founder_info['username'];
        $target_name = $target_info['username'];
        $group_name = $founder_info['group_name'];
        $time_str = date('Y-m-d H:i:s', time());

        $email_title = "有人向您发起了乒乓球比赛挑战，请及时应战!";
        $email_content = "亲爱的{$target_name}同学：<br><p style='text-indent: 2rem'>您所在的{$group_name}的{$founder_name}向您发起了挑战，请在规定时间内前往平台接受挑战，并由获胜方上报比赛的结果, 具体详情请登录平台查看.</p><p style='text-indent: 2rem'>若未在规定时间内接受挑战，系统将自动视你弃权!</p><br><div style='float: right'>CUIT乒乓球擂台赛组委会</div><div style='float: right;clear: both'>{$time_str}</div><div style='clear: both'></div><br>（本邮件由系统程序自动发送，您无需回复）";

        $Email->sendMail($target_info['email_address'], $email_title, $email_content);
    }

}

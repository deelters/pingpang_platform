<?php
    /*
        @后端操作接口 !!!必须先定义函数名称在$all_action数组内
        @操作成功[1] 操作失败[-1] 未定义函数操作[-2]
    */

    //判断是否登陆
    if(intval($_SESSION["role_id"])==0)
    {
        Tool::sendResponseJson(Tool::makeResponseTpl('error', '登录状态有误，请检查！'));
        exit();
    }


    //自动调用相应函数操作
    $action=$_POST["action"];
    $all_action=array(
        'make_challenge',
        'accept_challenge',
        'uploadResult',
        'change_pwd',
        'active_account',
        'adduser',
        'reset_pwd',
        'set_userLock',
        'getEmailCode',
        'checkEmailCode',
        'cancelEmail',
        'cancel_task'
    ); //已注册函数

    if(in_array($action,$all_action))
    {
        //获取用户状态
        $User = new user();
        $User->getUserStatusByUserId($_SESSION['user_id'], $user_status);

        //特殊用户账户异常状态判断
        if ($user_status == 2 || $user_status == 3)
        {
            Tool::sendResponseJson(Tool::makeResponseTpl('error', '操作失败, 当前账户状态异常！'));
            exit();
        }

        if($user_status == 0 && $action!= "active_account")
        {
            Tool::sendResponseJson(Tool::makeResponseTpl('error', '当前账户未激活，暂不支持该操作'));
            exit();
        }

        call_user_func($action);
    }
    else
    {
        Tool::sendResponseJson(Tool::makeResponseTpl('error', '无效的API请求操作方法！'));
        exit();
    }


    //发起挑战
    function make_challenge()
    {
        $User = new user();
        $founder_id = $_SESSION['user_id'];
        $target_id = intval($_POST['target_id']);

        Tool::sendResponseJson($User->MakeChallenge($founder_id, $target_id));
    }

    //接受挑战接口
    function accept_challenge()
    {
        $User = new user();
        $task_id = intval($_POST['task_id']);

        Tool::sendResponseJson($User->acceptChallenge($task_id, $_SESSION['user_id']));
    }

    //上报比赛数据
    function uploadResult()
    {
        $User = new user();
        $winner_id = $_SESSION['user_id'];
        $task_id = intval($_POST['task_id']);
        $check_code = $_POST['check_code'];

        Tool::sendResponseJson($User->uploadResult($task_id, $winner_id, $check_code));
    }

    //统一修改密码校验组件
    function common_change_pwd($user_id, $old_pwd, $new_pwd)
    {
        $User = new user();

        if ($old_pwd == "" || $new_pwd == "")
        {
            return Tool::makeResponseTpl('error', '修改失败，输入的密码不能为空!');
        }

        if ($old_pwd == $new_pwd)
        {
            return Tool::makeResponseTpl('error', '修改失败，新密码不能与原密码相同!');
        }

        if (!$User->checkUserPassword($user_id, $old_pwd))
        {
            return Tool::makeResponseTpl('error', '修改失败，原密码输入错误!');
        }

        if (!$User->changeUserPassword($user_id, $new_pwd))
        {
            return Tool::makeResponseTpl('error', '修改失败，服务端出现了未知的错误!');
        }

        return Tool::makeResponseTpl('success', '密码修改成功, 请重新登录!');

    }

    //修改密码
    function change_pwd()
    {
        $User = new user();
        $user_id = $_SESSION['user_id'];
        $old_pwd = $_POST['old_pwd'];
        $new_pwd = $_POST['new_pwd'];

        $action_info = common_change_pwd($user_id, $old_pwd, $new_pwd);

        if ($action_info['status'] == 'success')
        {
            $User->loginOut();
        }

        return Tool::sendResponseJson($action_info);
    }

    //激活用户账户
    function active_account()
    {
        $User = new User();
        $user_id = $_SESSION['user_id'];
        $old_pwd = $_POST['old_pwd'];
        $new_pwd = $_POST['new_pwd'];

        $user_status = 0;
        $User->getUserStatusByUserId($user_id, $user_status);
        if ($user_status != 0)
        {
            return Tool::sendResponseJson(Tool::makeResponseTpl('error', '该账户已被激活过，请勿重复操作!'));
        }

        $action_info = common_change_pwd($user_id, $old_pwd, $new_pwd);

        if ($action_info['status'] == 'error')
        {
            return Tool::sendResponseJson($action_info);
        }

        $User->setUserStatus($user_id, 1);
        $User->loginOut();
        return Tool::sendResponseJson(Tool::makeResponseTpl('success', '您的账户激活成功,请重新登录!'));
    }

    /*
        @管理员后台添加用户
        @验证码错误[-1],用户名或密码格式不符合要求[-2],用户名已被注册[-3],未知的错误[-4],注册成功[1]
    */
    function adduser()
    {
        $user=new user();
        $role_id=$_SESSION["role_id"];

        //判断是否为管理员用户权限
        if($role_id!=2)
        {
            return Tool::sendResponseJson(Tool::makeResponseTpl('error', '抱歉,您的操作权限不足!'));
        }

        $username = $_POST["username"]; //待添加用户名
        $stu_number = $_POST['stu_number']; //学号
        $group_id = $_POST['group_id']; //选手所在学院组
        $sex = $_POST['sex']; //性别
        $qq_number = $_POST['qq_number']; //QQ号
        $password = Tool::CreateTaskCheckCode(); //生成的随机密码

        return Tool::sendResponseJson($user->registerUser($stu_number, $username, $password, $group_id, $sex, $qq_number)); //执行添加用户操作
    }

    //重置密码
    function reset_pwd()
    {
        $User = new user();
        $role_id=$_SESSION["role_id"];

        //判断是否为管理员用户权限
        if ($role_id!=2)
        {
            return Tool::sendResponseJson(Tool::makeResponseTpl('error', '抱歉,您的操作权限不足!'));
        }

        $user_id = $_POST['user_id'];
        $new_pwd = Tool::CreateTaskCheckCode();

        if (!$User->changeUserPassword($user_id, $new_pwd))
        {
            return Tool::sendResponseJson(Tool::makeResponseTpl('error', '密码重置失败，后端出现了未知错误!'));
        }

        return Tool::sendResponseJson(Tool::makeResponseTpl('success', '选手密码重置成功!<br>新密码为: <span style="color: red">'. $new_pwd .'</span>'));
    }

    //设置账户锁定状态
    function set_userLock()
    {
        $User = new user();
        $role_id=$_SESSION["role_id"];

        //判断是否为管理员用户权限
        if ($role_id!=2)
        {
            return Tool::sendResponseJson(Tool::makeResponseTpl('error', '抱歉,您的操作权限不足!'));
        }

        $user_id = $_POST['user_id'];

        //判断是否被禁赛
        $User->getUserStatusByUserId($user_id, $user_status);
        if (!in_array($user_status , [1, 2])){
            return Tool::sendResponseJson(Tool::makeResponseTpl('error', '选中的账户被禁赛或未激活，暂不支持该操作!'));
        }

        //设置锁定状态
        if ($user_status == 1) {
            $user_status = 2;
            $action_name = '锁定';
        }
        else {
            $user_status = 1;
            $action_name = '解锁';
        }

        if (!$User->setUserStatus($user_id, $user_status)){
            return Tool::sendResponseJson(Tool::makeResponseTpl('error', '操作失败，后端出现了未知错误!'));
        }

        return Tool::sendResponseJson(Tool::makeResponseTpl('success', '选中的账户'. $action_name ."成功!"));

    }

    //获取邮箱操作验证码
    function getEmailCode()
    {
        $Email = new Email();
        $user_id = $_SESSION['user_id'];
        $email_address = $_POST['email_address'];
        $username = $_SESSION['username'];

        if (!$Email->isValidAddress($email_address)){
            return Tool::sendResponseJson(Tool::makeResponseTpl('error', '无效的邮件地址!'));
        }

        $code_info = $Email->produceCode($user_id);

        if ($code_info['status'] != 'success'){
            return Tool::sendResponseJson($code_info);
        }

        $Email->sendMail($email_address, 'CUIT乒乓球挑战赛平台邮箱绑定操作邮件', "亲爱的$username,您正在进行乒乓球挑战赛平台邮箱绑定操作.<br><br>操作验证码为: {$code_info['data']} <br><br>该验证码5分钟内有效!");
        $Email->setUserEmailAddress($user_id, $email_address);
        return Tool::sendResponseJson(Tool::makeResponseTpl('success', '激活邮件已发送!'));
    }

    //校验邮箱验证码并绑定
    function checkEmailCode()
    {
        $Email = new Email();
        $user_id = $_SESSION['user_id'];
        $code = $_POST['code'];
        $email_address = $_POST['email_address'];

        $check_info = $Email->checkCode($user_id, $code);

        if ($email_address != $Email->getUserEmailAddress($user_id)){
            return Tool::sendResponseJson(Tool::makeResponseTpl('error', '输入的邮箱地址与获取验证码的不一致!'));
        }

        if ($check_info['status'] != 'success'){
            return Tool::sendResponseJson($check_info);
        }

        //绑定用户邮箱
        return  Tool::sendResponseJson($Email->setUserEmailInfo($user_id, $email_address, 1));
    }

    //取消绑定邮箱
    function cancelEmail()
    {
        $user_id = $_SESSION['user_id'];
        $Email = new Email();

        $action_info = $Email->setUserEmailStatus($user_id, 0);

        if ($action_info['status'] == 'success'){
            return Tool::sendResponseJson(Tool::makeResponseTpl('success', '邮箱解绑成功!'));
        }

        return $action_info;
    }

    //管理员取消比赛
    function cancel_task()
    {
        $User = new user();
        $role_id=$_SESSION["role_id"];

        //判断是否为管理员用户权限
        if ($role_id!=2)
        {
            return Tool::sendResponseJson(Tool::makeResponseTpl('error', '抱歉,您的操作权限不足!'));
        }

        $task_id = $_POST['task_id'];

        Tool::sendResponseJson($User->cancelTask($task_id));
    }

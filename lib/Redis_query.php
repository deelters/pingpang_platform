<?php
/*
        Redis缓存查询类 创建于2021-03-23
*/

class Redis_query
{
    var $redis_conn = null;
    var $redis_pool = null;

    //构造函数
    public function __construct()
    {
        //首先创建redis连接池
        $this->setRedisPool();
    }

    //析构函数
    public function __destruct()
    {

    }

    //创建redis连接池
    public function setRedisPool()
    {
        $this->redis_pool = new Redis_pool();
        $this->redis_conn = $this->redis_pool->getRedis();
    }

    /*
     * @description 将用户积分信息记录到缓存内
     * @parma $sex-type - 选手所在性别组别 [女生组 0, 男生组 1]
     * @parma $user_id - 用户id
     * @parma $score 用户积分
     * */
    public function setUserScoreInfo($sex_type, $user_id, $score)
    {
        //进行判断记录到那个缓存区
        if ($sex_type == SexType::WOMAN) {
            $redis_bookname = RedisBookName::score_list_woman;
        }
        else {
            $redis_bookname = RedisBookName::score_list_man;
        }

        $status = $this->redis_conn->zAdd($redis_bookname, $score, $user_id);

        if (!$status)
        {
            return false;
        }

        return true;
    }

    //通过user_id来获取用户积分
    public function getUserScoreByUserId($user_id)
    {
        $user_sex = $this->getUserSex($user_id);

        if ($user_sex == SexType::MAN){
            $score = $this->redis_conn->zScore(RedisBookName::score_list_man, $user_id);
        }
        else if ($user_sex == SexType::WOMAN){
            $score = $this->redis_conn->zScore(RedisBookName::score_list_woman, $user_id);
        }
        else{
            $score = 0;
        }

        return intval($score);
    }

    //返回用户所在的性别组
    public function getUserSex($user_id)
    {
        //若存在于集合中，则返回积分，否则返回False（因此只需要判断值是否为数字）
        $is_man = $this->redis_conn->zRank(RedisBookName::score_list_man, $user_id);
        $is_woman = $this->redis_conn->zRank(RedisBookName::score_list_woman, $user_id);

        if (is_numeric($is_man)){
            $sex_res = SexType::MAN;
        }
        else if (is_numeric($is_woman)){
            $sex_res = SexType::WOMAN;
        }
        else{
            $sex_res = null;
        }

        return $sex_res;
    }

    //将用户排名写入缓存
    public function setUserRankNum($user_id, $sex, $rank_num)
    {
        if ($sex == SexType::WOMAN){
            $book_name = RedisBookName::ranknum_woman;
        }
        else{
            $book_name = RedisBookName::ranknum_man;
        }

        $this->redis_conn->hSet($book_name, $user_id, $rank_num);
    }

    //读取缓存中用户的排名
    public function getUserRankNum($user_id)
    {
        //判断用户的所在性别组
        $sex = $this->getUserSex($user_id);

        //判断是否存在该用户
        if (is_null($sex))
        {
            return -1;
        }

        //从相应组别中读取数据
        $book_name = [RedisBookName::ranknum_woman, RedisBookName::ranknum_man];
        return $this->redis_conn->hGet($book_name[$sex], $user_id);
    }

    //判断是否存在缓存
    public function isHasCaches()
    {
        $cache_num1 = $this->redis_conn->zCard(RedisBookName::score_list_man);
        $cache_num2 = $this->redis_conn->zCard(RedisBookName::score_list_woman);
        $cache_num3 = $this->redis_conn->hLen(RedisBookName::ranknum_man);
        $cache_num4 = $this->redis_conn->hLen(RedisBookName::ranknum_woman);
        return min($cache_num1, $cache_num2, $cache_num3, $cache_num4) > 0;
    }

    //将数据库内男女组所有积分数据写入缓存
    public function initScoreInfo()
    {
        $conn = new mysqli(db_host, db_username, db_password, db_database);
        $query_list = [SexType::WOMAN, SexType::MAN];
        //将男女组积分信息写入缓存内
        foreach ($query_list as $sex)
        {
            //读入男生、女生组积分数据
            $sql = "SELECT `user_id`, `score`, `username` FROM `users` WHERE `sex` = '$sex'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $this->setUserScoreInfo($sex, $row['user_id'], $row['score']);
                    $this->setUsernamesInfo($row['user_id'], $row['username']);
                }
            }
        }
        $conn->close();
    }

    //将男女组排名更新
    public function updateRankNum()
    {
        //读取男子、女子组积分缓存信息
        $query_list = [SexType::WOMAN, SexType::MAN];
        $book_namelist = [RedisBookName::score_list_woman, RedisBookName::score_list_man];
        foreach ($query_list as $sex) {
            $res = $this->redis_conn->zRevRange($book_namelist[$sex], 0, -1, true);
            $rankNum_last = 0;
            $score_last = -1;
            foreach ($res as $key => $value) {
                if ($value == $score_last) {
                    $this->setUserRankNum($key, $sex, $rankNum_last);
                } else {
                    $this->setUserRankNum($key, $sex, ++$rankNum_last);
                    $score_last = $value;
                }
            }
        }
    }

    //存入 user_id -> username 哈希表
    public function setUsernamesInfo($user_id, $username)
    {
        $this->redis_conn->hSet(RedisBookName::username_hashmap, $user_id, $username);
    }

    public function getUsernamesInfo($user_id)
    {
        return $this->redis_conn->hGet(RedisBookName::username_hashmap, $user_id);
    }

    //通过读取缓存来获取RankList
    public function getRankListBySex($sex)
    {
        $data_list = array();
        $book_namelist = [RedisBookName::score_list_woman, RedisBookName::score_list_man];
        $res = $this->redis_conn->zRevRange($book_namelist[$sex], 0, -1, true);
        foreach ($res as $key => $value){
            $tmp_username = $this->getUsernamesInfo($key);
            $tmp_ranknum = $this->getUserRankNum($key);
            $tmp_item = array(
                "user_id" => $key,
                "score" => $value,
                "username" => $tmp_username,
                "rank_num" => $tmp_ranknum
            );
            array_push($data_list, $tmp_item);
        }

        return $data_list;
    }

}

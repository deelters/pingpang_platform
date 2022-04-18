<?php
/*
        Redis缓存类 创建于2021-3-23
*/

define("HOST", "127.0.0.1");
define("PORT", 6379);


class Redis_pool
{
    var $redis = null;

//    构造函数
    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect(HOST, PORT);
    }

//    获取redis实例
    public function getRedis()
    {
        return $this->redis;
    }
}

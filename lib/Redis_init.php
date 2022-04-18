<?php
    /*
        @初始化Redis内的缓存数据
        @修改于2021-3-23
    */

    //判断是否存在缓存
    $cache = new Redis_query();
    $cache_status = $cache->isHasCaches();

    //如果没有缓存，则读取数据库中数据，并写入缓存
    if (!$cache_status)
    {
        $cache->initScoreInfo();
        $cache->updateRankNum();
    }



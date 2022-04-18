<?php
    $user_id = $_SESSION["user_id"];
    $User = new user();
    $user_info = $User->getUserInfo($user_id);
    $cache = new Redis_query();
    $my_rankNum = $cache->getUserRankNum($user_id);
    $max_rankNum = [16, 48];//当前学院组排行榜的最大名次
    $my_rankNum = $my_rankNum > $max_rankNum[$user_info['sex']] ? '暂未上榜' : "第{$my_rankNum}名";

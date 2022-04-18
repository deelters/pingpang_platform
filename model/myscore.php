<?php
    /*
     * @我的积分详情后端页面
     *
     * */
    $user_id = $_SESSION['user_id'];
    $User = new user();
    $Cache = new Redis_query();
    $sex = $Cache->getUserSex($user_id);
    $detail_list = $User->getScoreDetail($user_id);
    if ($detail_list == null){
        $total_num = 0;
    }else{
        $total_num = count($detail_list);
    }
    $max_rankNum = [16, 48];//当前学院组排行榜的最大名次

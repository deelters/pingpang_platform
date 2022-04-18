<?php
    $Rank = new Rank();
    $User = new user();
    $cache = new Redis_query();
    $user_id = $_SESSION['user_id'];
    global $my_info;
    $my_info = $User->getUserInfo($user_id);
    $my_rankNum = $cache->getUserRankNum($user_id); //当前用户榜单排名
    $rank_list = $cache->getRankListBySex($my_info['sex']); //挑战榜单
//    $rest_times = $User->getUserRestTimes($user_id); //本周剩余可挑战次数
    $now_rankNum = $cache->getUserRankNum($user_id); //用户当前排名
    $my_score = $cache->getUserScoreByUserId($user_id); //用户当前积分
    $has_unconfirmed_task = $User->hasUnconfirmedTask($user_id); //用户是否有待接受比赛

    global $max_rank;
    $max_rank = [16, 48]; //每个性别组最大显示排名
    //是否显示在榜单上，男生组只取前48名，女生组取前16名
    function isShowInRank($item)
    {
        global $max_rank;
        global $my_info;
        return $item['rank_num'] > 0 && $item['rank_num'] <= $max_rank[$my_info['sex']];
    }

    if ($my_info['sex'] == SexType::MAN) {
        $rank_list = array_filter($rank_list, "isShowInRank");
    }
    else{
        $rank_list = array_filter($rank_list, "isShowInRank");
    }

    $total_num = count($rank_list); //当前榜单上选手总数

    //判断挑战按钮是否禁用
    $btn_enabled = array();

    for ($i = 0; $i < $total_num; $i++)
    {
        if ($rank_list[$i]["user_id"] == $_SESSION["user_id"])
        {
            $btn_enabled[$i] = false;
        }
        else
        {
            //判断基准排名点（如果未在榜上，则只能挑战后五名）
            if ($my_rankNum > $max_rank[$my_info['sex']])
            {
                $base_rankNum = $max_rank[$my_info['sex']] + 1;
            }
            else
            {
                $base_rankNum =  $my_rankNum;
            }

            if ($rank_list[$i]["rank_num"] >= $base_rankNum - 5 && $rank_list[$i]["rank_num"] < $base_rankNum)
            {
                $btn_enabled[$i] = true;
            }
            else
            {
                $btn_enabled[$i] = false;
            }
        }
    }
    $max_rankNum = $max_rank[$my_info['sex']];

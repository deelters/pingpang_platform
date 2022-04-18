<?php
    $User = new user();
    $user_id = $_SESSION['user_id'];
    $task_info = $User->getUserTaskInfo($user_id);
    $total_num = count($task_info);
    $cache = new Redis_query();
    global $sex;
    $sex = $cache->getUserSex($user_id);

    //将名次为0的选手显示为未上榜
    for ($cnt = 0; $cnt < $total_num; $cnt++)
    {
        getRankLabelName($task_info[$cnt]['founder_rank_num_before']);
        getRankLabelName($task_info[$cnt]['target_rank_num_before']);
        getRankLabelName($task_info[$cnt]['founder_rank_num_after']);
        getRankLabelName($task_info[$cnt]['target_rank_num_after']);
    }

    //将排名为0的选手显示为未上榜
    function getRankLabelName(&$rank_num)
    {
        global $sex;
        $max_rankNum = [16, 48];
        if ($rank_num > $max_rankNum[$sex]) $rank_num = '未上榜';
    }

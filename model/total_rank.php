<?php
    $user_id = $_SESSION['user_id'];
    $groups_info = [array("group_id" => 1, "group_name" => "男子组"), array("group_id" => 0, "group_name" => "女子组")];
    $groups_count = count($groups_info);
    $query_group_id = intval($app->get_argument(1)); //获取带查询参数id
    //如果是无效性别组参数，则默认返回男子组信息
    if (!in_array($query_group_id, [0, 1]))
    {
        $query_group_id = 1;
    }
    $cache = new Redis_query();
    $rank_list = $cache->getRankListBySex($query_group_id); //获取该院榜单
    global $sex;
    $sex = $query_group_id;
    //转换显示未上榜显示的排名文本
    $func = function ($item){
        global $sex;
        $max_rankNum = [16, 48];
        if ($item['rank_num'] > $max_rankNum[$sex]){
            $item['rank_num'] = '未上榜';
        }
        return $item;
    };
    $rank_list = array_map($func, $rank_list);
    $count_rank_list = count($rank_list);

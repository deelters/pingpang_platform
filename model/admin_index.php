<?php
    $User = new user();
    $Rank = new Rank();
    $query_group_id = intval($app->get_argument(1));
    if ($query_group_id == 0) $query_group_id = -1;
    $User->getAllUserList($query_group_id, $total_num, $user_info_list);
    $groups_info = $Rank->getGroupsInfo();
    $group_num = count($groups_info);

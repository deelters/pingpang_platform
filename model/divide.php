<?php
    $User = new user();
    $Cache = new Redis_query();
    $user_id = $_SESSION['user_id'];
    $start_info = $User->getStartInfo($user_id);
    $is_show = $start_info != null; //是否显示分组信息出来
    $another_id = $start_info['another_id'];
    $another_name = $Cache->getUsernamesInfo($another_id);
    $table_id = $start_info['table_id'];

<?php
    session_name("session_id"); //安全考虑，修改默认session_id
    session_start();

    include("../../lib/Redis_pool.php");
    include("../../lib/Redis_query.php");
    include("../../lib/Enum_data.php");
    include("./DB.php");
    if (intval($_SESSION['role_id']) != 2){
        return null;
        exit();
    }

    //读取数据库
    $db = new DB();
    $cache = new Redis_query();
    $query = $db->getConn()->query("SELECT `a_id`, `b_id`, `table_id` FROM `divide_groups` WHERE `status` = '0' ORDER BY `table_id` ASC");
    $data_list = array(); //待返回数组
    $tmp_table = array(); //每一桌信息
    $start_group_id = 1;
    if ($query->num_rows > 0){
        while ($row = $query->fetch_assoc()){
            if ($row['table_id'] != $start_group_id) {
                $start_group_id++;
                array_push($data_list, $tmp_table);
                $tmp_table = array();
            }
            array_push($tmp_table, array(
                    "a_id" => $row['a_id'],
                    "b_id" => $row['b_id'],
                    "a_username" => $cache->getUsernamesInfo($row['a_id']),
                    "b_username" => $cache->getUsernamesInfo($row['b_id']),
                    "table_id" => $start_group_id
                ));
        }

        array_push($data_list, $tmp_table);
    }

    echo json_encode($data_list);





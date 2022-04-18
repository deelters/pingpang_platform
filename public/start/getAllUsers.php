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
    $query = $db->getConn()->query("SELECT `user_id`, `username`, `sex` FROM `users` ORDER BY `user_id` ASC");
    $man_list = array();
    $woman_list = array();
    if ($query->num_rows > 0){
        while ($row = $query->fetch_assoc()){
            $info = array(
                "user_id" => $row['user_id'],
                "username" => $row['username']
            );

            if ($row['sex'] == 0){
                array_push($woman_list, $info);
            }
            else {
                array_push($man_list, $info);
            }
        }
    }

    $data_list = array(
        "woman" => $woman_list,
        "man" => $man_list
    );

    echo json_encode($data_list);

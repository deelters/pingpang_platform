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
    $conn = $db->getConn();
    //读取传入的数据
    $request_data = json_decode(file_get_contents ( 'php://input' ));
    $sex = $request_data->sex; //选择待删除的性别参数
    //删除分组名单
    $query_a = $conn->query("DELETE FROM `divide_groups` WHERE `sex` = '{$sex}'");
    //同时删除轮空信息
    $query_b = $conn->query("DELETE FROM `lunkong` WHERE `sex` = '{$sex}'");
    if ($query_a && $query_b){
        echo json_encode(array(
            "msg" => "分组信息删除成功!"
        ));

    }
    else {
        echo json_encode(array(
            "msg" => "发生了未知的错误， 分组信息删除失败！"
        ));
    }

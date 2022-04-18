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

     $db = new DB();
     $cache = new Redis_query();
     $conn = $db->getConn();
     $query = $conn->query("SELECT * FROM `lunkong`");
     $man = array();
     $woman = array();
     if ($query->num_rows > 0){
         while ($row = $query->fetch_assoc()){
                 $name = $cache->getUsernamesInfo($row['user_id']);
             if ($row['sex'] == '1'){
                 array_push($man, "{$name} ({$row['user_id']}号)");
             }
             else{
                  array_push($woman, "{$name} ({$row['user_id']}号)");
             }
         }
     }

     echo json_encode(array(
         "man" => $man,
         "woman" => $woman
     ));

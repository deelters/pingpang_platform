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
    global $conn;
    $conn = $db->getConn();
    $cache = new Redis_query();

    //读取传入的数据
    $request_data = json_decode(file_get_contents ( 'php://input' ));
    $id_list = $request_data->user_ids;
    $sex = $request_data->sex;

    //进行随机分组
    shuffle($id_list);
    $divide_result = array();
    $direct_person = null; //轮空选手
    $each_table_nums = intval(count($id_list) / 30) + 1; //每桌分配组数
    $now_table_cnt = 0; //当前分配人员个数
    //计算分配最少的桌号
    $query = $conn->query("SELECT distinct `table_id` as a, (select COUNT(table_id) FROM divide_groups WHERE table_id  = a) as `nums` FROM `divide_groups` ORDER BY `nums` ASC ");
    if ($query->num_rows > 0){
        $used_table_nums = $query->fetch_assoc()['a'];
        $now_table_cnt = $used_table_nums;
    }

    while (count($id_list)){
        //轮空的情况
        if (count($id_list) == 1){
            $direct_person = array_pop($id_list);
            break;
        }
        $a_id = array_pop($id_list);
        $b_id = array_pop($id_list);
//        $table_id = ++$now_table_cnt % 21;  //利用同余自动分配桌数
//        $table_id = $table_id == 0 ? 2 : $table_id;
        $table_id = getMinNumsTableId();
        $item = array($a_id, $b_id, $table_id);
        //将分组信息写入数据库
        $query = $conn->query("INSERT INTO `divide_groups` (`a_id`, `b_id`, `table_id`, `sex`, `status`) VALUES ('{$item[0]}', '{$item[1]}', '{$item[2]}', '{$sex}', '0')");
    }

    //轮空信息加入
    if ($direct_person != null){
            $query = $conn->query("INSERT INTO `lunkong` (`user_id`, `sex`) VALUES ('$direct_person', '$sex')");
    }

    echo json_encode(array(
        "type" => "success",
        "msg" => "分组成功!",
        "direct_person" => $direct_person
        ));

    function getMinNumsTableId(){
        global $conn;
        $query = $conn->query("SELECT distinct `table_id` as a, (select COUNT(table_id) FROM divide_groups WHERE table_id  = a) as `nums` FROM `divide_groups` ORDER BY `nums` ASC ");
        if ($query->num_rows > 0){
            $row = $query->fetch_assoc();
            $res = $conn->query("SELECT COUNT(`div_id`) FROM `divide_groups`");
            $nums = $res->fetch_assoc()['COUNT(`div_id`)'];
            if ($nums < 21){
                return 21 - $nums;
            }
            else{
                return $row['a'];
            }
        }
        return 1;
    }

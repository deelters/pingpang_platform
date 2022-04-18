<?php
    /*
     * @description 开幕赛分组管理界面
     * @date 2021-03-25 18:12
     * */
    session_name("session_id"); //安全考虑，修改默认session_id
    session_start();
    //只有管理员用户才用权限访问
    if (intval($_SESSION['role_id']) != 2){
//        header("location: ".$_SERVER["HOST"]."/login");
        echo '请重新登录!';
        exit();
    }
    //路由选择页面
    $page_name_list = ['tables', 'info', 'persons'];
    $p = $_GET['p'];
    if (!in_array($p, $page_name_list)){
        $p = "main";
    }
    include("../view/single/{$p}.html");

<?php
    /*
        @用户权限拦截器
        @zuoweiyuan 2020-05-20 22:42
    */

    $app=new app();
    $User = new user();
    $controller=$app->get_controller();
    $method=$app->get_method();
    $login_status=$_SESSION["role_id"];

    if($login_status==null) //游客身份时
    {
        $forbidden_router=array("index", "divide", "mytask","myinfo","admin","active", "myscore");
        if(in_array($controller,$forbidden_router))
        {
            header("location:".$_SERVER["HOST"]."/login");
            exit();
        }
    }
    else if($login_status==1) //选手身份时
    {
        $forbidden_router=array("admin");
        if(in_array($controller,$forbidden_router))
        {
            header("location:".$_SERVER["HOST"]."/login");
            exit();
        }
    }

    //获取用户状态
    $User->getUserStatusByUserId($_SESSION['user_id'], $user_status);
    //账户未激活用户拦截
    if ($user_status == 0)
    {
        if (!in_array($controller, array("active", "api", "login", "total")))
        {
            header("location:".$_SERVER["HOST"]."/active");
            exit();
        }
    }

<?php
    /*
        @网站主入口
        @修改于2021-3-18
    */
    define("ALLOW",1);
    session_name("session_id"); //安全考虑，修改默认session_id
    include ("../lib/Enum_data.php");
    include("../lib/urlchange.php");
    include("../lib/Redis_pool.php");
    include("../lib/Redis_query.php");
    include("../lib/db.php");
    include("../lib/csrf.php");
    include("../lib/compiler.php");
    include("../lib/fitter.php");
    include("../lib/Redis_init.php");

    $app=new app();
    $path=$app->get_controller();

    if($path==""){
        header("Location:". $_SERVER["HOST"] ."/index");
        exit();
    }

    if($app->get_method()!="") $path.="_".$app->get_method();

    //判断是api请求还是页面请求
    if($app->get_controller()=="api" && $app->get_method()!="" && file_exists("../api/".$app->get_method().".php"))
    {
        include("../api/".$app->get_method().".php");
    }
    else
    {
        show_template($path);
    }

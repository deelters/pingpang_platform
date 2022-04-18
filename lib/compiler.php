<?php
    /*
        @zuoweiyuan 模板编译转换引擎   修改于 2020-04-23 11:14
    */

    //若非法访问则返回主页
    if(!defined("ALLOW")) 
    {
        header("location:".$_SERVER["HOST"]."/");
        exit();
    }

    //显示处理后模板
    function show_template($tpl_name)
    {
        //若模板文件不存在则返回主页
        if(!file_exists("../view/$tpl_name.html"))
        {
            //echo "模板文件".$tpl_name.".html 不存在!";
            if(file_exists("../view/home.html"))
            {
                // header("location:".$_SERVER["HOST"]."/notfound");
                show_template("notfound");
            }
            else
            {
                echo "出错了!";
            }
            exit();
        }

        //判断模板文件是否需要重新进行编译，否则直接引用编译后文件
        if(!file_exists("../compile/$tpl_name.php") || filemtime("../view/$tpl_name.html")>filemtime("../compile/$tpl_name.php"))
        {
            compile_template($tpl_name);
        }
        
        $app=new app;
        $controller=$app->get_controller(); //当前所在控制器名称
        $method=$app->get_method(); //当前所在方法
        $argument[1]=$app->get_argument(1); //获取所有参数1
        $argument[2]=$app->get_argument(2); //获取参数2

        //加载model后端数据传递文件
        if(file_exists("../model/$tpl_name.php"))
        {
            include("../model/$tpl_name.php");
        }
        //加载编译后的模板文件
        include("../compile/$tpl_name.php");
    }


     //编译模板
     function compile_template($tpl_name)
     {
         //读取源模板数据
         $fp=fopen("../view/$tpl_name.html","r");
         $code="";
         while(!feof($fp))
         {
             $code.=fgetc($fp);
         }
         fclose($fp);
         //开始编译源模板
         turn_var($code);
         turn_for($code);
         turn_for_var($code);
         turn_include($code);
         turn_if($code);
         //生成编译后文件
        //  $code=str_replace("\n","",$code);// 取消换行
        //  $code=str_replace("  ","",$code);// 取消多余的空格间隔 补充:经测试这样有一定bug!
         $fp=@fopen("../compile/$tpl_name.php",'w'); //开始写入
         fprintf($fp,"%s",$code);
         fclose($fp);
     }


/* 模板编译引擎 开始*/

    //转换模板变量
    function turn_var(&$code)
    {
        preg_match_all('/{{(.*?)}}/', $code, $matches);
        for($i=0;$i<count($matches[0]);$i++) 
        {
            $code=str_replace($matches[0][$i],'<?php echo $'.$matches[1][$i].';?>',$code);
        }
    }

    //转换模板循环头尾
    function turn_for(&$code)
    {
        //转换循环首
        preg_match_all('/{for:(.*?)}/', $code, $matches);
        for($i=0;$i<count($matches[0]);$i++)
        {
            $code=str_replace($matches[0][$i],'<?php for($i=0;$i<$'.$matches[1][$i].';$i++){?>',$code);
        }
        //转换循环尾
        $code=str_replace("{endfor}","<?php }?>",$code);
    }

    //转换循环变量
    function turn_for_var(&$code)
    {
        preg_match_all('/{%(.*?)%}/', $code, $matches);
        for($i=0;$i<count($matches[0]);$i++)
        {
            $code=str_replace($matches[0][$i],'<?php echo $'.$matches[1][$i].'[$i];?>',$code);
        }
    }

    //转换模板引用
    function turn_include(&$code)
    {
        preg_match_all('/{include:(.*?)}/', $code, $matches);
        for($i=0;$i<count($matches[0]);$i++)
        {
            $matches[1][$i]=str_replace(" ","",$matches[1][$i]);
            $code=str_replace($matches[0][$i],'<?php show_template("'.$matches[1][$i].'");?>',$code);
        }
    }

    //转换模板if判断
    function turn_if(&$code)
    {

        preg_match_all('/{if:(.*?)}/', $code, $matches);
        for($i=0;$i<count($matches[0]);$i++)
        {
            $code=str_replace($matches[0][$i],'<?php if('.$matches[1][$i].'){?>',$code);
        }
        $code=str_replace("{else}","<?php }else{?>",$code);
        $code=str_replace("{endif}","<?php }?>",$code);

    }

/* 模板编译引擎 结束*/
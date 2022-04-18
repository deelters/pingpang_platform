<?php
    /*
        @用户注册接口
        @zuoweiyuan 2020-05-21
    */
    define("role_id",1); //页面注册用户默认权限
    $user=new user();
    $username="";
    $password="";
    $code="";
    $username=$_POST["username"];
    $password=$_POST["password"];
    $code=$_POST["code"];
    echo $user->registerUser($username,$password,$code,role_id);
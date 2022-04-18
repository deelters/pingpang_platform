<?php 
    $user=new user();
    $code=$_POST["code"];
    $stu_num=$_POST["stu_num"];
    $password=$_POST["password"];
    Tool::sendResponseJson($user->checkLogin($stu_num,$password,$code));
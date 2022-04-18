<?
    $user=new user();
    $user->loginOut();
    header("location:".$_SERVER["HOST"]."/login");
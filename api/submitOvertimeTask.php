<?php
    /*
     * @description 判断超时任务 供python定时任务调用接口
     * */

    //判断是否为服务器自身调用（进行接口安全校验）
    if($_SERVER["REMOTE_ADDR"] != '127.0.0.1'){
        header('HTTP/1.1 403 Forbidden');
        Tool::sendResponseJson(Tool::makeResponseTpl('error', 'Access Forbidden.'));
        exit();
    }

    //进行超时未提交比赛的处理
    $User = new user();
    Tool::sendResponseJson($User->processOvertimeTask());
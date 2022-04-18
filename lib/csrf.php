<?php
    /*
        @CSRF攻击防护
        @zuoweiyuan 2020-11-21 15:30
        @修改于2021-3-18
    */

    //只对POST请求进行CSRF_TOKEN的校验
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return;
    }

    //判断是否存在CSRF_TOKEN
    if (!key_exists('HTTP_X_CSRFTOKEN', $_SERVER)){
        Tool::sendResponseJson(Tool::makeResponseTpl('error', '非法请求，CSRF_TOKEN无效!'));
        exit();
    }

    //判断CSRF的值是否与Session内的值一致
    if ($_SERVER['HTTP_X_CSRFTOKEN'] != $_SESSION['CSRF_TOKEN']){
        Tool::sendResponseJson(Tool::makeResponseTpl('error', '非法请求，CSRF_TOKEN无效!'));
        exit();
    }

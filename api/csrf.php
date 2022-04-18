<?php

    if ($_SESSION['CSRF_TOKEN'] == null){
        $_SESSION['CSRF_TOKEN'] = session_create_id(); //生成CSRF_TOKEN用于防护
    }

    $CSRF_TOKEN = $_SESSION['CSRF_TOKEN'];

    echo $CSRF_TOKEN;
<?php
    if ($_SESSION['user_id'] != null)
    {
        header("location:".$_SERVER["HOST"]."/index");
    }
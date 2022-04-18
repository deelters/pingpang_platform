<?php
    $User = new User();

    $task_infos = $User->getAllTaskInfo();
    $total_num = count($task_infos);
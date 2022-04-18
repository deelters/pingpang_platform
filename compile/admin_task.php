<?php show_template("common_header");?>

<style>
    label{
        text-align: end;
    }
</style>

<div class="container">
    <?php show_template("common_nav");?>

    <div style="margin-top: 30px">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link " href="/admin/index">用户总览</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="/admin/task">挑战记录</a>
            </li>
        </ul>
    </div>

    <div style="margin-top: 20px">
        <table class="table table-hover table-bordered text-center">
            <thead>
                <tr>
                    <th>序号</th>
                    <th>学院组</th>
                    <th>发起方</th>
                    <th>被挑战方</th>
                    <th>发起时间</th>
                    <th>截止时间</th>
                    <th>比赛状态</th>
                    <th>操作</th>
                </tr>
            </thead>

            <tbody>
                <?php for($i=0;$i<$total_num;$i++){?>
                <tr>
                    <td><?php echo $i + 1;?></td>
                    <td><?php echo $task_infos[$i]['group_name'];?></td>
                    <td>
                        <?php if($task_infos[$i]['winner_id'] == $task_infos[$i]['founder_id']){?>
                        <i class="fa fa-trophy" aria-hidden="true" style="color: red"></i>
                        <?php }else{?>
                        <?php }?>
                        <?php echo $task_infos[$i]['founder_name'];?> (<?php echo $task_infos[$i]['founder_id'];?>号)
                    </td>
                    <td>
                        <?php if($task_infos[$i]['winner_id'] == $task_infos[$i]['target_id']){?>
                        <i class="fa fa-trophy" aria-hidden="true" style="color: red"></i>
                        <?php }else{?>
                        <?php }?>
                        <?php echo $task_infos[$i]['target_name'];?> (<?php echo $task_infos[$i]['target_id'];?>号)
                    </td>
                    <td><?php echo $task_infos[$i]['begin_time'];?></td>
                    <td><?php echo $task_infos[$i]['end_time'];?></td>
                    <td>
                        <?php if($task_infos[$i]['status']==0){?>
                            <?php if( $task_infos[$i]['error_code']==3){?>
                                <h5><span class="badge badge-pill badge-info">已取消</span></h5>
                            <?php }else{?>
                                <h5><span class="badge badge-pill badge-danger">已结束</span></h5>
                            <?php }?>
                        <?php }else{?>
                            <?php if( $task_infos[$i]['confirm']==1){?>
                                <h5><span class="badge badge-pill badge-success">进行中</span></h5>
                            <?php }else{?>
                                <h5><span class="badge badge-pill badge-warning">待接受</span></h5>
                            <?php }?>
                        <?php }?>
                    </td>
                    <td>
                        <?php if($task_infos[$i]['status']==1){?>
                        <button class="btn btn-danger btn-sm btn-cancelTask" data-task_id="<?php echo $task_infos[$i]['task_id'];?>" data-founder="<?php echo $task_infos[$i]['founder_name'];?> (<?php echo $task_infos[$i]['founder_id'];?>号)" data-target="<?php echo $task_infos[$i]['target_name'];?> (<?php echo $task_infos[$i]['target_id'];?>号)">取消比赛</button>
                        <?php }else{?>
                        <?php }?>
                    </td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    </div>

</div>

<script>
    //取消比赛按钮
    $('.btn-cancelTask').click(function (){
        var that = this;
        showModal('操作确认', '<h5>是否确定要取消该场比赛?</h5><br>发起方: ' + $(this).data('founder') + '<br>' + '被挑战方: ' + $(this).data('target'), function () {
            cancelTask($(that).data('task_id'));
        })
    });

    //调用取消比赛接口
    function cancelTask(task_id)
    {
        $.post({
            url: "/api/setting",
            data: {action: 'cancel_task', task_id: task_id},
            success: function (response){
                showAlert('提示', response.message, response.status, function () {
                    location.reload();
                });
            },
            error: function (error){
                showAlert('提示', '服务器连接失败!', 'error');
            }
        })
    }

</script>

<?php show_template("common_component");?>
<?php show_template("common_footer");?>
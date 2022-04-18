<?php show_template("common_header");?>
<div class="container">
    <?php show_template("common_nav");?>
    <h3 style="text-align: center;margin-top: 10px">我的挑战</h3>

    <?php for($i=0;$i<$total_num;$i++){?>
    <table class="table table-bordered text-center" style="margin-bottom: 50px">
        <tr>
            <th>序号</th>
            <td><?php echo $i + 1;?></td>
            <th class="text-nowrap">比赛类型</th>
            <td colspan="3" class="text-nowrap"><?php if($task_info[$i]['founder_id']==$user_id){?>我方发起<?php }else{?>对方发起<?php }?></td>
        </tr>
        <tr>
            <th class="text-nowrap">对手姓名</th>
            <td><?php if($task_info[$i]['founder_id']==$user_id){?> <?php echo $task_info[$i]['target_name']?> <?php }else{?> <?php echo $task_info[$i]['founder_name']?>  <?php }?></td>
            <th>对手编号</th>
            <td><?php if($task_info[$i]['founder_id']==$user_id){?> <?php echo $task_info[$i]['target_id']?> <?php }else{?> <?php echo $task_info[$i]['founder_id']?>  <?php }?></td>
        </tr>
        <tr>
            <th>对手QQ号</th>
            <td colspan="3"><?php if($task_info[$i]['founder_id']==$user_id){?> <?php echo $User->getUserQQNumber($task_info[$i]['target_id'])?> <?php }else{?> <?php echo $User->getUserQQNumber($task_info[$i]['founder_id'])?>  <?php }?></td>
        </tr>
        <tr>
            <th>比赛状态</th>
            <td class="text-nowrap">
                <?php if($task_info[$i]['confirm']==null && $task_info[$i]['status']==1){?>
                    <span style="font-size: medium;color: white" class="badge badge-pill badge-warning">等待接受</span>
                <?php }else{?>
                    <?php if($task_info[$i]['status']==1){?>
                        <span style="font-size: medium" class="badge badge-pill badge-success">进行中</span>
                    <?php }else{?>
                        <?php if($task_info[$i]['error_code'] == 3){?>
                        <span style="font-size: medium" class="badge badge-pill badge-info">已取消</span>
                        <?php }else{?>
                        <span style="font-size: medium" class="badge badge-pill badge-danger">已结束</span>
                        <?php }?>
                    <?php }?>
                <?php }?>
            </td>
            <th>获胜方</th>
            <td><?php if($task_info[$i]['winner_id']==null){?>
                ---
                <?php }else{?>
                    <?php if($task_info[$i]['winner_id'] == $user_id){?>
                    <span class="text-success">我方获胜</span>
                    <?php }else{?>
                    <span class="text-danger">对方获胜</span>
                    <?php }?>
                <?php }?></td>
        </tr>
        <tr>
            <th>我的赛前名次</th>
            <td><?php if($task_info[$i]['founder_id'] == $user_id){?>
                    <?php echo $task_info[$i]['founder_rank_num_before'];?>
                <?php }else{?>
                    <?php echo $task_info[$i]['target_rank_num_before'];?>
                <?php }?></td>
            <th>对方赛前名次</th>
            <td><?php if($task_info[$i]['founder_id'] == $user_id){?>
                    <?php echo $task_info[$i]['target_rank_num_before'];?>
                <?php }else{?>
                    <?php echo $task_info[$i]['founder_rank_num_before'];?>
                <?php }?></td>
        </tr>

        <?php if($task_info[$i]['status'] == 0){?>
        <tr>
            <th>我的赛后名次</th>
            <td><?php if($task_info[$i]['founder_id'] == $user_id){?>
                <?php echo $task_info[$i]['founder_rank_num_after'];?>
                <?php }else{?>
                <?php echo $task_info[$i]['target_rank_num_after'];?>
                <?php }?></td>
            <th>对方赛后名次</th>
            <td><?php if($task_info[$i]['founder_id'] == $user_id){?>
                <?php echo $task_info[$i]['target_rank_num_after'];?>
                <?php }else{?>
                <?php echo $task_info[$i]['founder_rank_num_after'];?>
                <?php }?></td>
        </tr>
        <?php }else{?>
        <?php }?>

        <tr>
            <th>比赛开始时间</th>
            <td colspan="3"><?php echo $task_info[$i]['begin_time'];?></td>
        </tr>
        <tr>
            <th>比赛截止时间</th>
            <td colspan="3"><?php echo $task_info[$i]['end_time'];?></td>
        </tr>

        <?php if( $task_info[$i]['error_code']!=null){?>
        <tr>
            <th>特殊情况备注</th>
            <?php if($task_info[$i]['error_code']==1){?><td colspan="3" class="text-info">被挑战者未在36小时内主动接受挑战，系统自动视为弃权!</td><?php }else{?><?php }?>
            <?php if($task_info[$i]['error_code']==2){?><td colspan="3" class="text-info">比赛双方获胜者未在规定时间内上报比赛数据, 本次挑战无效!</td><?php }else{?><?php }?>
            <?php if($task_info[$i]['error_code']==3){?><td colspan="3" class="text-info">因发生特殊情况，管理员取消了本次比赛!</td><?php }else{?><?php }?>
        </tr>
        <?php }else{?>
        <?php }?>

        <?php if($task_info[$i]['status']==1){?>
        <tr>
            <td colspan="4">
                <?php if($task_info[$i]['confirm']==null && $task_info[$i]['target_id']==$user_id){?>
                <button class="btn btn-sm btn-success accept-challenge" data-task_id="<?php echo $task_info[$i]['task_id'];?>" style="margin-right: 10px">接受挑战</button>
                <?php }?>

                <?php if($task_info[$i]['confirm']!=null && $task_info[$i]['status']==1){?>
                <button class="btn btn-sm btn-primary report-result" data-task_id="<?php echo $task_info[$i]['task_id'];?>" data-check_code="<?php if($task_info[$i]['founder_id']==$user_id){?> <?php echo $task_info[$i]['founder_check_code'];?> <?php }else{?> <?php echo $task_info[$i]['target_check_code'];?> <?php }?>">上报结果</button>
                <?php }?>
            </td>
        </tr>
        <?php }else{?>
        <?php }?>

    </table>
    <?php }?>

</div>

<div id="submit-content" style="display: none">
    <h4>我的确认码：<span style="color: cornflowerblue">#{my_check_code}</span></h4>
    <hr>
    <p style="color: green">仅由获胜方选手向对手索要确认码，并填写在下方进行获胜信息提交！</p>
    <div class="form-group">
        <label for="check-code">对手确认码:</label>
        <input onchange="getKeyCode(this)" id="check-code" class="form-control" placeholder="仅获胜方在这输入对手的确认码！">
    </div>
    <div class="form-group">
        <input onchange="getCheckBoxStatus(this)" id="submit-checkbox" type="checkbox" style="zoom: 150%;vertical-align:middle;display: inline">
        <label for="submit-checkbox" style="display: inline"><span style="color: red">（勾选）</span>本次比赛是我获胜，我保证比赛结果客观公正无欺诈，若有隐瞒我愿意接受禁赛处理!</label>
    </div>
</div>

<script>
    //确认码的值
    var key_code = "";
    //是否勾选条款
    var checkBox_status = "";

    //接受挑战按钮事件
    $(".accept-challenge").click(function (){
        task_id = $(this).data('task_id');
        showModal('提示', '是否确认接受挑战?', function (){
            acceptChallenge(task_id);
        });
    });

    //接受比赛
    function acceptChallenge(task_id)
    {
        $.post({
            url: "/api/setting",
            data: {action:"accept_challenge", task_id: task_id},
            success: function (response){
                showAlert('提示', response.message, response.status, function (){
                    location.reload();
                });
            },
            error: function (error){
                showAlert('出错啦', '连接服务器失败, 请稍后重试!', 'error');
            }
        })
    }

    //上报结果按钮
    $('.report-result').click(function (){
        const html_content = $('#submit-content').html().toString();
        var task_id = $(this).data('task_id');
        checkBox_status = false;
        key_code = "";
        showModal('<span style="color: red">仅由获胜方提交结果！！!</span>', html_content.replace('#{my_check_code}', $(this).data('check_code')), function () {
            uploadResult(task_id);
        })

    });

    //比赛结果上报
    function uploadResult(task_id)
    {
        if (key_code == "")
        {
            showAlert('提示', '输入的确认码不能为空！', 'error');
            return;
        }
        else if (checkBox_status == false)
        {
            showAlert('提示', '请勾选诚信须知条款！', 'error');
            return;
        }

        $.post({
            url: "/api/setting",
            data: {action:"uploadResult", task_id:task_id, check_code:key_code},
            success: function (response) {
                showAlert('提示', response.message, response.status, function () {
                    location.reload();
                })
            },
            error: function (error){
                showAlert('出错啦', '连接服务器失败, 请稍后重试!', 'error');
            }
        })
    }

    //动态监听确认码框中的值
    function getKeyCode(obj)
    {
        key_code = obj.value;
    }

    //动态监听选择框状态
    function getCheckBoxStatus(obj)
    {
        checkBox_status = obj.checked;
    }
</script>

<?php show_template("common_component");?>
<?php show_template("common_footer");?>

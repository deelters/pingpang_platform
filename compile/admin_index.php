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
                <a class="nav-link active" href="/admin/index">用户总览</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/task">挑战记录</a>
            </li>
        </ul>
    </div>

    <div style="margin-top: 20px">
        <button id="btn-add-user" class="btn btn-info" style="float: right;margin-bottom: 10px"><i class="fa fa-plus" aria-hidden="true"></i>&nbsp;新增用户</button>
        <select class="form-control" id="group-selector" style="margin-bottom: 20px">
            <option value="-1" <?php if($query_group_id == -1){?>selected<?php }else{?><?php }?>>所有学院组</option>
            <?php for($i=0;$i<$group_num;$i++){?>
            <option value="<?php echo $groups_info[$i]['group_id'];?>" <?php if($query_group_id == $groups_info[$i]['group_id']){?>selected<?php }else{?><?php }?>><?php echo $groups_info[$i]['group_name'];?></option>
            <?php }?>
        </select>
        <table class="table table-hover table-bordered" style="text-align: center">
            <thead>
            <tr>
                <th style="width: 9%">选手编号</th>
                <th style="width: 12%">姓名</th>
                <th style="width: 12%">学号</th>
                <th style="width: 10%">性别</th>
                <th style="width: 20%">学院组</th>
                <th style="width: 9%">当前积分</th>
                <th style="width: 9%">账户状态</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
                <?php for($i=0;$i<$total_num;$i++){?>
                <tr>
                    <td><?php echo $user_info_list[$i]['user_id'];?></td>
                    <td><?php echo $user_info_list[$i]['username'];?></td>
                    <td><?php echo $user_info_list[$i]['stu_number'];?></td>
                    <td><?php if($user_info_list[$i]['sex'] == 1){?>男<?php }else{?>女<?php }?></td>
                    <td><?php echo $user_info_list[$i]['group_name'];?></td>
                    <td>
                        <?php echo $user_info_list[$i]['score'];?>&nbsp;分
                    </td>
                    <td>
                        <?php if($user_info_list[$i]['status'] == 0){?><span class="text-warning">未激活</span><?php }else{?><?php }?>
                        <?php if($user_info_list[$i]['status'] == 1){?><span class="text-success">正常</span><?php }else{?><?php }?>
                        <?php if($user_info_list[$i]['status'] == 2){?><span class="text-info">被锁定</span><?php }else{?><?php }?>
                        <?php if($user_info_list[$i]['status'] == 3){?><span class="text-danger">禁赛中</span><?php }else{?><?php }?>
                    </td>
                    <td>
                       <button class="btn btn-success btn-sm btn-changePwd" data-user_id="<?php echo $user_info_list[$i]['user_id'];?>" data-username="<?php echo $user_info_list[$i]['username'];?>" data-stu_number="<?php echo $user_info_list[$i]['stu_number'];?>" style="margin-top: 4px" >重置密码</button>
                        <?php if($user_info_list[$i]['status'] == 2){?>
                       <button class="btn btn-primary btn-sm btn-lock" data-user_status="<?php echo $user_info_list[$i]['status'];?>" data-user_id="<?php echo $user_info_list[$i]['user_id'];?>" data-username="<?php echo $user_info_list[$i]['username'];?>" data-stu_number="<?php echo $user_info_list[$i]['stu_number'];?>" style="margin-top: 4px" >解锁账户</button>
                        <?php }else{?>
                       <button class="btn btn-warning btn-sm btn-lock" data-user_status="<?php echo $user_info_list[$i]['status'];?>" data-user_id="<?php echo $user_info_list[$i]['user_id'];?>" data-username="<?php echo $user_info_list[$i]['username'];?>" data-stu_number="<?php echo $user_info_list[$i]['stu_number'];?>" style="margin-top: 4px" >锁定账户</button>
                        <?php }?>
                        <!--                       <button class="btn btn-danger btn-sm" style="margin-top: 4px">禁赛</button>-->
                    </td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    </div>

</div>

<div id="add-user" style="display: none">
    <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">姓名</label>
            <div class="col-8">
                <input onchange="username = this.value" id="add-username" class="form-control" type="text">
            </div>
    </div>

     <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">学号</label>
            <div class="col-8">
                <input onchange="stu_num = this.value" id="add-stu-num" class="form-control" type="text">
            </div>
    </div>

    <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">QQ号</label>
            <div class="col-8">
                <input onchange="qq = this.value" id="add-qq" class="form-control" type="text">
            </div>
    </div>

    <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">性别</label>
            <div class="col-8">
                <select onchange="sex = this.value" class="form-control" id="add-sex">
                    <option value="1" selected>男</option>
                    <option value="0" >女</option>
                </select>
            </div>
    </div>

     <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">所在学院</label>
            <div class="col-8">
                <select onchange="group_id = this.value" class="form-control" id="add-group-id">
                    <?php for($i=0;$i<$group_num;$i++){?>
                    <option value="<?php echo $groups_info[$i]['group_id'];?>" <?php if($query_group_id == $groups_info[$i]['group_id']){?>selected<?php }else{?><?php }?>><?php echo $groups_info[$i]['group_name'];?></option>
                    <?php }?>
                </select>
            </div>
    </div>


</div>

<script>
    var username = "";
    var sex = 1;
    var group_id = 1;
    var stu_num = "";
    var rank_num = 0;
    var qq = "";

    $('#group-selector').change(function () {
        window.location = '/admin/index/' + $(this).val();
    });

    //重置密码按钮
    $('.btn-changePwd').click(function () {
        var user_id = $(this).data("user_id");
        var username = $(this).data("username");
        var stu_number = $(this).data("stu_number");

        showModal('提示', '是否确定重置该选手密码?<br><br>选手姓名: ' + username + '<br>选手学号: ' + stu_number, function () {
             resetpwd(user_id);
        });
    });

    //锁定用户按钮事件
    $('.btn-lock').click(function () {
        var user_id = $(this).data("user_id");
        var username = $(this).data("username");
        var stu_number = $(this).data("stu_number");
        var action_name = $(this).data('user_status') == 2 ? '解锁' : '锁定';

        showModal('提示', '是否确定' + action_name + '该选手账户?<br><br>选手姓名: ' + username + '<br>选手学号: ' + stu_number, function () {
             set_lock(user_id);
        });
    });

    //新增用户按钮事件
    $('#btn-add-user').click(function () {
        username = "";
        sex = 1;
        group_id = 1;
        stu_num = "";
        rank_num = 0;
        qq = "";
        showModal('添加用户', $('#add-user').html(), function () {
            addUser();
        })
    });

    //重置密码操作
    function resetpwd(user_id){
        $.post({
           url: "/api/setting",
           data: {action: 'reset_pwd', user_id: user_id},
           success: function (response) {
               showAlert('提示', response.message, response.status, function () {
                   location.reload();
               });
           },
            error: function () {
               showAlert('提示', '出现了未知的错误!', 'error');
            }
        });
    }

    //设置锁定状态
    function set_lock(user_id)
    {
         $.post({
           url: "/api/setting",
           data: {action: 'set_userLock', user_id: user_id},
           success: function (response) {
               showAlert('提示', response.message, response.status, function () {
                    location.reload();
               });
           },
            error: function () {
               showAlert('提示', '出现了未知的错误!', 'error');
            }
        });
    }

    //添加用户
    function addUser()
    {
        if (username == "")
        {
            showAlert('提示', '请输入选手姓名!', 'error');
            return;
        }
        else if (stu_num == "")
        {
            showAlert('提示', '请输入选手学号!', 'error');
            return;
        }
        else if(qq == "")
        {
             showAlert('提示', '请输入选手QQ号!', 'error');
            return;
        }

        $.post({
           url: "/api/setting",
           data: {action: 'adduser', username: username, stu_number: stu_num, group_id: group_id, sex: sex, qq_number: qq},
           success: function (response) {
               if (response.status == 'success') {
                   showAlert('提示', '用户注册成功, 请牢记以下信息!<br>选手编号: ' + response.data.user_id + '<br>用户姓名: ' + response.data.username + '<br>学号: ' + response.data.stu_number + '<br>性别：' + response.data.sex + '<br>学院组: ' + response.data.group_name + '<br>登录密码: ' + response.data.password, response.status, function () {
                       location.reload();
                   });
               }
               else {
                   showAlert('提示', response.message, response.status);
               }
           },
            error: function () {
               showAlert('提示', '出现了未知的错误!', 'error');
            }
        });


    }
</script>

<?php show_template("common_component");?>
<?php show_template("common_footer");?>

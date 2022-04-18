<?php show_template("common_header");?>
<style>
    label{
        text-align: end;
    }
</style>

<div class="container">
    <?php show_template("common_nav");?>
    <h3 style="text-align: center;margin-top: 10px">我的信息</h3>

    <div style="margin-top: 20px">
        <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">选手编号</label>
            <div class="col-8">
                <input class="form-control" disabled type="text" value="<?php echo $user_id;?>">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-3">姓名</label>
            <div class="col-8">
                <input class="form-control disabled" disabled type="text" value="<?php echo $user_info['username'];?>">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-3">学号</label>
            <div class="col-8">
                <input class="form-control disabled" disabled type="text" value="<?php echo $user_info['stu_number'];?>">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-3">性别</label>
            <div class="col-8">
                <input class="form-control disabled" disabled type="text" value="<?php if($user_info['sex'] == 1){?>男<?php }else{?>女<?php }?>">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">当前排名</label>
            <div class="col-8">
                <input class="form-control disabled" disabled type="text" value="<?php echo $my_rankNum;?>">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-3">所在学院</label>
            <div class="col-8">
                <input class="form-control disabled" disabled type="text" value="<?php echo $user_info['group_name'];?>">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-3">邮箱地址</label>
            <div class="col-8">
                <input class="form-control disabled" disabled type="text" value="<?php if($user_info['email_status']==1){?> <?php echo $user_info['email_address'];?> <?php }else{?> 尚未绑定邮箱 <?php }?>">
            </div>
        </div>

        <div class="form-group" style="text-align: center">
                <button id="changePwd-btn" class="btn btn-primary"><i class="fa fa-cog" aria-hidden="true"></i>&nbsp;修改登录密码</button>
                <?php if($user_info['email_status']==0){?>
                <button id="openEmail-btn" class="btn btn-info" style="margin-left: 8px"><i class="fa fa-envelope" aria-hidden="true"></i>&nbsp;绑定邮箱</button>
                <?php }else{?>
                <button id="closeEmail-btn" class="btn btn-danger" style="margin-left: 8px"><i class="fa fa-trash" aria-hidden="true"></i> 解绑邮箱</button>
                <?php }?>
        </div>
    </div>
</div>

<div id="changePwd-content" style="display: none">
     <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">原密码</label>
            <div class="col-8">
                <input onchange="old_pwd = this.value" id="old-pwd" class="form-control" type="password">
            </div>
    </div>

     <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">新密码</label>
            <div class="col-8">
                <input onchange="new_pwd = this.value" id="new-pwd" class="form-control" type="password">
            </div>
    </div>

     <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">确认新密码</label>
            <div class="col-8">
                <input onchange="check_pwd = this.value" id="check-pwd" class="form-control" type="password">
            </div>
    </div>
</div>

<!--绑定邮件模态款内容-->
<div class="modal fade" id="openEmail-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">邮箱绑定</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" style="font-size: larger">&times;</span>
        </button>
      </div>
      <div id="openEmail-content" class="modal-body">

        <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">邮箱地址</label>
            <div class="col-8">
                <input placeholder="请输入邮箱地址" id="email-address" class="form-control" type="text">
            </div>
        </div>

      <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">验证码</label>
            <div class="input-group-append col-8">
                <input placeholder="请输入验证码" id="code" class="form-control" type="text">
                <button id="btn-getCode" class="btn btn-secondary">获取验证码</button>
            </div>
        </div>


      </div>
      <div class="modal-footer">
<!--        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>-->
        <button style="width: 80%;margin: auto;" id="openEmail-sure" type="button" class="btn btn-info">确定绑定</button>
      </div>
    </div>
  </div>
</div>


<script>
    var old_pwd = "";
    var new_pwd = "";
    var check_pwd = "";
    var time_rest = 5 * 60;

    //修改密码按钮事件
    $('#changePwd-btn').click(function () {
        showModal('修改登录密码',$('#changePwd-content').html() , function () {
           changePwd();
           old_pwd = '';
           new_pwd = '';
           check_pwd = '';
        });
    });

    $('#btn-getCode').click(function () {
        if (!isEmailvalid($('#email-address').val())){
            show_newAlert('提示', '邮箱地址格式有误,请检查!');
            return;
        }

        var status = true;

        //执行发送邮件操作
        $.post({
            url: "/api/setting",
            data: {action: "getEmailCode", email_address: $('#email-address').val()},
            success: function (response) {
                if (response.status == 'wait'){
                    time_rest = response.data;
                    show_newAlert('提示', response.message);
                }
                else {
                    show_newAlert('提示', response.message, response.status);
                }
            },
            error: function (error) {
                status = false;
                show_newAlert('提示', '服务器连接失败!');
            }
        })

        //若发生错误则返回
        if(!status){
            return;
        }

        $('#btn-getCode').attr('disabled', 'disabled');
        var timer = setInterval(function () {
            time_rest--;
            if (time_rest == 0){
                clearTimeout(timer);
                $('#btn-getCode').removeAttr('disabled');
                time_rest = 5 * 60;
                $('#btn-getCode').html('获取验证码');
            }
            else {
                $('#btn-getCode').html(time_rest + '秒后可以重发');
            }
        }, 1000);
    });

    //绑定邮箱按钮
    $('#openEmail-btn').click(function () {
        $('#openEmail-modal').modal('show');
    });

    //确认提交绑定按钮事件
    $('#openEmail-sure').click(function () {
        if (!isEmailvalid($('#email-address').val())){
            show_newAlert('提示', '邮箱地址格式有误,请检查!');
            return;
        }

        if ($('#code').val() == ""){
            show_newAlert('提示', '请输入操作验证码!');
            return;
        }

        $.post({
            url: "/api/setting",
            data: {action: "checkEmailCode", email_address: $('#email-address').val(), code: $('#code').val()},
            success: function (response) {
                if (response.status == 'success'){
                    $('#openEmail-modal').modal('hide');
                    showAlert('提示', response.message, response.status, function () {
                        location.reload();
                    });
                }
                else {
                    show_newAlert('提示', response.message, response.status);
                }
            },
            error: function (error) {
                status = false;
                show_newAlert('提示', '服务器连接失败!');
            }
        })


    });

    //新封装模态框
    function show_newAlert(title, content, status = 'error')
    {
        $('#openEmail-modal .modal-footer').hide();
        showAlert(title, content, status, function () {
            $('#openEmail-modal .modal-footer').show();
        });
    }

    //修改密码
    function changePwd()
    {
        if (old_pwd == "")
        {
            showAlert('提示', '原密码不能为空!', 'error')
            return;
        }
        else if (new_pwd == "")
        {
            showAlert('提示', '新密码不能为空!', 'error')
            return;
        }
        else if (check_pwd != new_pwd)
        {
            showAlert('提示', '两次输入的新密码不一致!', 'error')
            return;
        }

        $.post({
            url: "/api/setting",
            data: {action: "change_pwd", old_pwd: old_pwd, new_pwd: new_pwd},
            success: function (response){
                showAlert('提示', response.message, response.status, function () {
                    if (response.status == "success")
                    {
                        location.reload();
                    }
                });
            },
            error: function (error){
                showAlert('提示', "出现了未知的错误！", 'error');
            }
        })
    }

    //校验验证码是否合法
    function isEmailvalid(email_address) {
        var regex = /^([0-9A-Za-z\-_\.]+)@([0-9a-z]+\.[a-z]{2,3}(\.[a-z]{2})?)$/g;
        return regex.test(email_address);
    }

    //解除邮箱绑定按钮
    $('#closeEmail-btn').click(function () {
        showModal('操作提示', '<h4>是否确定解绑当前邮箱?</h4><br><h4 style="color: red"><?php echo $user_info['email_address'];?></h4>', function () {
            cancel_email();
        })
    });

    //解除邮箱绑定
    function cancel_email()
    {
        $.post({
            url: "/api/setting",
            data: {action: "cancelEmail"},
            success: function (response){
                showAlert('提示', response.message, response.status, function () {
                    if (response.status == "success")
                    {
                        location.reload();
                    }
                });
            },
            error: function (error){
                showAlert('提示', "出现了未知的错误！", 'error');
            }
        })
    }
</script>

<?php show_template("common_component");?>
<?php show_template("common_footer");?>

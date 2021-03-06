<?php show_template("common_header");?>
<style>
    label{
        text-align: end;
    }
</style>

<div class="container">
    <?php show_template("common_nav");?>
    <h3 style="text-align: center;margin-top: 40px">平台账户激活</h3>
    <div style="margin-top: 20px">
    <p style="text-align: center;color: red">为确保账户安全，初次进入平台需要您强制修改登录密码, 修改后方可使用!</p>
        <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">原密码</label>
            <div class="col-8">
                <input id="old-pwd" class="form-control" type="password">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">新密码</label>
            <div class="col-8">
                <input id="new-pwd" class="form-control" type="password">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-3 text-nowrap">确认新密码</label>
            <div class="col-8">
                <input id="check-pwd" class="form-control" type="password">
            </div>
        </div>
        <br>
        <div class="form-group" style="text-align: center">
                <button id="changePwd-btn" class="btn btn-success" onclick="changePwd()">确认激活</button>
        </div>
    </div>
</div>

<script>
    //修改密码
    function changePwd()
    {
        var old_pwd = $('#old-pwd').val();
        var new_pwd = $('#new-pwd').val();
        var check_pwd = $('#check-pwd').val();

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
            data: {action: "active_account", old_pwd: old_pwd, new_pwd: new_pwd},
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
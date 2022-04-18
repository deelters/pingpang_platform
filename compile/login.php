<?php show_template("common_header");?>
<style>

    #top-title{
        margin-top: 4rem;
        text-align: center;
    }

    #form-area{
        margin-top: 2rem;
    }

    body{
       background-color: #f5f5f5;
    }
</style>

<div class="container">
    <div id="top-title">
    <img src="/source/image/logo.jpg" width="100px" height="100px" style="margin-bottom: 10px">
    <h3>CUIT乒乓球擂台赛<br>选手平台</h3>
    </div>

    <div id="form-area">
        <div class="form-group">
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                  <div class="input-group-text"><i class="fa fa-user"></i></div>
                </div>
                <input class="form-control" type="text" id="stu-num" placeholder="请输入你的学号">
            </div>
        </div>

        <div class="form-group">
             <div class="input-group mb-2">
                <div class="input-group-prepend">
                  <div class="input-group-text"><i class="fa fa-key"></i></div>
                </div>
                <input class="form-control" type="password" id="password" placeholder="请输入你的密码">
            </div>
        </div>

        <div class="form-group">
            <input style="display: inline-block;" class="form-control col-6" type="code" id="code" placeholder="请输入验证码" maxlength="4">
            <img id="code_img" style="margin-left: 1rem" height="32rem" width="120rem" src="/api/code">
        </div>


        <div class="form-group">
            <button class="form-control btn btn-success" onclick="checkLogin()">登录</button>
        </div>
        <h5 id="alert-tip" style="color: red;font-weight: bold"></h5>
    </div>
</div>

<script>
    //校验登录
    function checkLogin() {
        let stu_num = $("#stu-num").val();
        let password = $("#password").val();
        let code = $("#code").val();

        clearAlert();

        if (stu_num == ""){
            showAlert("用户名不能为空！");
            return;
        }
        else if (password == ""){
            showAlert("密码不能为空！");
            return ;
        }
        else if (code == ""){
            showAlert("验证码不能为空！");
            return ;
        }

        showAlert("正在验证登录,请稍等···");

        $.post({
            url: "/api/checklogin", data: { stu_num: stu_num, password: password, code: code }, success: function (result) {
                if (result.status == 'success') {
                    $("#alert-tip").html(result.message);
                    window.location = "/index";
                    return;
                }
                else{
                    $("#alert-tip").html(result.message);
                }
                $("#code").val("");
                $("#code_img").click();
            }
        });
    }

    function showAlert(content){
        $("#alert-tip").html(content);
    }

    function clearAlert(){
        $("#alert-tip").html("");
    }

     $("#code_img").click(function () {
        $("#code_img").attr("src", "/api/code/"+new Date().getTime());
    });
</script>
<?php show_template("common_footer");?>
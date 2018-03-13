<!DOCTYPE html>

<html>
<head>
    <title>用户登录</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/css/login.css" />
</head>
<body>
<div id="loginbox">
    <div class="icon-logo">
        <img src="/images/icon.jpg" />
    </div>

    <div class="control-group normal_text">
        <h1>
            CRM渠道管理系统
        </h1>
    </div>
        <div class="control-group">
            <div class="controls">
                <div class="main_input_box">
                    <span class="add-on bg_lg"><i class="fa fa-user"></i></span>
                    <input id="username" name="username" placeholder="用户名" type="text" />

                </div>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <div class="main_input_box">
                    <span class="add-on bg_ly"><i class="fa fa-lock"></i></span>
                    <input id="passwd" name="passwd" placeholder="密 码" type="password" />
                </div>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <div class="main_input_box">
                    <span class="add-on"></span>
                    <input type="submit" class="btn btn-success" value="登  录" />
                </div>
            </div>
        </div>
        <div class="error"></div>
    <div class="form-actions">
        <div class="cy">CopyRight &copy; 2016 &nbsp;&nbsp; Powered by MIAOKE</div>
    </div>
</div>


<script src="/js/jquery.min.js"></script>
<script>
        $(function () {
            $(".btn-success").click(function () {
                var uid = $("#username").val();
                var passwd = $("#passwd").val();

                if($.trim(uid) == ""){
                    $(".error").text("请填写用户名!");
                    return false;
                }

                if($.trim(passwd) == ""){
                    $(".error").text("请填写密码!");
                    return false;
                }

                $(".btn-success").val("正在登陆...").attr("disabled","true");
                $.post('/site/logon', {"username":uid, "passwd":passwd },function (res) {
                    var result = JSON.parse(res);
                    if(result.recode == "1"){
                        window.location.href = "/";
                    }else{
                        $(".error").text("用户名或密码不正确!");
                        $(".btn-success").val("登  录").removeAttr("disabled");
                    }
                });
            });
        });
</script>

</body>

</html>

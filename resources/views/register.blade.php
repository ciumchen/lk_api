<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>来客-{{$title}}</title>
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1,viewport-fit=cover,maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link href="/css/mui.css" rel="stylesheet" />
    <link href="/css/app.css" rel="stylesheet" />
    <link href="/css/login.css" rel="stylesheet" />
    <style>

    </style>
</head>
<body>


<div class="mui-content" id="content">
    <div class="login-box">
        <img src="/images/logo.png" alt="" class="logo">
        <div class="input-group">

            <input type="text" name="" id="phone" placeholder="请输入手机号" value="" class="input-class"/>
        </div>
        <div class="input-group display-flex display-between get-code-box">

            <input type="text" name="" id="verify_code" placeholder="请输入验证码" value="" class="border-right input-class flex-input-class"/>
            <div class='border-div'></div>
            <a href="javascript:;" class="black-color" id="get-code">获取验证码</a>


        </div>
        <div class="input-group">

            <input type="password" name="" id="password" placeholder="请输入密码" value="" class="input-class"/>
        </div>
        <div class="input-group">

            <input type="text" name="" id="invite_code" placeholder="请输入邀请码" value="{{$invite_code}}" class="input-class"/>
        </div>

    </div>

    <div class="bottom-button-box">
        <button type="button" class="login-btn" id="register-btn">
            注册
        </button>
        <p class="black-color"><a href="/download-app" class="black-color" >下载APP></a></p>
    </div>
</div>

</body>
<script src="/js/mui.js"></script>
<script src="https://cdn.staticfile.org/js-sha256/0.9.0/sha256.min.js"></script>
<script src="/js/app.js?v=21"></script>

<script type="text/javascript">
    mui.init({
        swipeBack:true //启用右滑关闭功能
    });
    document.getElementById("register-btn").addEventListener("tap",function () {

        register();
    });
    var t1;
    var i = 60;
    var get_code_btn = document.getElementById("get-code");
    get_code_btn.addEventListener("tap",function () {
        var phone = document.getElementById('phone').value;

        get_code_btn.style.cssText += 'pointer-events: none;color:#ccc;';
        mui.ajax(host+"/api/verify-codes", {
            data:
                {
                    phone:phone,
                    type:"register",
                },
            dataType: 'json',//服务器返回json格式数据
            type: 'post',//HTTP请求类型
            timeout:20000,//超时时间设置为20秒；
            success: function (data)
            {
                if (data.code === 0)
                {
                    t1 = setInterval(refreshQuery, 1000);

                    mui.toast('发送成功!');

                }
                else
                {
                    mui.toast(data.msg);
                    get_code_btn.style.cssText += 'pointer-events: auto;color:#353434;';
                }
            },
            error:function(xhr,type,errorThrown)
            {
                mui.toast("网络错误");
            }
        });

    })
    function refreshQuery() {
        i--;
        get_code_btn.innerHTML = '获取验证码 ' + i;
        if (i < 1) {
            window.clearInterval(t1);
            get_code_btn.style.cssText += 'pointer-events: auto;color:#353434;';
            get_code_btn.innerHTML = '获取验证码';
            i = 60;
        }
    }
    function register()
    {

        var phone = document.getElementById("phone").value;
        var password = document.getElementById("password").value;
        var invite_code = document.getElementById("invite_code").value;
        var verify_code = document.getElementById("verify_code").value;

        var p = 'lk' + phone + password;
        var encrypt_password = sha256(p);


        mui.ajax(host+"/api/register", {
            data: {
                phone:phone,
                password:encrypt_password,
                invite_code:invite_code,
                verify_code:verify_code
            },
            dataType: 'json',//服务器返回json格式数据
            type: 'post',//HTTP请求类型
            success: function (data) {

                if (data.code === 0)
                {
                    mui.alert("注册成功,下载APP登录",function(){

                        setTimeout(function(){
                            window.location.href = '/download-app';
                        },1000);
                    });
                }
                else
                {
                    mui.toast(data.msg);
                }
            },
            error:function(xhr,type,errorThrown){

                mui.toast("网络错误");
            }
        });
    }

</script>
</html>

<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>登录</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
body {
    font-family: "微软雅黑";
    font-size: 14px;
    background: url(./public/resource/detail/login/images/bg.jpg) fixed center center;
}
    
*{
    margin: 0;
    padding: 0;
}
.logo_box{
    width: 280px;
    height: 490px;
    padding: 35px;
    color: #EEE;
    position: absolute;
    left: 50%;
    top:100px;
    margin-left: -175px;
}
.logo_box h3{
    text-align: center;
    height: 20px;
    font: 20px "microsoft yahei",Helvetica,Tahoma,Arial,"Microsoft jhengHei",sans-serif;
    color: #FFFFFF;
    height: 20px;
    line-height: 20px;
    padding:0 0 35px 0;
}
.forms{
    width: 280px;
    height: 485px;
}
.logon_inof{
    width: 100%;
    min-height: 450px;
    padding-top: 35px;
    position: relative;
}       
.input_outer{
    height: 46px;
    padding: 0 5px;
    margin-bottom: 20px;
    border-radius: 50px;
    position: relative;
    border: rgba(255,255,255,0.2) 2px solid !important;
}
.u_user{
    width: 25px;
    height: 25px;
    background: url(image/login_ico.png);
    background-position:  -125px 0;
    position: absolute;
    margin: 12px 13px;
}
.us_uer{
    width: 25px;
    height: 25px;
    background-image: url(image/login_ico.png);
    background-position: -125px -34px;
    position: absolute;
    margin: 12px 13px;
}
.l-login{
    position: absolute;
    z-index: 1;
    left: 50px;
    top: 0;
    height: 46px;
    font: 14px "microsoft yahei",Helvetica,Tahoma,Arial,"Microsoft jhengHei";
    line-height: 46px;
}
label{
    color: rgb(255, 255, 255);
    display: block;
}
.text{
    width: 220px;
    height: 46px;
    outline: none;
    display: inline-block;
    font: 14px "microsoft yahei",Helvetica,Tahoma,Arial,"Microsoft jhengHei";
    margin-left: 50px;
    border: none;
    background: none;
    line-height: 46px;
}
/*///*/
.mb2{
    margin-bottom: 20px
}
.mb2 a{
    text-decoration: none;
    outline: none;
}
.submit {
    padding: 15px;
    margin-top: 20px;
    display: block;
}
.act-but{
    height: 20px;
    line-height: 20px;
    text-align: center;
    font-size: 20px;
    border-radius: 50px;
    background: #0096e6;
}
/*////*/
.check{
    width: 280px;
    height: 22px;
}
.clearfix::before{
    content: "";
    display: table;
}
.clearfix::after{
    display: block;
    clear: both;
    content: "";
    visibility: hidden;
    height: 0;
}
.login-rm{
    float: left;
}
.login-fgetpwd {
    float: right;
}
.check{
    width: 18px;
    height: 18px;
    background: #fff;
    border: 1px solid #e5e6e7;
    margin: 0 5px 0 0;
    border-radius: 2px;
}
.check-ok{
    background-position: -128px -70px;
    width: 18px;
    height: 18px;
    display: inline-block;
    border: 1px solid #e5e6e7;
    margin: 0 5px 0 0;
    border-radius: 2px;
    vertical-align: middle
}
.checkbox{
    vertical-align: middle;
    margin: 0 5px 0 0;
}

/*=====*/
/*其他登录口*/
.logins{
width: 280px;
height: 27px;
line-height: 27px;
float:left;
padding-bottom: 30px;
}
.qq{
    width: 27px;
    height: 27px;
    float: left;
    display: inline-block;
    text-align: center;
    margin-left: 5px;
    margin-right: 18px;
}
.wx{
    width: 27px;
    height: 27px;
    text-align: center;
    line-height: 27px;
    display: inline-block;
    margin: 5px 18px auto 5px;
}
.wx img{
    width: 16px;
    height: 16px;
    float: left;
    line-height: 27px;
    text-align: center;
}
/*////*/
.sas{
    width: 280px;
    height: 18px;
    float: left;
    color: #FFFFFF;
    text-align: center;
    font-size: 16px;
    line-height: 16px;
    margin-bottom: 50px;
}
.sas a{
    width: 280px;
    height: 18px;
    color: #FFFFFF;
    text-align: center;
    line-height: 18px;
    text-decoration: none;
}
input{
    outline:none;
    -moz-appearance:none; /* Firefox */
    -webkit-appearance:none; /* Safari 和 Chrome */
    -webkit-apperance:normal;
}
input:-webkit-autofill {
     -webkit-box-shadow: 0 0 0 1000px white inset !important;
 }
 input:-webkit-autofill {
   transition: background-color 5000s ease-in-out 0s;
}
</style>
<link rel="stylesheet" type="text/css" href="./public/public/jqueryUI/ui-1.11.2/jquery-ui.min.css">
<script type="text/javascript" src="./public/public/jquery/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="./public/public/jqueryUI/ui-1.11.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="./public/public/js/base.js"></script>
<link rel="stylesheet" type="text/css" href="./public/public/dialog/dialog.css">
<script type="text/javascript" src="./public/public/dialog/dialog.js"></script>
<script type="text/javascript" src="./public/resource/javascript/framework.js"></script>
</head>

<body>
    <div class="logo_box">
        <h3>欢迎登录云平台</h3>
        <form id="_login_form"  name="_login_form" action="<?php echo U('/Admin/Login/dologin');?>" method="post" onsubmit="return false;" >
                <!-- <input type="text" name="a" value=" " style="display:none;"> -->
                <input type="password" name="b" value=" " style="display:none;">
            <div class="input_outer">
                <span class="u_user"></span>
                <input name="account" class="text" AUTOCOMPLETE="off" onfocus=" if(this.value=='输入用户名登录') this.value=''" onblur="if(this.value=='') this.value='输入用户名登录'" value="输入用户名登录" style="color: #fff !important" type="text">
            </div>
            <div class="input_outer">
                <span class="us_uer"></span>
                <label class="l-login login_password" style="color: rgb(255, 255, 255);display: block;">输入密码</label>
                <input name="password" id="password" AUTOCOMPLETE="off" class="text" style="color: #FFFFFF !important; position:absolute; z-index:100;" onfocus="pass_focus()"  onblur="if(this.value=='') $('.login_password').show()" value="" type="text">
            </div>
            
            <div class="mb2"><a class="act-but submit" href="javascript:;" style="color: #FFFFFF" onclick="Login()">登录</a></div>
            
        </form>
    </div>
</body>
<script type="text/javascript" src="./public/resource/js/login.js"></script>
<script type="text/javascript">
    function pass_focus(){
        $("#password").attr('type','password');
        $('.login_password').hide();
    }
</script>
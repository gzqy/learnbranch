<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php include __DIR__.'/../Public/Head.php';?>

    </head>

    <body>
        <div class="page">
            <!--header begin-->
            <header>
                <div class="bigcontainer">
                    <div id="logo">
                        <a href="./">VAA 先锋</a>
                    </div>

                </div>
            </header>
            <!-- the main menu-->
            <div class="ui teal inverted menu">
                <div class="bigcontainer">
                    <div class="right menu" style="height:45px;">


                    </div>
                </div>
            </div>
            <!--the main content begin-->
            <div class="container">
                <!--the content-->
                <div class="ui grid">
                    <div class="one column row">
                        <div class="column" >

                            <div style="margin:50px auto;width:600px;">

                                <form class="ui form segment"  id="_login_form" name="_login_form" action="/login/toForget" method="post" onsubmit="return false;"   >

                                    <div class="field">
                                        <label>新的登入密码</label>
                                        <div class="ui left icon input">
                                            <i class="lock icon"></i>
                                            <input type="password" name="password" >
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>再次输入登入密码</label>
                                        <div class="ui left icon input">
                                            <i class="lock icon"></i>
                                            <input type="password" name="apassword" >
                                        </div>
                                    </div>
                                    <input type="hidden" name="forget_key"  value="<?=$forget_key?>">
                                    <div class="ui blue button" style="padding: 10px 50px" onclick="Login();">
                                        更改密码
                                    </div>


                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php $controller->loadAssets('resource/js/login.js')?>
    </body>
</html>

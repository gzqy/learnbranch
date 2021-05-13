<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="/public/public/nav/css/font-awesome.css" rel="stylesheet">
<script type="text/javascript" src="/public/public/nav/js/jquery-1.10.1.min.js"></script>
<script type="text/javascript" src="/public/public/nav/js/google-maps.js"></script>
<title><?php echo ($SEO['title']); ?></title>
<link rel="stylesheet" type="text/css" href="/public/resource/css/global.css">
<link rel="stylesheet" type="text/css" href="/public/resource/css/main.css">
<link rel="stylesheet" type="text/css" href="/public/public/jqueryUI/ui-1.11.2/jquery-ui.min.css">
<script type="text/javascript" src="/public/public/jquery/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="/public/public/jqueryUI/ui-1.11.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="/public/public/js/base.js"></script>
<link rel="stylesheet" type="text/css" href="/public/public/dialog/dialog.css">
<script type="text/javascript" src="/public/public/dialog/dialog.js"></script>
<script type="text/javascript" src="/public/resource/javascript/framework.js"></script>
<link rel="stylesheet" type="text/css" href="/public/resource/css/public.css">
<link rel="stylesheet" type="text/css" href="/public/public/css/public.css">
<script type="text/javascript" src="/public/public/js/handle.js"></script>

<style type="text/css">
  .inner-container::-webkit-scrollbar {
    display: none;

</style>
<body style="overflow-y: hidden;">
    <!-- 上部固定栏 -->
    <div class="head">
        <div class="mes">
            <img src="/<?php echo ($SEO['logo']); ?>" style="display:block;width:100%;margin-top:0 auto;margin-left:0px;">
        </div>
        <div class="about">
            <div class="something">
                <p><b><?php echo ((isset($ACCOUNT["name"]) && ($ACCOUNT["name"] !== ""))?($ACCOUNT["name"]):"欢迎登陆"); ?></b><em>▼</em></p>
            </div>
             <div class="something" id="_all_online_box" style="float:right;margin-right:20px;">
             <p>
                <span >在线设备 <span id="_all_online_drive" style="margin-right: 20px"></span></span>
                <span id="_all_online_account_box">在线账号 <span id="_all_online_account"></span></span>
                </p>
            </div>
        </div>
    </div>
    <!-- 上部固定栏 -->
<!--<?php  print_r($nav);?>-->
    <!-- 左侧一级导航 -->
    <div style="width:11%;height:calc(100% - 53px);background: #222;margin-top:53px;overflow-y: hidden; line-height:24px;position: relative;">
      <div class="inner-container" style="width:100%;height:100%;margin: 0;overflow-y: scroll;position: absolute; left: 0;">
        <ul class="vertical-nav dark red">

            <?php if(is_array($nav)): foreach($nav as $key=>$vo): if($nav_id == $vo['id']): ?><li class="active"><a href="<?php echo ($vo['a']); ?>" ><?php echo ($vo['name']); ?></a></li>
            <?php else: ?>    
                <li><a href="<?php echo ($vo['a']); ?>" ><?php echo ($vo['name']); ?>   </a></li><?php endif; endforeach; endif; ?>
        </ul>
      </div>
   </div>

      
        
    <!-- 左侧一级导航 -->
    <ol class="setNav" style="background-color:white;border:1px #ccc solid;">
        <!-- <li><a href="##">账号信息</a></li> -->
        <!-- <li><a href="##">实名认证</a></li> -->
        <li><a href="<?php echo U('Admins/edit',array('id'=>$ACCOUNT['id']));?>">修改密码</a></li>
        <li><a href="<?php echo U('Login/Logout');?>">退出账号</a></li>
    </ol>
<script type="text/javascript">
    $(function(){
       // 点击切换设置
       $('.head .about .something p').click(function(event) {
          if($(this).children('em').html() == '▼'){
            $(this).children('em').html('▲');
            $('.setNav').show();
          }else{
            $(this).children('em').html('▼');
            $('.setNav').hide();
          }
       });
       // 点击切换设置
})
    function isSupportNotify(){
        if (window.Notification) {
            // 支持
            // console.log("支持"+"Web Notifications API");
            this.isAllowNotify()
        } else {
            // 不支持
            console.log("不支持"+"Web Notifications API");
        }
    }
    //通知功能 有骚扰用户的嫌疑，让用户根据自己喜好选择是否开启通知权限
    function isAllowNotify(){
        if(window.Notification && Notification.permission !== "denied") {
            Notification.requestPermission(function(status) {
                if (status === "granted") {
                } else{
                    alert('拒绝通知就无法及时看到来电弹屏了哦！如要接受请在设置中选择允许接收通知。');
                }
            });
        }
    }
    isSupportNotify()

</script>
<script type="text/javascript" src="./public/resource/js/crm.js?3"></script>

<!--<script type="text/javascript" src="./public/resource/jedate/jedate.js"></script>-->
<script type="text/javascript" src="./public/resource/jedate-6.5.0/src/jedate.js"></script>
<link rel="stylesheet" type="text/css" href="./public/resource/jedate-6.5.0/skin/jedate.css">
<link rel="stylesheet" type="text/css" href="./public/resource/detail/newShop/css/nav.css">
<link rel="stylesheet" type="text/css" href="./public/resource/detail/newShop/css/right.css">
<link rel="stylesheet" type="text/css" href="./public/resource/detail/newShop/css/tab.css">
<link rel="stylesheet" type="text/css" href="./public/resource/detail/newShop/css/search.css">
<link rel="stylesheet" type="text/css" href="./public/resource/detail/newShop/css/list.css">
<link rel="stylesheet" type="text/css" href="./public/resource/detail/newShop/css/style.css">
<link rel="stylesheet" type="text/css" href="./public/resource/detail/newShop/css/page.css">

<div class="content" style="background-color: #fff;">
    <div class="con">
        <dl class="nav">
            <dt><b>●</b>版本管理</dt>
            <?php if(is_array($second_nav)): foreach($second_nav as $key=>$vo): if($vo["id"] == $second_nav_id): ?><dd class="bg"><a href="javascript:;"><b>●</b><?php echo ($vo["name"]); ?></a></dd>
                <?php else: ?>
                    <dd ><a href="<?php echo ($vo["a"]); ?>"><b>●</b><?php echo ($vo["name"]); ?></a></dd><?php endif; endforeach; endif; ?>
            
        </dl>
        <div class="right" >
            <div class="formation clearfix" style="margin-top: 10px;">
                <table>
                    <tr>
                        <th>版本</th>
                        <th>地址</th>
                        <th>是否启用</th>
                        <th>更新内容</th>
                        <th>操作</th>
                    </tr>
                </table>
            </div>

            <div class="formation clearfix" style="border: 1px solid #e7e7e7;border-top: none;">
                <table >
                    <?php if(is_array($list)): foreach($list as $key=>$val): ?><tr class="one">
                        <td><?php echo ($val["version"]); ?></td>
                        <td><?php echo ($val["url"]); ?></td>
                        <td><?php echo ($val["status"]); ?></td>
                        <td><?php echo ($val["content"]); ?></td>
                        <td>
                            <a href="<?php echo U('/AppVersion/add',array('id'=>$val['id']));?>">编辑</a>
                            <!--<i>|</i>-->
                            <!--<a href="javascript:;" onclick="versionDelete(<?php echo ($val['id']); ?>)">删除</a>-->
                        </td><?php endforeach; endif; ?>
                </table>
            </div>

            <div class="pages" style="font-size: 14px;">
                <!--<?php echo ($Page); ?>-->
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    if(document.getElementById("start-one")){
        jeDate("#start-one",{
            minDate:"1900-01-01",              //最小日期
            maxDate:"2099-12-31",              //最大日期
            method:{
                choose:function (params) {
                }
            },
            format: "YYYY-MM-DD hh:mm:ss"
        });
    }
    if(document.getElementById("start-two")){
        jeDate("#start-two",{
            minDate:"1900-01-01",              //最小日期
            maxDate:"2099-12-31",              //最大日期
            method:{
                choose:function (params) {
                }
            },
            format: "YYYY-MM-DD hh:mm:ss"
        });
    }
    if(document.getElementById("start-three")){
        jeDate("#start-three",{
            minDate:"1900-01-01",              //最小日期
            maxDate:"2099-12-31",              //最大日期
            method:{
                choose:function (params) {
                }
            },
            format: "YYYY-MM-DD hh:mm:ss"
        });
    }
    if(document.getElementById("start-four")){
        jeDate("#start-four",{
            minDate:"1900-01-01",              //最小日期
            maxDate:"2099-12-31",              //最大日期
            method:{
                choose:function (params) {
                }
            },
            format: "YYYY-MM-DD hh:mm:ss"
        });
    }
</script>
<script type="text/javascript" src="./public/resource/js/device.js"></script>
<script>
    function versionDelete(id){
        dialog.tip({
            msg:'确定要删除吗?',
            buttons:{
                "Ok": { text:'确定',"class": 'btn-done', click: function() {
                    ajax({
                        type: "POST",
                        dataType: 'json',
                        url: 'index.php?s=/AppVersion/delete',
                        data: "id="+id,
                        success: function(rs){
                            dialog.tip({
                                msg:rs.msg,
                                buttons:{
                                    "Done": { text:'确定',"class": 'btn-done', click: function() { window.location.reload();   } }
                                }
                            });
                        }
                    });
                }},
                "Cancel": { text:'取消',"class": '', click: function() {  dialog.hide(); } }
            }

        });
    }
</script>
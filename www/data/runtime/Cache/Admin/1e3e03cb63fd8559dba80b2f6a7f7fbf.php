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
            <dt><b>●</b>账号管理</dt>
            <dd class="bg"><a href="javascript:;"><b>●</b>账号管理</a></dd>
        </dl>
        <div class="right" >
            <div class="aps-tab-bar aps-tab-bar-type-card aps-tab-bar-align-left aps-tab-bar-size-small">
                <div class="aps-tab-bar-wrapper" style="width: 100%;">
                    <div class="aps-tab-item-wrapper">
                        <div class="aps-tab-label aps-tab-label-active aps-tab-label-col-5">
                            <span class="aps-tab-label-inner"><a class="tabs" href="javascript:;">账号列表</a></span>
                        </div>
                        <div class="aps-tab-label  aps-tab-label-col-5">
                            <span class="aps-tab-label-inner"><a class="tabs" href="<?php echo U('Admins/add');?>">添加账号</a></span>
                        </div>

                    </div>
                </div>
            </div>

            <form class="clearfix search" id="_form1" method="post" action="<?php echo U('/Admins/index');?>">
                    <div class="filter">
                        <div class="aps-widget aps-ani-transition aps-grid aps-grid-fluid aps-state-visible">
                            <div class="aps-ani-transition aps-grid-row aps-state-visible">
                                <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 账号名称:</div>
                                <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;"><input type="text" placeholder="请填写账号名称" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small" name="name" value="<?php echo ($name); ?>"></div>

                                <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 登录账号:</div>
                                <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;"><input type="text" placeholder="请填写登录账号" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small" name="account" value="<?php echo ($account); ?>"></div>
                                
                                <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 账号状态:</div>
                                <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;">
                                    <select name="status" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                                        <?php if($status == 1): ?><option selected value="1">在线账号</option>
                                            <option value="2">离线账号</option>
                                            <option value="0">所有账号</option>
                                        <?php elseif($status == 2): ?>
                                            <option value="1">在线账号</option>
                                            <option selected value="2">离线账号</option>
                                            <option value="0">所有账号</option>
                                        <?php else: ?>
                                            <option value="1">在线账号</option>
                                            <option value="2">离线账号</option>
                                            <option selected value="0">所有账号</option><?php endif; ?>    
                                    </select>
                                </div>
                            </div>
                            
                            <!--<div class="aps-ani-transition aps-grid-row aps-state-visible">-->
                                <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 关键名词:</div>-->
                                <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;"><input type="text" placeholder="请填写关键名词" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small" name="keywords" value="<?php echo ($keywords); ?>"></div>-->

                                <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 添加时间:</div>-->
                                <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;"><input type="text" id="start-one" placeholder="最小值" class="datainp aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small" name="start_date" value="<?php echo ($start_date); ?>" readonly></div>    -->

                                <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 添加时间:</div>-->
                                <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;"><input type="text" id="start-two" placeholder="最大值" class=" datainp aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small" name="end_date" value="<?php echo ($end_date); ?>" readonly></div>-->
                            <!--</div>-->

                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                                 style="padding: 8px;"> 选择分配账号:
                            </div>
                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                                 style="padding: 8px;">
                                <select id="select_userid" name="select_userid" style="width: 100px"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                                    <option value="0">选择账号</option>
                                    <?php if(is_array($users)): foreach($users as $key=>$vo): if($vo["id"] == $select_userid): ?><option selected value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option>
                                            <?php else: ?>
                                            <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; ?>
                                </select>
                            </div>
                            <button class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-normal aps-size-small btn"
                                    style="margin-top: 10px" type="button"
                                    onclick="telToAccount('<?php echo U('Admins/AccountToAccount');?>',0)">分配
                            </button>
                            <button class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-normal aps-size-small btn"
                                    style="margin-top: 10px" type="button"
                                    onclick="telToAccount('<?php echo U('Admins/AccountToAccount');?>',1)">取消分配
                            </button>
                            <div class="aps-ani-transition aps-grid-row aps-state-visible">
                                <div class="aps-ani-transition aps-grid-col aps-grid-col-md-24 aps-state-visible search-button" style="padding: 8px;">
                                    <button type="button" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-primary aps-size-small btn" onclick="$('#_form1').submit();">搜索</button>
                                    <button type="button" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-normal aps-size-small btn" onclick="$('input').val('')">重置</button>
                                    <button type="button" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-normal aps-size-small btn float-btn" onclick="excel('<?php echo U('Admins/index_excel');?>')">导出列表</button>
                                </div>
                            </div>
                        </div>
                    </div>
            </form>
            <input type="hidden" name="gdata" id="gdata" value='<?php echo ($gdata); ?>' />
            <div class="formation clearfix" style="margin-top: 10px;">
                <table>
                    <tr>
                        <th>账号名称</th>
                        <th style="width: 40px;">状态</th>
                        <th>账号</th>
                        <th>电话</th>
                        <th>邮箱</th>
                        <th>添加时间</th>
                        <th>最近登录</th>
                        <th>最后在线</th>
                        <th style="width:200px">操作</th>
                    </tr> 
                </table>
            </div>

            <div class="formation clearfix" style="border: 1px solid #e7e7e7;border-top: none;">
                    <table >
                        <?php if(is_array($data)): foreach($data as $key=>$val): ?><tr class="one">
                            <td style="color:#06C;"><?php echo ($val['name']); ?></td>
                            <td style="width: 40px;">
                                <?php if($val['is_online'] == 1): ?><img src="./public/resource/images/online.png" width=30% title='在线'>
                                <?php else: ?>
                                    <img src="./public/resource/images/off.png" width=30% title="离线"><?php endif; ?>
                            </td>
                            <td><?php echo ($val["account"]); ?></td>
                            <td><?php echo ($val["tel"]); ?></td>
                            <td><?php echo ($val["email"]); ?></td>
                            <td><?php echo (date("Y-m-d H:i:s",$val["add_time"])); ?></td>
                            <td><?php echo (date("Y-m-d H:i:s",(isset($val["login_time"]) && ($val["login_time"] !== ""))?($val["login_time"]):"尚未登录")); ?></td>
                            <td><?php echo ($val["last_time"]); ?></td>
                            <td  style="width:200px">
                                <a href="<?php echo U('/Admins/logs',array('id'=>$val['id']));?>">日志</a><i>|</i>
                                <a href="<?php echo U('/Admins/edit',array('id'=>$val['id']));?>">编辑</a><i>|</i>
                                <a href="<?php echo U('Admins/delete',array('id'=>$val['id']));?>">删除</a>
                            </td>
                        </tr><?php endforeach; endif; ?>
                    </table>
            </div>
            
            <div class="pages" style="font-size: 14px;">
                <?php echo ($Page); ?>
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
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

<link rel="stylesheet" type="text/css" href="/public/resource/detail/newShop/css/style.css">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/public/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/public/assets/vendor/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/public/assets/vendor/linearicons/style.css">
    <link rel="stylesheet" href="/public/assets/vendor/chartist/css/chartist-custom.css">
    <link rel="stylesheet" href="/public/assets/css/main.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">
</head>

<div class="content" style="overflow-y:auto;background-color: #F3F5F8">
    <div style="margin-left: 20px" id="tel_layer_area">
    </div>
        <div id="wrapper">
            <div class="main">
                <!-- MAIN CONTENT -->
                <div class="main-content">
                    <div class="container-fluid">
                        <!-- 登录账户基本信息 -->
                        <div class="panel panel-headline">
                            <div class="panel-body">
                                <h4 style="width:auto;display: inline-block;">本账号登录信息</h4>
                                <a href="javascript:;" style="text-decoration: none;display: inline-block;background: 0 0;float: right;font-size: 18px;" onclick="togle($(this))"><i class="fa fa-chevron-up"></i></a>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-3" style="width:33.33%">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-registered"></i></span>
                                            <p>
                                                <span class="number"><?php echo (date("Y-m-d H:i:s",$ACCOUNT['add_time'])); ?></span>
                                                <span class="title">注册时间</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="width:33.33%">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-lastfm-square"></i></span>
                                            <p>
                                                <span class="number"><?php echo (date("Y-m-d H:i:s",$account_login['login_time'])); ?></span>
                                                <span class="title">上次登录</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="width:33.33%">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-buysellads"></i></span>
                                            <p>
                                                <span class="number"><?php echo (date("Y-m-d H:i:s",$account_login['last_time'])); ?></span>
                                                <span class="title">最后在线</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9">
                                        <div id="headline-chart1" class="ct-chart"></div>
                                    </div>
                                    <div class="col-md-3">

                                        <div class="weekly-summary text-right" onclick="window.location.href='<?php echo U('Device/status');?>'">
                                            <span class="number"><?php echo ($account_devices["total"]); ?></span> <span class="percentage"><i class="fa fa-caret-up text-success"></i> </span>
                                            <span class="info-label">权限设备</span>
                                        </div>
                                        <div class="weekly-summary text-right" onclick="window.location.href='<?php echo U('Attention/index');?>'">
                                            <span class="number"><?php echo ($att["device"]); ?></span> <span class="percentage"><i class="fa fa-caret-up text-success"></i></span>
                                            <span class="info-label">关注设备</span>
                                        </div>
                                        <div class="weekly-summary text-right" onclick="window.location.href='<?php echo U('Attention/logList');?>'">
                                            <span class="number"><?php echo ($att["log"]); ?></span> <span class="percentage"><i class="fa fa-caret-down text-danger"></i></span>
                                            <span class="info-label">关注记录</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!---管理员相关信息-->
                        <div class="panel panel-headline">
                            <div class="panel-body">
                                <h4 style="width:auto;display: inline-block;">账号信息</h4>
                                <a href="javascript:;" style="text-decoration: none;display: inline-block;background: 0 0;float: right;font-size: 18px;" onclick="togle($(this))"><i class="fa fa-chevron-up"></i></a>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-3" >
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-google-wallet"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($accounts["total"]); ?></span>
                                                <span class="title">账户总数</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-line-chart"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($accounts["inline"]); ?></span>
                                                <span class="title">在线账户</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-toggle-off"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($accounts["out"]); ?></span>
                                                <span class="title">离线账户</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-bar-chart"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($accounts["round"]); ?></span>
                                                <span class="title">账户在线率</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9">
                                        <div id="headline-chart" class="ct-chart"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="weekly-summary text-right" onclick="window.location.href='<?php echo U('Admins/index');?>'">
                                            <span class="number"><?php echo ($accounts["total"]); ?></span> <span class="percentage"><i class="fa fa-caret-up text-success"></i> </span>
                                            <span class="info-label">全部账户</span>
                                        </div>
                                        <div class="weekly-summary text-right" onclick="window.location.href='<?php echo U('Admins/index',array('status'=>2));?>'">
                                            <span class="number"><?php echo ($accounts["inline"]); ?></span> <span class="percentage"><i class="fa fa-caret-up text-success"></i></span>
                                            <span class="info-label">在线账户</span>
                                        </div>
                                        <div class="weekly-summary text-right" onclick="window.location.href='<?php echo U('Admins/index',array('status'=>1));?>'">
                                            <span class="number"><?php echo ($accounts["out"]); ?></span> <span class="percentage"><i class="fa fa-caret-down text-danger"></i> </span>
                                            <span class="info-label">离线账户</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!---设备相关信息-->
                        <div class="panel panel-headline">  
                            <div class="panel-body">
                                <h4 style="width:auto;display: inline-block;">设备信息</h4>
                                <a href="javascript:;" style="text-decoration: none;display: inline-block;background: 0 0;float: right;font-size: 18px;" onclick="togle($(this))"><i class="fa fa-chevron-up"></i></a>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-google-wallet"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($devices["total"]); ?></span>
                                                <span class="title">设备总数</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-caret-up"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($devices["inline"]); ?></span>
                                                <span class="title">在线设备</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-caret-down"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($devices["out"]); ?></span>
                                                <span class="title">离线设备</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-bar-chart"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($devices["round"]); ?></span>
                                                <span class="title">设备在线率</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9">
                                        <div id="headline-chart2" class="ct-chart"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="weekly-summary text-right">
                                            <span class="number"><?php echo ($devices["total"]); ?></span> <span class="percentage"><i class="fa fa-caret-up text-success"></i> </span>
                                            <span class="info-label">设备总数</span>
                                        </div>
                                        <div class="weekly-summary text-right">
                                            <span class="number"><?php echo ($devices["inline"]); ?></span> <span class="percentage"><i class="fa fa-caret-up text-success"></i> <?php echo ($devices["round"]); ?></span>
                                            <span class="info-label">在线设备</span>
                                        </div>
                                        <div class="weekly-summary text-right">
                                            <span class="number"><?php echo ($devices["out"]); ?></span> <span class="percentage"><i class="fa fa-caret-down text-danger"></i></span>
                                            <span class="info-label">离线设备</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!---记录相关信息-->
                        <div class="panel panel-headline">
                            <div class="panel-body">
                                <h4 style="width:auto;display: inline-block;">通话信息</h4>
                                <a href="javascript:;" style="text-decoration: none;display: inline-block;background: 0 0;float: right;font-size: 18px;" onclick="togle($(this))"><i class="fa fa-chevron-up"></i></a>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-google-wallet"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($data["all"]); ?></span>
                                                <span class="title">记录总数</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-outdent"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($data["comeing"]); ?></span>
                                                <span class="title">来电记录</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-compress"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($data["outgoing"]); ?></span>
                                                <span class="title">去电记录</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metric">
                                            <span class="icon"><i class="fa fa-bar-chart"></i></span>
                                            <p>
                                                <span class="number"><?php echo ($data["round"]); ?></span>
                                                <span class="title">接通率</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-9">
                                        <div id="visits-trends-chart" class="ct-chart"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="weekly-summary text-right">
                                            <span class="number"><?php echo ($data["missed"]); ?></span> <span class="percentage"><i class="fa fa-caret-up text-success"></i></span>
                                            <span class="info-label">未接来电</span>
                                        </div>
                                        <div class="weekly-summary text-right">
                                            <span class="number"><?php echo ($data["message"]); ?></span> <span class="percentage"><i class="fa fa-caret-up text-success"></i></span>
                                            <span class="info-label">来电留言</span>
                                        </div>
                                        <div class="weekly-summary text-right">
                                            <span class="number"><?php echo ($data["audio"]); ?></span> <span class="percentage"><i class="fa fa-caret-down text-danger"></i></span>
                                            <span class="info-label">音频记录</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <!-- 操作日志 -->
                         <div class="panel panel-headline">
                                <!-- TIMELINE -->
                            <div class="panel-body">
                                <h4 style="width:auto;display: inline-block;">操作日志</h4>
                                <a href="javascript:;" style="text-decoration: none;display: inline-block;background: 0 0;float: right;font-size: 18px;" onclick="togle($(this))"><i class="fa fa-chevron-up"></i></a>
                            </div>
                            <div class="panel-body" style="padding: 0px;">
                                <ul class="list-unstyled activity-list">
                                <?php if(is_array($account_logs)): foreach($account_logs as $key=>$vo): ?><li style="padding:12px 0;">
                                        <p><?php echo ($vo["content"]); ?><span class="timestamp"><?php echo (date("Y-m-d H:i:s",$vo['utime'])); ?></span></p>
                                    </li><?php endforeach; endif; ?>                                                
                                </ul>
                            </div>
                                <!-- END TIMELINE -->
                        </div>
                        
                        <!--系统情况-->
                        <div class="panel panel-headline">
                             <div class="panel-body">
                                <h4 style="width:auto;display: inline-block;">系统信息</h4>
                                <a href="javascript:;" style="text-decoration: none;display: inline-block;background: 0 0;float: right;font-size: 18px;" onclick="togle($(this))"><i class="fa fa-chevron-up"></i></a>
                            </div>
                            <div class="panel-body">
                                <div id="system-load" class="easy-pie-chart" data-percent="<?php echo ($a["round"]); ?>">
                                    <span class="percent"><?php echo ($a["round"]); ?></span>
                                </div>
                                <h4>系统信息</h4>
                                <ul class="list-unstyled list-justify">
                                    <li>操作系统: <span><?php echo ($a["osname"]); ?></span></li>
                                    <li>服务器: <span><?php echo ($a["os"]); ?></span></li>
                                    <li>PHP<span><?php echo ($a["version"]); ?></span></li>
                                    <li>数据库<span>mysql<?php echo ($a["mysql_version"]); ?></span></li>
                                    <li>磁盘总空间<span><?php echo ($a["total"]); ?>G</span></li>
                                    <li>已使用空间<span><?php echo ($a["use"]); ?>G</span></li>
                                    <li>剩余空间<span><?php echo ($a["free"]); ?>G</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="panel panel-headline" style="margin-bottom: 50px">
                            <div class="panel-body">
                                <h4 style="width:auto;display: inline-block;">版本信息</h4>
                                <a href="javascript:;" style="text-decoration: none;display: inline-block;background: 0 0;float: right;font-size: 18px;" onclick="togle($(this))"><i class="fa fa-chevron-up"></i></a>
                            </div>
                            <div class="panel-body">
                                <?php  $log = SITE_PATH . '/version.txt'; $content=file_get_contents($log);?>
                                <textarea style="width: 800px;height:100px;" class="icp-form-item-textarea J_must J_dwCard" onchange="updateVersion($(this))"><?php echo $content; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END MAIN CONTENT -->
            </div>
            
           <div class="main" style="width:260px;float: right;margin-left:0px;">
                <div class="main-content" style="padding: 10px 0px;">
                <div class="container-fluid">
                    <div class="row" style="width:100%;margin-left: 0px;">
                        <div class="col-md-6" style="width:100%;padding-right: 0px;padding-left: 0px;">
                        <?php if(is_array($all_purviews)): foreach($all_purviews as $key=>$val): ?><div class="panel" style="margin:0px;border-radius: 0px;">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><?php echo ($val['gname']); ?><span class="_group_status _group_status_<?php echo ($key); ?>" data-id="<?php echo ($key); ?>"><?php echo ($val['inline']); ?></span>
                                    /<?php echo ($val['total']); ?></h3>
                                    <div class="right">
                                        <button type="button" class="btn-toggle-collapse"><i class="lnr lnr-chevron-up lnr-chevron-down"></i></button>
                                    </div>
                                </div>
                                <div class="panel-body no-padding" style="display: none;">
                                    <table class="table table-striped" style="margin-bottom: 1px;">
                                        <tbody>
                                            <?php  unset($val['gname']); unset($val['total']); unset($val['inline']); unset($val['out']); ?>
                                            <?php if(is_array($val)): foreach($val as $k=>$v): ?><tr>
                                                    <td ><a style="background-color:transparent;;padding-left: 10px;width:auto;font-size: 12px;" href="<?php echo U('Device/showLine',array('id'=>$v['device_id']));?>"><?php echo ($v['name']); ?></a></td>
                                                    <td class="_status _status_<?php echo ($v["device_id"]); ?>" data-id="<?php echo ($v["device_id"]); ?>">
                                                        <?php if($v['status']): ?><img src="./public/resource/images/online.png" width=20 title='在线'>
                                                        <?php else: ?>
                                                            <img src="./public/resource/images/off.png" width=20 title="离线"><?php endif; ?>
                                                    </td>
                                                </tr><?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div><?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>
            </div>
           </div>
        </div>

        <!--分组设备信息-->
            
</div>
<script src="/public/assets/vendor/jquery/jquery.min.js"></script>
<script src="/public/assets/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="/public/assets/vendor/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<script src="/public/assets/vendor/jquery.easy-pie-chart/jquery.easypiechart.min.js"></script>
<script src="/public/assets/vendor/chartist/js/chartist.min.js"></script>
<script src="/public/assets/scripts/klorofil-common.js"></script>
<script>
    $(function() {
        var data, options;

        // headline charts
        var account = "<?php echo ($accounts['total']); ?>";
        var account_line = "<?php echo ($accounts['inline']); ?>";
        var account_out = "<?php echo ($accounts['out']); ?>";
        data = {
            labels: ['全部', '在线', '离线'],
            series: [
                [account, account_line, account_out],
                [account, account_line, account_out],
            ]
        };

        options = {
            height: 300,
            showArea: true,
            showLine: false,
            showPoint: false,
            fullWidth: true,
            axisX: {
                showGrid: false
            },
            lineSmooth: false,
        };

        new Chartist.Line('#headline-chart', data, options);
        var devices = "<?php echo ($devices['total']); ?>";
        var device_inline = "<?php echo ($devices['inline']); ?>";
        var device_out = "<?php echo ($devices['out']); ?>";
        data = {
            labels: ['全部', '在线', '离线-------'],
            series: [
                [devices, device_inline, device_out],
                [devices, device_inline, device_out],
            ]
        };

        new Chartist.Line('#headline-chart2', data, options);
        new Chartist.Line('#headline-chart3', data, options);

        var device ="<?php echo ($account_devices['total']); ?>";
        var log = "<?php echo ($att["log"]); ?>";
        var adevice = "<?php echo ($att['device']); ?>";
        var log_count = "<?php echo ($log_count); ?>";
        data = {
            labels: ['关注设备', '关注记录', '权限设备', '操作日志'],
            series: [
                [device, log, adevice, log_count],
                
            ]
        };

        options = {
            height: 300,
            showArea: true,
            showLine: false,
            showPoint: false,
            fullWidth: true,
            axisX: {
                showGrid: false
            },
            lineSmooth: false,
        };

        new Chartist.Bar('#headline-chart1', data, options);
        var all = "<?php echo ($data['all']); ?>";
        var comeing = "<?php echo ($data['comeing']); ?>";
        var outgoing = "<?php echo ($data['outgoing']); ?>";
        var missed = "<?php echo ($data['missed']); ?>";
        var audioaudio = "<?php echo ($data['audio']); ?>";
        var message = "<?php echo ($data['message']); ?>";
        var vedio = "<?php echo ($data['vedio']); ?>";
        // visits trend charts
        data = {
            labels: ['全部记录', '来电记录', '去电记录', '未接来电', '音频记录', '来电留言', '现场视频'],
            series: [
                
                [all, comeing, outgoing, missed, audioaudio, message, vedio]
            ]
        };

        

        new Chartist.Bar('#visits-trends-chart', data, options);

        var sysLoad = $('#system-load').easyPieChart({
            size: 130,
            barColor: function(percent) {
                return "rgb(" + Math.round(200 * percent / 100) + ", " + Math.round(200 * (1.1 - percent / 100)) + ", 0)";
            },
            trackColor: 'rgba(245, 245, 245, 0.8)',
            scaleColor: false,
            lineWidth: 5,
            lineCap: "square",
            animate: 800
        });

        
        

    });
</script>
<script type="text/javascript">
    function togle(obj){

        var class1 = obj.children("i").attr('class');
        
            if(obj.children("i").is(".fa-chevron-up")){
                obj.children("i").attr('class','fa fa-chevron-down');
            }else{
                obj.children("i").attr('class','fa fa-chevron-up');
            }
        
        par = obj.parent().next().slideToggle();
    }
    function updateVersion(obj) {
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: 'index.php?s=/Admin/Index/updateVersion',
            data: "content="+obj.val(),
            success: function(rs){
                alert(rs.msg)
            }
        })
    }
    function telLayer(){
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: 'index.php?s=/Api/Api/telLayer',
            data: "",
            success: function(rs){
                if(rs.status){
                    for(var v in rs.data){
//                        alert(rs.data[v].msg)
                        if (''==$("#"+rs.data[v].id).text()){
                            $("#tel_layer_area").after("<p style='margin-left: 20px' id='"+ rs.data[v].id +"'>"+ rs.data[v].msg +"&nbsp&nbsp&nbsp&nbsp<span onclick='$(this).parent().hide()' style='color: red'>删除</span></p>");
                        }
                    }
                }
            }
        })
        window.setTimeout(telLayer,2000);
    }
//    telLayer()
</script>
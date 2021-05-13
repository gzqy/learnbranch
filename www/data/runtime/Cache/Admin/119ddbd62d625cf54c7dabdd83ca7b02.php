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

<script>
    var needCrmTelLayer = 1;
</script>
<div class="content" style="background-color: #fff;">
    <div class="con">
        <dl class="nav">
            <dt><b>●</b>客户管理</dt>
         
            <?php if(is_array($second_nav)): foreach($second_nav as $key=>$vo): if($vo["id"] == $second_nav_id): ?><dd class="bg"><a href="javascript:;"><b>●</b><?php echo ($vo["name"]); ?></a></dd>
                    <?php else: ?>
                    <dd><a href="<?php echo ($vo["a"]); ?>"><b>●</b><?php echo ($vo["name"]); ?></a></dd><?php endif; endforeach; endif; ?>

        </dl>
        <div class="right">
            <div class="aps-tab-bar aps-tab-bar-type-card aps-tab-bar-align-left aps-tab-bar-size-small">
                <div class="aps-tab-bar-wrapper" style="width: 100%;">
                    <div class="aps-tab-item-wrapper">
                        <div class="aps-tab-label aps-tab-label-active aps-tab-label-col-5">
                            <span class="aps-tab-label-inner"><a class="tabs" href="javascript:;">客户列表</a></span>
                        </div>

                        <div class="aps-tab-label  aps-tab-label-col-5">
                            <span class="aps-tab-label-inner"><a class="tabs" href="<?php echo U('Concats/add');?>">添加客户</a></span>
                        </div>
                    </div>
                </div>
            </div>

            <form class="clearfix search" id="_form1" method="post" action="<?php echo U('/Concats/index');?>">
                <div class="filter">
                    <div class="aps-widget aps-ani-transition aps-grid aps-grid-fluid aps-state-visible">
                        <div class="aps-ani-transition aps-grid-row aps-state-visible">
                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                                 style="padding: 8px;"> 客户名称:
                            </div>
                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                                 style="padding: 8px;"><input type="text" placeholder="请填写名称"
                                                              class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                              name="name" value="<?php echo ($name); ?>"></div>

                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                                 style="padding: 8px;"> 客户电话:
                            </div>
                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                                 style="padding: 8px;"><input type="text" placeholder="请填写电话"
                                                              class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                              name="tel1" value="<?php echo ($tel1); ?>"></div>

                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                                 style="padding: 8px;"> 客户分组:
                            </div>
                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                                 style="padding: 8px;">
                                <select name="group_id"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                                    <option value="0">所有分组</option>
                                    <?php if(is_array($groups)): foreach($groups as $key=>$vo): if($vo["id"] == $group_id): ?><option selected value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option>
                                            <?php else: ?>
                                            <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; ?>
                                </select>
                            </div>
                            <!--
                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                               style="padding: 8px;"> 客户来源:
                          </div>
                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                               style="padding: 8px;">
                              <select name="source_id"
                                      class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                                  <option value="0">所有来源</option>
                                  <?php if(is_array($sourceList)): foreach($sourceList as $key=>$vo): if($vo["id"] == $source_id): ?><option selected value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option>
                                          <?php else: ?>
                                          <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; ?>
                              </select>
                          </div>
                      </div>

                      <div style="display: none" class="aps-ani-transition aps-grid-row aps-state-visible">
                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                               style="padding: 8px;"> 客户性别:
                          </div>
                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                               style="padding: 8px;">
                              <select name="gender"
                                      class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                                  <option value="0">选择性别</option>
                                  <option value="1"
                                  <?php if($gender == 1): ?>selected<?php endif; ?>
                                  >男</option>
                                  <option value="2"
                                  <?php if($gender == 2): ?>selected<?php endif; ?>
                                  >女</option>
                              </select>
                          </div>

                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                               style="padding: 8px;"> 注册时间:
                          </div>
                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                               style="padding: 8px;"><input type="text" id="start-one" placeholder="最小值"
                                                            class="datainp aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                            name="add_start_date" value="<?php echo ($add_start_date); ?>" readonly>
                          </div>

                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                               style="padding: 8px;"> 注册时间:
                          </div>
                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                               style="padding: 8px;"><input type="text" id="start-two" placeholder="最大值"
                                                            class=" datainp aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                            name="add_end_date" value="<?php echo ($add_end_date); ?>" readonly>
                          </div>
                      </div>

                      <div style="display: none" class="aps-ani-transition aps-grid-row aps-state-visible">

                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                               style="padding: 8px;"> 更新时间:
                          </div>
                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                               style="padding: 8px;"><input type="text" id="start-three" placeholder="最小值"
                                                            class="datainp aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                            name="upd_start_date" value="<?php echo ($upd_start_date); ?>" readonly>
                          </div>

                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                               style="padding: 8px;"> 更新时间:
                          </div>
                          <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                               style="padding: 8px;"><input type="text" id="start-four" placeholder="最大值"
                                                            class=" datainp aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                            name="upd_end_date" value="<?php echo ($upd_end_date); ?>" readonly>
                          </div>

                      </div>-->
                       <!-- <div class="aps-ani-transition aps-grid-row aps-state-visible">
                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                                 style="padding: 8px;">
                                客户意向：
                                <select id="form_feedback" name="form_feedback" style="width: 100px"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                                   <option value="0">全部</option>
                                    <?php if(is_array($feedbackType)): foreach($feedbackType as $key=>$vo): if($key == $feedbackTypeForm): ?><option selected value="<?php echo ($key); ?>"><?php echo ($vo); ?></option>
                                            <?php else: ?>
                                            <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; ?>
                                </select>
                            </div>
                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                                 style="padding: 8px;">
                                重要程度：
                                <select id="importantLevel" name="important_level" style="width: 100px"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                                    <option value="0">全部</option>
                                    <?php if(is_array($importantLevelList)): foreach($importantLevelList as $key=>$vo): if($key == $importantLevel): ?><option selected value="<?php echo ($key); ?>"><?php echo ($vo); ?></option>
                                            <?php else: ?>
                                            <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; ?>
                                </select>
                            </div>
                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                                 style="padding: 8px;">
                                是否已拨：
                                <select id="form_is_dailed" name="form_is_dailed" style="width: 100px"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                                    <?php if(is_array($option_is_dailed)): foreach($option_is_dailed as $key=>$vo): if($key == $form_is_dailed): ?><option selected value="<?php echo ($key); ?>"><?php echo ($vo); ?></option>
                                            <?php else: ?>
                                            <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; ?>
                                </select>
                            </div>
                            <?php if($isAdmin): ?><div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                                     style="padding: 8px;">
                                    分配状态:
                                    <select id="form_select_userid" name="form_select_userid" style="width: 100px"
                                            class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                                        <option value="0">全部</option>
                                        <option value="-1"
                                        <?php if($form_select_userid==-1) {echo "selected";} ?> >未分配</option>
                                        <option value="-2"
                                        <?php if($form_select_userid==-2) {echo "selected";} ?>>已分配</option>
                                        <?php if(is_array($users)): foreach($users as $key=>$vo): if($vo["id"] == $form_select_userid): ?><option selected value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option>
                                                <?php else: ?>
                                                <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; ?>
                                    </select>
                                </div><?php endif; ?>
                        </div>-->
                        <div class="aps-ani-transition aps-grid-row aps-state-visible">
                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-24 aps-state-visible search-button "
                                 style="padding: 8px;">
                                <button type="button"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-primary aps-size-small btn"
                                        onclick="$('#_form1').submit();">搜索
                                </button>
                                <button type="button"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-normal aps-size-small btn"
                                        onclick="$('input').val('')">重置
                                </button>
                                <button type="button"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-normal aps-size-small btn float-btn"
                                        onclick="excel('<?php echo U('Concat/index_excel');?>')">导出列表
                                </button>
                                <!--上传通讯文件：<input  type="file" placeholder="导入客户" onclick="$('#file').val('')" onchange="import_excel('<?php echo U('Concat/import_excel');?>')" id="file" name="filename" />-->
                                <label style="position: relative;" for="file">
                                    <input type="button"
                                           style="padding: 5px 10px; margin-bottom: 7px;margin-left: 5px; background: #00c1de;color: #FFF;border: none;"
                                           id="btn" value="导入客户"><span style="margin-left: 5px" id="text"></span>
                                    <input type="file" style="position: absolute;left: 0;top: 0;opacity: 0;"
                                           onclick="$('#file').val('')"
                                           onchange="import_excel('<?php echo U('Concats/import_excel');?>')" id="file"
                                           name="filename">
                                </label>

                                <button type="button"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-normal aps-size-small btn float-btn"
                                        onclick="window.open('./public/CSV/导入通讯模板.xlsx')">导出模板
                                </button>
                                <!--<br/>-->
                            </div>
                        </div>

                        <div class="aps-ani-transition aps-grid-row aps-state-visible">
                            <div class="aps-ani-transition aps-grid-col aps-grid-col-md-24 aps-state-visible search-button "
                                 style="padding: 8px;">
                                电话号码：<input style="width: 120px"
                                            class="datainp aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                            id="tel" type="text" name="tel" value="<?php echo ($tel); ?>">
                              <!--  <button type="button"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-primary aps-size-small btn"
                                        onclick="ws_contact(-1,'<?php echo U('Concat/wsConnectBefore');?>')">设备拨号
                                </button>
                                <button type="button"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-primary aps-size-small btn"
                                        onclick="if($('#dial_info').is(':hidden')){$('#dial_info').show()}else{$('#dial_info').hide()}">显示/隐藏拨号信息
                                </button>-->

                                <button type="button"
                                        class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-primary aps-size-small btn"
                                        onclick="Calltel('<?php echo U('Test/addPush');?>',<?php echo $_SESSION['ACCOUNTS']['tel']?>)">手机拨号
<!--                                        onclick="ws_cont($_SESSION['ACCOUNTS']['tel'],'<?php echo U('Test/addPush');?>')">设备拨号-->

                                    <!--                                    <a href="<?php echo U('/Test/addPush',array('tel'=>$_SESSION['ACCOUNTS']['tel'],'phone'=>$val['tel1'],'type'=>call));?>">-->

                                </button>
                                <button class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-primary aps-size-small btn"
                                        type="button"
                                        onclick="telToAccount('<?php echo U('Test/addPush');?>',0)">号码群拨
                                </button>
                                <button class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-primary aps-size-small btn"
                                        type="button"
                                        onclick="telTodel('<?php echo U('Test/Pushs');?>',<?php echo $_SESSION['ACCOUNTS']['tel']?>)">挂断
                                </button>

                            </div>
                        </div>


                    </div>
                </div>
                <input type="text" id="limit" name="limit" value="<?php echo ($limit); ?>" hidden>
            </form>
            <input type="hidden" name="gdata" id="gdata" value='<?php echo ($gdata); ?>'/>
            <div class="formation clearfix" style="margin-top: 10px;">
                <table>
                    <tr>
                        <th style="width: 50px"><input type="checkbox" id="allSelects" onclick="changeAllIsBuy()"></th>
                        <th style="width:120px">名称</th>
                        <th style="width: 120px;">电话</th>
                        <th>备注</th>
                        <th>分组</th>
                        <th>来电</th>
                        <th>去电</th>
                        <th>未接</th>
                        <th>公司</th>

                        <th style="width:200px">客户管理</th>
                    </tr>
                </table>
            </div>

            <form id="_form" name="_form" action="/device/setGroup" method="post" onsubmit="return false;">
                <div class="formation clearfix" style="border: 1px solid #e7e7e7;border-top: none;">
                    <table>
                        <?php $sourceList1=array_column($sourceList,'name','id'); ?>
                        <?php if(is_array($data)): foreach($data as $key=>$val): ?><tr class="one">
                                <td style="width: 50px"><input type="checkbox" class="J_check _ids" name="ids[]"
                                                               value="<?php echo ($val['tel1']); ?>"></td>
                                <td style="width:120px">
                                    <?php echo ($val['name']); ?>
                                </td>
                                <!--<td><?php echo ($val["tel1"]); ?></td>-->
                                <td style="cursor:pointer;width: 120px"><?php echo ($val["tel1"]); ?>
                                </td>
                                <td><?php echo ($val["note"]); ?></td>
                                <td><?php echo ($val["gname"]); ?></td>
                                <td><?php echo ($val["comeing"]); ?></td>
                                <td><?php echo ($val["outgoing"]); ?></td>
                                <td><?php echo ($val["missed"]); ?></td>
                                <td><?php echo ($val["company"]); ?></td>
<!--                                <td><?php echo ($val["comeing"]); ?></td>-->
<!--                                <td><?php echo ($val["outgoing"]); ?></td>-->
<!--                                <td><?php echo ($val["missed"]); ?></td>-->
                                <!--<td><?php echo ($val["add_time"]); ?></td>-->
                                <!--<td><?php echo ($val["upd_time"]); ?></td>-->
<!--                                <td><?php echo (string)$sourceList1[$val['source_id']] ?></td>-->

								<!--<a href="javascript:;"
                                   onclick="ws_contact(<?php echo ($val['id']); ?>,'<?php echo U('Concat/wsConnectBefore');?>')">座机</a><i>|</i>-->
                                <!--<a href="<?php echo U('/Test/addPush',array('tel'=>$_SESSION['ACCOUNTS']['tel'],'phone'=>$val['tel1'],'type'=>call));?>">手机</a></td>-->
                                <!--<td id="is_dailed_<?=$val['id']?>"><?=$val['is_dailed']==1?'是':'否';?></td>-->
<!--                                <td style="width: 100px"><?php echo (string)$feedbackType[$val['feedback_type']] ?></td>-->
                                <!--<td style="width: 100px">-->
                                    <!--<select id="select_feedback_<?=$val['id']?>"-->
                                            <!--onchange="feedbackChange('<?php echo U('/Concat/changeFeedBack');?>','select_feedback_<?=$val['id']?>',<?=$val['id']?>)"-->
                                            <!--style="width: 90px" class="">-->
                                        <!--<?php foreach ($feedbackType as $key=>$vo):?>-->
                                        <!--<?php if($key==$val['feedback_type']){ ?>-->
                                        <!--<option selected value="<?php echo ($key); ?>"><?=$vo?></option>-->
                                        <!--<?php }elseif($key>0){ ?>-->
                                        <!--<option value="<?=$key?>"><?=$vo?></option>-->
                                        <!--<?php }?>-->
                                        <!--<?php endforeach;?>-->
                                    <!--</select>-->
                                <!--</td>-->
<!--                                <td><?php echo $importantLevelList[$val['important_level']] ?></td>-->
<!--                                <td><?php echo ($val["contactAccounts"]); ?></td>-->
                                <td style="width:200px">
                                    <a href="<?php echo U('/Concats/add',array('id'=>$val['id']));?>">编辑</a><i>|</i>
                                    <a href="javascript:;" onclick="ConcatRemove(<?php echo ($val['id']); ?>)">删除</a><i>|</i>
                                    <a href="javascript:;"  onclick="crm_show(<?php echo ($val['id']); ?>)">通话记录</a>


                                    <!--<a href="javascript:;" onclick="ws_contact(<?php echo ($val['id']); ?>,'<?php echo U('Concat/wsConnectBefore',['dialLocal'=>1]);?>')">本地拨号</a>-->

                            </tr><?php endforeach; endif; ?>
                    </table>
                </div>
            </form>

            <div class="pages" style="font-size: 14px;">
                每页条数<input type="text" id="limit1" value="<?php echo ($limit); ?>" style="width: 40px">
                <button onclick="document.getElementById('limit').value=document.getElementById('limit1').value;document.getElementById('_form1').submit()">
                    确定
                </button>
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
<script type="text/javascript" src="./public/resource/js/concat.js"></script>
<div id="crm_window" class="ui-dialog ui-widget ui-corner-all ui-front ui-dialog-buttons"
     tabindex="-1"
     role="dialog" aria-describedby="_dialog_crm_tip" aria-labelledby="ui-id-1"
     style="background-color: white;width: 89%;height: 100%; left: 11%; display: none; overflow:scroll;z-index: 101;">
    <div style="margin-top: 53px;margin-bottom: 5px">
        <button onclick='$("#crm_window").hide()' type="button" style="width: 50px"
                class="ui-button ui-widget ui-state-default ui-corner-all">返回
        </button>
        <span style="margin-left:35%">客户信息及通话记录</span>
    </div>
    <!--<br/>-->
    <!--<hr>-->
    <form style="background-color: white" id="_form_crm" method="post" action="<?php echo U('/Admin/Crm/concatSave');?>" class="clearfix search">
        <div class="filter" style="padding: 0px 15px 0px 15px;border: 0px">
            <input type="hidden" name="crm_id" value="<?php echo ($id); ?>">
            <div class="aps-widget aps-ani-transition aps-grid aps-grid-fluid aps-state-visible">
                <div class="aps-ani-transition aps-grid-row aps-state-visible">
                    <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 最后分配者:</div>-->
                    <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;"><input readonly type="text" placeholder="" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small" name="crm_last_admin" style="width: 80px;" value=""></div>-->

                    <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 最后分配时间:</div>-->
                    <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;"><input readonly type="text" placeholder="" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small" name="crm_last_order_time" value=""></div>-->

                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 姓名:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;"><input type="text" placeholder=""
                                                      class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                      name="crm_name" style="width: 110px;" value=""></div>

                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 性别:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;"><input type="text" placeholder=""
                                                      class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                      name="crm_gender" style="width: 60px;" value=""></div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 客户意向:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;">
                        <select name="crm_feedback_type" style=""
                                class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                            <?php foreach ($feedbackType as $key=>$vo):?>
                            <?php if($key==$val['feedback_type']){ ?>
                            <option selected value="<?php echo ($key); ?>"><?=$vo?></option>
                            <?php }elseif($key>0){ ?>
                            <option value="<?=$key?>"><?=$vo?></option>
                            <?php }?>
                            <?php endforeach;?>
                        </select>
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 客户来源:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;">
                        <select name="crm_source_id" style=""
                                class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                            <option value="0">所有来源</option>
                            <?php if(is_array($sourceList)): foreach($sourceList as $key=>$vo): if($vo["id"] == $val['source_id']): ?><option selected value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 重要程度:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;">
                        <select name="crm_important_level" style=""
                                class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                            <?php if(is_array($importantLevelList)): foreach($importantLevelList as $key=>$vo): if($key == $val['importantLevel']): ?><option selected value="<?php echo ($key); ?>"><?php echo ($vo); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; ?>
                        </select>
                    </div>
                </div>
                <div class="aps-ani-transition aps-grid-row aps-state-visible">
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 类别:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;">
                        <select name="crm_group_id"
                                class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small">
                            <option value="0">所有类别</option>
                            <?php if(is_array($groups)): foreach($groups as $key=>$vo): if($vo["id"] == $group_id): ?><option selected value="{vo.id}"><?php echo ($vo["name"]); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 联系电话1:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;"><input style="width: 120px" type="text" placeholder=""
                                                      class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                      name="crm_tel1" value=""></div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 联系电话2:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;"><input style="width: 120px" type="text" placeholder=""
                                                      class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                      name="crm_tel2" value=""></div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 联系电话3:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;"><input style="width: 120px" type="text" placeholder=""
                                                      class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                      name="crm_tel3" value=""></div>

                </div>
                <div class="aps-ani-transition aps-grid-row aps-state-visible">
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 公司:</div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;"><input type="text" placeholder="" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small" name="crm_company" value=""></div>

                    <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 职位:</div>-->
                    <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;"><input type="text" placeholder="" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small" name="crm_position" value=""></div>-->
                    <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title" style="padding: 8px;"> 城市:</div>-->
                    <!--<div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible" style="padding: 8px;"><input type="text" placeholder="" class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small" name="crm_city" value=""></div>-->

                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 详细地址:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;"><input type="text" placeholder=""
                                                      class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                      name="crm_address" value=""></div>

                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 电子邮件:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;"><input type="text" placeholder=""
                                                      class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                      name="crm_email" value=""></div>
                </div>
                <div class="aps-ani-transition aps-grid-row aps-state-visible">
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 回访提醒时间:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;"><input type="text" id="remind_time" placeholder="回访提醒时间"
                                                      class="datainp aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-input aps-size-small"
                                                      name="crm_remind_time" value="" readonly></div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 沟通记录:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;"><textarea type="text" rows="3" cols="30" style="resize: both;"
                                                         name="crm_note_list" readonly></textarea></div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-2 aps-state-visible legend title"
                         style="padding: 8px;"> 回访记录:
                    </div>
                    <div class="aps-ani-transition aps-grid-col aps-grid-col-md-4 aps-state-visible"
                         style="padding: 8px;"><textarea type="text" rows="3" cols="20" style="resize: both;height: 60px;width: 200px"
                                                         name="crm_note"></textarea></div>
                </div>
                <div class="aps-ani-transition aps-grid-row aps-state-visible">
                    <?php if(0&&$cancelDial){?>
                    <button type="button"
                            class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-primary aps-size-small btn"
                            onclick="cancelDialed()">取消拨号
                    </button>
                    <?php }?>
                    <button type="button"
                            class="aps-ani-transition aps-widget aps-corner-tl aps-corner-tr aps-corner-bl aps-corner-br aps-btn aps-btn-primary aps-size-small btn"
                            onclick="crm_form($('#_form_crm'))">保存
                    </button>
                </div>
            </div>
        </div>
    </form>
    <hr>
    <div class="formation clearfix" style="margin-top: 10px;width:100%;">
        <table>
            <tr style="padding: 2px 0 2px 0;">
                <!--<th style="width: 20px"><input type="checkbox" name="to_all" id="_to_all" onclick="$('._ids').prop('checked',$(this).prop('checked'))" ></th>-->
                <th style="width: 70px">序号</th>
                <th style="width: 70px">端口</th>
                <!--<th style="width: 70px">关注</th>-->
                <th style="width: 100px">录音类型</th>
                <th style="width: 120px">日期时间</a></th>
                <!--<th style="width: 80px;" title="录音时长">录音</th>-->
                <th style="width: 80px;" title="通话时长">通话时长</th>
                <th style="width:100px;">号码</th>
                <!--<th style="width:70px;">星数</th>-->
                <th style="width: 200px">操作</th>
            </tr>
            <tbody id="crm_call_logs_body">

            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript" src="./public/resource/js/crm.js"></script>
<script>
    jeDate("#remind_time", {
        minDate: "<?php echo date('Y-m-d H:i:s') ?>",              //最小日期
        maxDate: "2099-12-31",              //最大日期
        method: {
            choose: function (params) {
            }
        },
        format: "YYYY-MM-DD hh:mm:ss"
    });
</script>
<div id="crm_tip" class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front _dialog_tip ui-dialog-buttons" tabindex="-1"
     role="dialog" aria-describedby="_dialog_tip" aria-labelledby="ui-id-1"
     style="height: auto; width: 400px; top: 226.5px; left: 300px; display: none; z-index: 111;">
    <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" style="display: none;">
        <span
                id="crm_tip_ui-id-1" class="ui-dialog-title">&nbsp;</span>
        <button type="button"
                class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close"
                role="button" title="Close">
            <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
            <span
                    class="ui-button-text">Close</span>
        </button>
    </div>
    <div id="crm_tip_dialog_tip" class="ui-dialog-content ui-widget-content"
         style="width: auto; min-height: 13px; max-height: none; height: auto;">
        <p><br/><b>已经拨号，弹屏<span id="timeout1">6</span>秒后请拿起听筒！
            <span id="timeout2">6</span>秒后自动消失!<br/>
            </b><b id="crm_tip_content">undefined</b></p>
    </div>
    <div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
        <div class="ui-dialog-buttonset">
            <button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"
                    role="button">
                <span onclick="$('#crm_tip').hide();$('#crm_tip_z_index').hide()" class="ui-button-text">确定</span>
            </button>
            <button type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"
                    role="button">
                <span onclick="$('#crm_tip').hide();$('#crm_tip_z_index').hide();cancelDialed()" class="ui-button-text">取消拨号</span>
            </button>
        </div>
    </div>
</div>
<div id="crm_tip_z_index" class="ui-widget-overlay ui-front" style="display:none; z-index: 109;"></div>


<script type="text/javascript">
function changeAllIsBuy() {
if($('#allSelects').is(':checked')){
$("input[name='ids[]']").prop('checked', true);
}else{
$("input[name='ids[]']").prop('checked', false);
}
}
</script>
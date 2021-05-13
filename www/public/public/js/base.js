$(document).ready(function () {
    bindAll();
    bindStatus();
    bindLineStatus();
    bindDatepicker();
    bindDatetimepicker();
    bindUpload();
    bindAttention();
    bindPagination();
    bindLogAttention();
    // $('.ui.dropdown').dropdown();
    // $('.ui.accordion').accordion();
});

//ajax 提交方法
function ajax(config){
    $.ajax({ 
        url:config.url,
        data:config.data || {},
        timeout:config.timeout || 60000,
        type:config.type || "POST",
        async:config.async==='sync' ? false:true,
        dataType:config.dataType || "json",
        success:function(rsp){

            
            if(typeof config.success=="function"){
                config.success(rsp);
            }
        },
        beforeSend:function(){
            if(typeof config.beforeSend=="function"){
                config.beforeSend();
            }
        },
        complete:function(){

            if(typeof config.complete=="function"){
                config.complete();
            }
        },
        error:function(XMLHttpRequest, textStatus, errorThrown){

            if(typeof config.error=="function"){
                config.error();
            }else{
               if(textStatus=='timeout'){
                    //alert('Connect Timeout!');
                }
                if(textStatus=='error'){
                    //alert('Handle error,Please try again!');
                } 
            }
        }
    });
}


var _form_posting = false;
//表单提交方法
function _form(form_id,callback) {
    if ( _form_posting == true ) {
        return false;
    }
    var fff;
    
    if ( typeof form_id=='object' ) {
        fff = form_id;
    } else {
        fff = $(form_id);
    }
    var url = fff.attr('action');
    var data = fff.serialize();
    if ( !url || !data ) {
        return false;
    }
    _form_posting = true;
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url,
        data: data,
        success: function(rs){
            _form_posting = false;
            callback(rs);
        }
    });
}

//js跳转方法
function go(url){
    window.location.href=url;
}

function nl2br( str ) {
   return str.replace(/([^>])\n/g, '$1<br>\n');
}

//检测是否是数值
function isNumber(x) {
    var f = parseFloat(x);  
    if (isNaN(f)) {  
        return x;  
    }  
    f = Math.round(x*100)/100;  
    return f;  
}

//设备检测绑定方法 每隔30秒检测
function bindStatus(){
    if ( $("._status").length == 0 ) {
        return false;
    }
    getStatus();
    var inty = setInterval(function(){
        getStatus();
    },30000);
}

//设备检测方法
function getStatus(){
    var data = '';
    $("._status").each(function(i,n){
        var device_id = $(n).attr('data-id');
        if ( device_id && data.indexOf(device_id) == -1 ) {
            data += '&ids[]='+device_id;
        }
    });
    $("._group_status").each(function(i,n){
        var group_id = $(n).attr('data-id');
        if ( group_id  ) {
            data += '&gids[]='+group_id;
        }
    });
    if ( data == '' ) {
        return false;
    }
    ajax({
        type: "GET",
        dataType: 'json',
        url: 'index.php?s=/Admin/Index/GetStatus',
        data: data,
        success: function(rs){
            if ( rs.status == 200 ) {
                var On_html = '<img src="./public/resource/images/online.png" width=20 title="在线">';
                var Off_html = '<img src="./public/resource/images/off.png" width=20 title="离线">';
                $.each(rs.data,function(i,n){
                    var html = '';
                    if ( n.status == true ) {
                        html = On_html;
                    } else {
                        html = Off_html;
                    }
                    $("._status_"+i).html(html);
                    if ( n.version ) {
                        $("._version_"+i).text(n.version);
                    }
                    if ( n.device_time ) {
                        $("._device_time_"+i).text(n.device_time);
                    }
                    if ( n.comeing ) {
                        $("._comeing_"+i).text(n.comeing);
                    }
                    if ( n.outgoing ) {
                        $("._outgoing_"+i).text(n.outgoing);
                    }
                    if ( n.missed ) {
                        $("._missed_"+i).text(n.missed);
                    }
                    if ( n.case ) {
                        $("._case_"+i).text(n.case);
                    }
                    if ( n.CPU ) {
                        $("._CPU_"+i).text(n.CPU+'%');
                    }
                    if ( n.Store ) {
                        $("._Store_"+i).text(n.Store+'%');
                    }
                    if ( n.Mem ) {
                        $("._Mem_"+i).text(n.Mem+'%');
                    }
                    if(n.IP){
                        $("_IP_"+i).text(n.IP);
                    }
                    if ( n.screen_pops ) {
                        showContact(n.screen_pops);
                    }

                });
                if(rs.gdata){
                     $.each(rs.gdata,function(i,n){
                        $("._group_status_"+i).html(n);
                     });
                }
            }
            if ( rs.status == 400 ) {
                dialog.tip({
                    msg:'此账号已在其他地方登入, 您被迫登出!!',
                    buttons:{
                        "Done": { text:'确定',"class": 'btn-done', click: function() { window.location.reload();   } }
                    }
                });
            }
        }
    });
}

//端口检测 绑定方法 10秒
function bindLineStatus(){
    if ( $("._line_status").length == 0 ) {
        return false;
    }
    setInterval(function(){
        getLineStatus();
    },10000);
}

//端口检测方法
function getLineStatus(){
    var data = '';
    $("._line_status").each(function(i,n){
        var id = $(n).attr('data-id');
        if ( id && data.indexOf(id) == -1 ) {
            data += '&ids[]='+id;
        }
    });
    if ( data == '' ) {
        return false;
    }
    ajax({
        type: "GET",
        dataType: 'html',
        url: 'index.php?s=/Admin/Device/GetLineStatus',
        data: data,
        success: function(html_rs){
            var rs = {};
            try {
                rs = $.parseJSON(html_rs);
            }catch(e) {}
            if ( rs.status == 200 ) {
                $.each(rs.data,function(i,n){
                    var html = '<img src="public/status/'+n.case_icon+'" height="20" align="absmiddle" > '+n.case_text;

                    $("._line_status_"+i).html(html);
                    if ( n.voltage ) {
                        $("._line_voltage_"+i).text(n.voltage);
                    }
                    if ( n.tel ) {
                        $("._line_tel_"+i).text(n.tel);
                    }
                    if ( n.comeing ) {
                        $("._line_comeing_"+i).text(n.comeing);
                    }
                    if ( n.outgoing ) {
                        $("._line_outgoing_"+i).text(n.outgoing);
                    }
                    if ( n.missed ) {
                        $("._line_missed_"+i).text(n.missed);
                    }
                    if ( n.cased ) {
                        $("._line_case_"+i).text(n.cased);
                    }
                    if ( n.last_date ) {
                        $("._line_last_date_"+i).text(n.last_date);
                    }
                    if ( n.case_text ) {
                        //$("._line_case_text_"+i).text(n.case_text);
                    }
                });
            }
        }
    });
}

//wav 播放
function playWav(id){
    var audio = document.getElementById(id);

    if(audio.paused){
        audio.play();
        $("#"+id+"_txt").text('停止');
    } else {
        audio.pause();
        $("#"+id+"_txt").text('播放');
    }
}

function bindDatepicker(){
    if ( $("._datepicker").length == 0 ) {
        return false;
    }
    $("._datepicker").datepicker({
        dateFormat: "yy-mm-dd"
    });
}

function bindDatetimepicker(){
    if ( $("._datetimepicker").length == 0 ) {
        return false;
    }

    $("._datetimepicker").datetimepicker({
        timeFormat: "HH:mm:ss",
        dateFormat: "yy-mm-dd"
    });
}

function showContact(tel){
    dialog.url({
        url:'/index/GetContact/?tel='+tel,
        title:'查看联系人',
        buttons:{
            "Done": { text:'保存',"class": 'btn-done', click: function() { 
                _form('#_contact_form',function(rs){
                    if (rs.status == 200 ) {
                        dialog.tip({
                            msg:rs.msg,
                            buttons:{
                                "Done": { text:'确定',"class": 'btn-done', click: function() { window.location.reload();   } }
                            }
                        });
                    } else{
                        dialog.tip({msg:rs.msg});
                    }
                });
            } },
            "Close": { text:'关闭',"class": '', click: function() { dialog.hide();   } }
        }
    });
    
}

//检测 账号 设备 登录数量 方法 绑定 60秒
function bindAll(){
    if ( $("#_all_online_drive").length == 0 ) {
        return false;
    }
    getAll();
    var inty = setInterval(function(){
        getAll();
    },60000);
}

//检测设备 账号接口
function getAll(){
    ajax({
        type: "GET",
        dataType: 'json',
        url: 'index.php?s=Admin/Index/GetAll',
        success: function(rs){
            if ( rs.status == 200 ) {
                if ( rs.account_total ) {
                    $("#_all_online_account").text(rs.account_online+'/'+rs.account_total);
                } else {
                    $("#_all_online_account_box").hide();
                }
                $("#_all_online_drive").text(rs.device_online+'/'+rs.device_total);
                $("#_all_online_box").css({display:'inline-block'});
            }
            
        }
    });
}


var _file_this = null;
function bindUpload(){
    if ( $("#_up_form").length == 0 ) {
        return false;
    }
    $("#_up_form").find('input[name="upfile"]').bind('change',function(){
        dialog.loading();
        $("#_up_form").submit();
    });
}

function upload(id) {
    id = id || 0;
    if ( id ) {
        $("#_upfile_id").val(id);
    }
    $("#_up_form").find('input[name="upfile"]').trigger('click');
}

var _attention_posting = false;
//关注设备 方法绑定
function bindAttention(){
    var ob = $("._attention");
    if ( ob.length == 0 ) {
        return false;
    }
    ob.click(function(){
        if ( _attention_posting == true ) {
            return false;
        } 
        var id = $(this).attr('data-id');
        var st = 0;
        if ( $(this).hasClass('empty') ) {
            $(this).removeClass('empty').removeClass('gray').addClass('gold');
            st = 1;
        } else {
            $(this).addClass('empty').addClass('gray').removeClass('gold');
            st = 0;
        }
        _attention_posting = true;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: 'index.php?s=/Admin/Device/setAttention',
            data: "id="+id+"&st="+st,
            success: function(rs){
                _attention_posting = false;
                dialog.tip({msg:rs.msg});
            }
        });
    });
}
//关注记录绑定
function bindLogAttention(){
    var ob = $("._log_attention");
    if ( ob.length == 0 ) {
        return false;
    }
    ob.click(function(){
        if ( _attention_posting == true ) {
            return false;
        } 
        var pl = $(this).parents('tr');
        var id = $(this).attr('data-id');
        var st = 0;
        if ( $(this).hasClass('empty') ) {
            $(this).removeClass('empty').addClass('gold');
            st = 1;
            pl.addClass('warning');
        } else {
            $(this).addClass('empty').removeClass('gold');
            pl.removeClass('warning');
            st = 0;
        }
        _attention_posting = true;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: 'index.php?s=/Admin/Device/setLogAttention',
            data: "id="+id+"&st="+st,
            success: function(rs){
                _attention_posting = false;
                if ( rs.status == 300 ) {
                    dialog.tip({msg:rs.msg});
                    $(this).addClass('empty').removeClass('gold');
                    pl.removeClass('warning');
                }
                dialog.tip({msg:rs.msg});
            }
        });
    });
}
//删除 确认弹窗
function delAll(the_form,the_action){
    dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                if ( the_action) {
                    $(the_form).attr('action',the_action);
                }
                if ( $("._ids:checked").length == 0 ) {
                    dialog.tip({msg:'请至少选择一个进行删除'});
                    return;
                }
                _form(the_form,function(rs){
                    console.log(rs);
                    if (rs.status == 200 ) {
                        dialog.tip({
                            msg:rs.msg,
                            buttons:{
                                "Done": { text:'确定',"class": 'btn-done', click: function() { window.location.reload();   } }
                            }
                        });
                    } else{
                        dialog.tip({msg:rs.msg});
                    }
                });
            }},
            "Cancel": { text:'取消',"class": '', click: function() {  dialog.hide(); } }
        }
        
    });
    
}


function bindPagination(){
    var ob = $("._pagination_select");
    if ( ob.length == 0 ) {
        return false;
    }
    ob.change(function(){
        var op = $(this).find("option:selected");
        var link = op.attr('data-page');
        window.location.href=link;
    });
}

//excel导出表格
function excel(url){
    var data = $("#gdata").val();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url,
        data: "data="+data+"&excel=1",
        success: function(data){
            if(200 == data.status){
                // location.href=data.url;

                // 2
                 eleLink = document.createElement('a')
                eleLink.download = data.url
                eleLink.style.display = 'none'
                eleLink.href = data.url
                // 触发点击
                document.body.appendChild(eleLink)
                eleLink.click()
                // 然后移除
                document.body.removeChild(eleLink)







            }else{
                alert(data.msg);
            }
        }
    });
}

function allSelect() {
    if($('#allSelect').is(':checked')){
        $("input[name='ids[]']").prop('checked', true);
    }else{
        $("input[name='ids[]']").prop('checked', false);
    }
}


// 下载文件方法
var funDownload = function (content, filename) {
    var eleLink = document.createElement('a');
    eleLink.download = filename;
    eleLink.style.display = 'none';
    // 字符内容转变成blob地址
    var blob = new Blob([content]);
    eleLink.href = URL.createObjectURL(blob);
    // 触发点击
    document.body.appendChild(eleLink);
    eleLink.click();
    // 然后移除
    document.body.removeChild(eleLink);
};
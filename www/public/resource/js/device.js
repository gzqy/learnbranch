$(document).ready(function(){
    // $('.ui.checkbox').checkbox();
	bindGroup();
    bindAdmins();
    bindStatistics();
    bindInquireData();
    bindInquireDataLog();
});
var Connect_ing = false;
var Monitor_ing = false;
var RunRec = document.getElementById("RunRec");

function bindConnect(){
    var ob = $("#RunRec");
    if ( ob.length == 0 ) {
        return false;
    }
    $('a').click(function(event,handler){

        if ( Connect_ing == true || Monitor_ing == true ) {

            dialog.tip({
                msg:'正在连接设备, 请断开后再进行操作...',
                buttons:{
                    "Done": { text:'断开连接',"class": 'btn-done', click: function() { 
                        if (  Connect_ing == true ) {
                            Disconnect();
                        }
                        if (  Monitor_ing == true ) {
                            StopMonitor();
                        }
                    } },
                    "Cancel": { text:'关闭',"class": '', click: function() { dialog.hide();   } }
                    
                }
            });

            return false;
        }
        
    });
}
function bindInquireData(){
    var ob = $("._device_pop");
    if ( ob.length == 0 ) {
        return false;
    }
    ob.click(function(){
        var vl = $(this).attr('data-id');
        $("#_device_box_"+vl).toggle();
        var txt = '';
        if ( $("#_device_box_"+vl+":visible").length > 0 ) {
            txt = '<i class="triangle up icon"></i>';
        } else {
            txt = '<i class="triangle down icon"></i>';
        }
        $(this).html(txt);
    });
}

function bindStatistics(){
    var ob = $("._device_change");
    if ( ob.length == 0 ) {
        return false;
    }
    ob.click(function(){
        var vl = $(this).val();
        var checked = $(this).prop('checked');

        $("._device_line_"+vl).prop('checked',checked);
    });
    $("._group_change").click(function(){
        var vl = $(this).val();
        var checked = $(this).prop('checked');

        $("._group_"+vl).prop('checked',checked);
        $("._group_"+vl).each(function(i,n){
            var vli = $(this).val();

            $("._device_line_"+vli).prop('checked',checked);
        });
    });
    $("#_all_change").click(function(){
        var checked = $(this).prop('checked');
        $("._group_change").prop('checked',checked);
        $("._device_change").prop('checked',checked);
        $("._line_change").prop('checked',checked);
    });
    $("._line_change").click(function(){
        var checked = $(this).prop('checked');
        if ( checked ) {
            var d_id = $(this).attr('data-device-id');
            $("._device_id_"+d_id).prop('checked',checked);
        }
    });
    $("._change_day").click(function(){
        var start_date = $(this).attr('data-start');
        var end_date = $(this).attr('data-end');
        $("input[name=start_date]").val(start_date);
        $("input[name=end_date]").val(end_date);
    });
}

function lookStatistics(){
    if ( $("._device_change:checked").length == 0 ) {
        dialog.tip({msg:'请选择设备再查看'});
        return;
    }
    if ( $("input[name=start_date]").val() == '' || $("input[name=end_date]").val() == '' ) {
        dialog.tip({msg:'请选择统计时间段'});
        return;
    }
    $("input[name=eco]").val('');
    $('#_Statistics_form').submit();
}

function downloadStatistics(){
    if ( $("._device_change:checked").length == 0 ) {
        dialog.tip({msg:'请选择设备再查看'});
        return;
    }
    if ( $("input[name=start_date]").val() == '' || $("input[name=end_date]").val() == '' ) {
        dialog.tip({msg:'请选择统计时间段'});
        return;
    }
    $("input[name=eco]").val('excel');
    $('#_Statistics_form').submit();
}

function downloadStatistics2(){
    $("#_Statistics_page").val('');
    $("#_Statistics_key").val('');
    $("#_Statistics_all_line").val('');
    $("#_Statistics_eco").val('excel');
    //$('#_Statistics_form').attr('action','/index/InquireData');
    $('#_Statistics_form').submit();
}

function downloadFileZipStatistics2(){
    $("#_Statistics_page").val('');
    $("#_Statistics_key").val('');
    $("#_Statistics_all_line").val('');
    $("#_Statistics_eco").val('zip');
    $('#_Statistics_form').attr('action','/Data/InquireData');
    $('#_Statistics_form').submit();
}

function Add(){

    _form('#_form',function(rs){
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
}
function Save(_this){
    var pl = $(_this).parents('form');
    _form(pl,function(rs){
        if (rs.status == 200 ) {
            dialog.tip({msg:rs.msg});
        } else{
            dialog.tip({msg:rs.msg});
        }
    });
}

function SaveCallNote(_this){
    var pl = $(_this).parents('.wavBox');
    var note = pl.find('._note_save').val();
    var id = pl.find('._note_id').val();
    ajax({
        type: "POST",
        dataType: 'json',
        url: '/Device/SaveCallNote',
        data: "data[note]="+note+"&data[id]="+id,
        success: function(rs){
            if ( note == '' ) {
                pl.find('._note_icon').html('<i class="large remove bookmark icon"></i>');
            } else {
                pl.find('._note_icon').html('<i class="large bookmark icon " style="color:red;"></i>');
            }
            
            dialog.tip({msg:rs.msg});
        }
    });

}

//修改端口名称
function SavePortName(_this){
    var pl = $(_this).parents('.wavBox');
    var port_name = pl.find('._port_name').val();
    var device_id = pl.find('._device_id').val();
    var line_id = pl.find('._line_id').val();
    
    ajax({
        type: "POST",
        dataType: 'json',
        url: 'index.php?s=Device/SavePortName',
        data: "data[PortName]="+port_name+"&data[device_id]="+device_id+"&data[code]="+line_id,
        success: function(rs){
            pl.find('._port_name_txt').html('<br>'+port_name);
            $(_this).parents('#LoginBox').css('display','none');
            dialog.tip({msg:rs.msg});
        }
    });

}

/**
* 设备批量删除
*/
function deleteAllDevice(){
     dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                if ( $("._ids:checked").length == 0) {
                    dialog.tip({msg:'请选择需要删除的设备'});
                    return false;
                }
                var data = '';
                $("._ids:checked").each(
                    function(i,n){
                        var call_id = $(n).val();
                        if ( call_id) {
                            data += '&ids[]='+call_id;
                        }
                    }
                );
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: 'index.php?s=/Admin/DeviceCT/deleteAllDevice',
                    data: data,
                    success: function(rs){
                        dialog.tip({msg:rs.msg});
                        window.location.reload();
                    }
                });
            }},
            "Cancel": { text:'取消',"class": '', click: function() {  dialog.hide(); } }
        }
        
    });
}

/**
* 批量删除设备分组
*/
function doGroupRemoves(){
    dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                if ( $("._ids:checked").length == 0) {
                    dialog.tip({msg:'请选择需要删除的设备'});
                    return false;
                }
                var data = '';
                $("._ids:checked").each(
                    function(i,n){
                        var call_id = $(n).val();
                        if ( call_id) {
                            data += '&ids[]='+call_id;
                        }
                    }
                );
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: 'index.php?s=/Admin/DeviceCT/doGroupRemoves',
                    data: data,
                    success: function(rs){
                        dialog.tip({msg:rs.msg});
                        window.location.reload();
                    }
                });
            }},
            "Cancel": { text:'取消',"class": '', click: function() {  dialog.hide(); } }
        }
        
    });
}

/**
* 设备批量分组
*/
function bindGroup(){
    var ob = $("#group");
    if ( ob.length == 0 ) {
        return false;
    }
    ob.change(function(){
        var group_id = $(this).val();
        var data = 'group_id='+group_id;
        $("._ids:checked").each(
            function(i,n){
                var call_id = $(n).val();
                if ( call_id) {
                    data += '&ids[]='+call_id;
                }
            }
        );
        ajax({
            type: "POST",
            dataType: 'json',
            url: 'index.php?s=/Admin/DeviceCT/dosetGroup',
            data: data,
            success: function(rs){
                alert(rs.msg);
                window.location.reload();
            }
        });
    });
}

//删除设备 加入回收站
function DeviceRemove(id){
    dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: 'index.php?s=/DeviceCT/Remove',
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

//删除设备 彻底删除
function DeviceDelte(id){
    dialog.tip({
        msg:'该操作将会彻底删除设备,是否删除?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: 'index.php?s=/DeviceCT/DeviceDelte',
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

//删除设备分组
function GroupRemove(id){
    dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: 'index.php?s=/DeviceCT/deleteGroup',
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

function AdminRemove(id){
    dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '/Admins/Remove',
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


function bindAdmins(){
    var ob = $("._show_group_box");
    if ( $("#_admin_box").length == 0 ) {
        return false;
    }
    if ( ob.length > 0 ) {
        ob.click(function(){

            var pl = $(this).parents('.checkbox');
            var vl = pl.find('.group_ids_box').val();
            var ch = pl.find('.group_ids_box').prop('checked');
            if ( ch ) {
                ch = false;
            } else {
                ch = true;
            }
            $(".device_ids_"+vl).prop('checked',ch);
        });
    }
    
    $("#_change_all").click(function(){

        var ch = $("#_change_all_box").prop('checked');
        if ( ch ) {
            ch = false;
        } else {
            ch = true;
        }
        
        $("._purview").prop('checked',ch);
    });
    $("#_change_all_device_box").click(function(){

        var ch = $(this).prop('checked');
        
        $("._devices").prop('checked',ch);
        $("._groups").prop('checked',ch);
    });
    $("._groups").click(function(){
        
        var ch = $(this).prop('checked');
        var did = $(this).val();
        $(".device_ids_"+did).prop('checked',ch);
    });
    $("._show_group_box").click(function(){
        var did = $(this).attr('data-id');
        $("#_group_box_"+did).toggle();
    });
}


function downloadWav(id,file){
    window.open(file);
    ajax({
        type: "GET",
        dataType: 'json',
        url: '/index/DownloadLog',
        data: "id="+id,
        success: function(rs){
            
        }
    });
}

function sendMail(){
    var device_id = $("#_device_id").val();
    $('#_exp_form').attr('action','/'+device_id+'/sendMail');
    _form('#_exp_form',function(rs){
        if (rs.status == 200 ) {
            dialog.tip({
                msg:rs.msg,
                buttons:{
                    "Done": { text:'确定',"class": 'btn-done', click: function() { dialog.hide();   } }
                }
            });
        } else{
            dialog.tip({msg:rs.msg});
        }
    });
}

function sendExcel(){
    if ( $("._ids:checked").length == 0 && $("#_to_all:checked").length == 0 ) {
        dialog.tip({msg:'请选择需要导出的记录'});
        return false;
    }
    var device_id = $("#_device_id").val();
    $('#_exp_form').attr('action','/'+device_id+'/toExcel');
    $('#_exp_form').submit();
}

 

/** 批量下载录音文件*/
function downloadFiles(){
    if ( $("._ids:checked").length == 0 && $("#_to_all:checked").length == 0 ) {
        dialog.tip({msg:'请选择需要下载的记录'});
        return false;
    }
    var call_ids = '';
    $("._ids:checked").each(
        function(i,n){
            var call_id = $(n).val();
            if ( call_id) {
                call_ids += '&call_ids[]='+call_id;
            }
        }
    );
    var url = "index.php?s=/Admin/Device/DownloadLogs"+call_ids;
    window.location = url;
    // location.herf = url;
}


var play_file = '';
var play_ing = false;

function openPlay(_this){

    play_file  = $(_this).attr('data-file');
    $( "#_pop_ups" ).dialog({
        width: 'auto',
        height:'auto',
        modal: true,
        resizable: false,
        draggable: false,
        closeOnEscape: false,
        open: function(event, ui){
            $('.ui-dialog').find(".ui-dialog-titlebar").hide();
        }
    });
}
function openMonitor(line){
    $("#_line").val(line);
    var line_status = $("#_line_status_txt_"+line).text();
    $( "#Monitor_up" ).dialog({
        width: 'auto',
        height:'auto',
        modal: true,
        resizable: false,
        draggable: false,
        closeOnEscape: false,
        open: function(event, ui){
            $("#Monitor_up_status").text(line_status);
            $('.ui-dialog').find(".ui-dialog-titlebar").hide();
        }
    });
}
function PlayFile(){
    var play_file = $("#play_file").val();
    if ( play_ing == false ) {
        Play(play_file);
        $('#_play_btn').text('停止播放');
        play_ing = true;
    } else {
        Stop();
        $('#_play_btn').text('开始播放');
        play_ing = false;
    }
}

function Monitor(){
    var status =  false;
    if ( Connect_ing == true ) {
        var connact_status = GetConnectionStatus();
        if ( connact_status != 30 ) {
            alert('设备连接中, 请稍候...');
            return false;
        }
        if ( Monitor_ing == false ) {
            status = StartMonitor();
            if ( status ) {
                $("#_Monitor").text('监听中...');
            }
        } else {
            status = StopMonitor();
            if ( status ) {
                $("#_Monitor").text('开始监听');
            }
        }
    } else {
        Connect();
    }
}
function Connect() {  	
    var arg1 = $("#_ip").val();;
    if ( !arg1 ) {
        alert('请输入设备IP');
        return false;
    }
    Connect_ing = true;
    try {   
        var v = RunRec.Connect(arg1);
        Connect_ing = true;
        if ( v ) {
            $("#_Monitor").text('开始监听');
            $("#_Disconnect").show();
            $("#_Monitor_close").hide();
        }
    } catch (e) {
        alert(e.message);
    }

    return Connect_ing;
}
function Disconnect() {  	
    try {   
        var v = RunRec.Disconnect();
        Connect_ing = false;
    } catch (e) {   
        alert(e.message)   
    }   
    $("#_Monitor").text('开始连接');
    $("#_Disconnect").hide();
    $("#_Monitor_close").show();
}	

function GetConnectionStatus() {

    try {   
        var v = RunRec.GetConnectionStatus();
    } catch (e) {   
        alert(e.message)   
    }   
    return v;
}	

function StartMonitor() {  	
    var arg1 = $("#_line").val();;
    if ( !arg1 ) {
        alert('请输入设备端口');
        return false;
    }
    try {   
        if ( Connect_ing ) {
            var v = RunRec.StartMonitor(arg1);
            Monitor_ing = true;
        }
    } catch (e) {   
        alert(e.message)   
    }
    
    return Monitor_ing;
}
function StopMonitor() {
    try {   
        if ( Monitor_ing ) {
            var v = RunRec.StopMonitor();
            Monitor_ing = false;
        }
    } catch (e) {   
        alert(e.message)   
    }
    return Monitor_ing;
}
function Play(file) {
    try {
        var obj = document.getElementById("Player1");
        obj.URL = file;
        obj.controls.play();
    } catch(error) {
        alert(error)
    }
}
function Stop() {
    try {            
        var obj = document.getElementById("Player1");
        obj.controls.stop();
    } catch(error) {
        alert(error)
    }	
}


function UpdateOk(){
    dialog.tip({
        msg:'软件升级文件已上传,请等待设备升级',
        buttons:{
            "Done": { text:'确定',"class": 'btn-done', click: function() { window.location.reload();   } }
        }
    });
}

function setStat() {
    $("#_form").attr('action','/device/ResetStat');
    if ( $("._ids:checked").length == 0 ) {
        dialog.tip({msg:'请至少选择一个'});
        return;
    }
    _form('#_form',function(rs){
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
}

//批量设备分组
function dosetGroup() {
    $("#_form").attr('action','/Device/ResetStat');
    if ( $("._ids:checked").length == 0 ) {
        dialog.tip({msg:'请至少选择一个'});
        return;
    }
    if($('#_group').val() === 0){
         dialog.tip({msg:'请选择设备组'});
        return;
    }
    _form('#_form',function(rs){
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
}

function bindInquireDataLog(){
    var ob = $("._get_page_log");
    if ( ob.length == 0 ) {
        return false;
    }

    var _Log_posting = false;
    ob.click(function(){

        if ( _Log_posting == true) {
            return false;
        }

        var _this = this;
        var page = $(this).attr('data-page');
        var logs_count = $(this).attr('data-count');
        var list = $(this).attr('data-list');
        var key = $(this).attr('data-key');
        var all_line = $(this).attr('data-all');
        var pl = $(this).parents('tr');
        page = parseInt(page);
        list = parseInt(list);
        logs_count = parseInt(logs_count);
        page += 1;
        var page_now = page * list;

        $("#_Statistics_page").val(page);
        $("#_Statistics_key").val(key);
        $("#_Statistics_eco").val('');
        $("#_Statistics_all_line").val(all_line);
        var data = $('#_Statistics_form').serialize();
        
        _Log_posting = true;
        dialog.loading();
        $.ajax({
            type: "POST",
            dataType: 'html',
            url: '/Data/GetInquireDataLog',
            data: data,
            success: function(rs){
                _Log_posting = false;
                pl.before(rs);
                if ( page_now >= logs_count) {
                    pl.remove();
                } else {
                    pl.find('._page_now').text(page_now);
                    $(_this).attr('data-page',page);
                }
                setTimeout(function(){
                    dialog.hide();
                },500);
                
            }
        });
    });
}
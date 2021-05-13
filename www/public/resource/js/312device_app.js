$(document).ready(function(){
    bindGroup();
});
/**
 * 设备批量删除
 */
function deleteAllDeviceApp(){
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
                    url: 'index.php?s=/Admin/DeviceApp/deleteAllDevice',
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
                    url: 'index.php?s=/Admin/DeviceApp/doGroupRemoves',
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
            url: 'index.php?s=/Admin/DeviceApp/dosetGroup',
            data: data,
            success: function(rs){
                alert(rs.msg);
                window.location.reload();
            }
        });
    });
}

//删除设备
function DeviceAppRemove(id){
    dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() {
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: 'index.php?s=/DeviceApp/Remove',
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
//彻底删除设备
function DeviceAppDelte(id){
    dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() {
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: 'index.php?s=/DeviceApp/DeviceAppDelte',
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
                    url: 'index.php?s=/DeviceApp/deleteGroup',
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
    var url = "index.php?s=/Admin/DeviceApp/DownloadLogs"+call_ids;
    window.location = url;
    // location.herf = url;
}

//批量设备分组
function dosetGroup() {
    $("#_form").attr('action','/DeviceApp/ResetStat');
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

//关注设备 方法绑定
function Attention($id){
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: 'index.php?s=/Admin/DeviceApp/setAttention',
        data: "id="+$id,
        success: function(rs){
            _attention_posting = false;
            dialog.tip({msg:rs.msg});
        }
    });
}
//关注记录绑定
function LogAttention($id){
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: 'index.php?s=/Admin/DeviceApp/setLogAttention',
        data: 'id='+$id,
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
    var url = "index.php?s=/Admin/DeviceApp/DownloadLogs"+call_ids;
    window.location = url;

}

$(document).ready(function(){
    $('.ui.checkbox').checkbox();
    bindGroup();

});
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

function bindGroup(){
    var ob = $("#_group");
    if ( ob.length == 0 ) {
        return false;
    }
    ob.change(function(){
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
    });
}
    
/**
批量删除通讯组
*/
function deleteAllGroup(){
     dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                if ( $("._ids:checked").length == 0) {
                    dialog.tip({msg:'请选择需要删除的记录'});
                    return false;
                }
                var call_ids = '';
                $("._ids:checked").each(
                    function(i,n){
                        var call_id = $(n).val();
                        if ( call_id) {
                            call_ids += '&ids[]='+call_id;
                        }
                    }
                );
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: 'index.php?s=/Admin/Concat/deleteAllGroup',
                    data: call_ids,
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

function ContactRemove(id){
    dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '/Contact/Remove',
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

function SourceRemove(id) {
    dialog.tip({
        msg: '确定要删除吗?',
        buttons: {
            "Ok": {
                text: '确定', "class": 'btn-done', click: function () {
                    ajax({
                        type: "POST",
                        dataType: 'json',
                        url: 'index.php?s=/Concat/deleteSource',
                        data: "id=" + id,
                        success: function (rs) {
                            dialog.tip({
                                msg: rs.msg,
                                buttons: {
                                    "Done": {
                                        text: '确定', "class": 'btn-done', click: function () {
                                            window.location.reload();
                                        }
                                    }
                                }
                            });
                        }
                    });
                }
            },
            "Cancel": {
                text: '取消', "class": '', click: function () {
                    dialog.hide();
                }
            }
        }

    });
}
function SsourceRemove(id) {
    dialog.tip({
        msg: '确定要删除吗?',
        buttons: {
            "Ok": {
                text: '确定', "class": 'btn-done', click: function () {
                    ajax({
                        type: "POST",
                        dataType: 'json',
                        url: 'index.php?s=/Concats/deleteSource',
                        data: "id=" + id,
                        success: function (rs) {
                            dialog.tip({
                                msg: rs.msg,
                                buttons: {
                                    "Done": {
                                        text: '确定', "class": 'btn-done', click: function () {
                                            window.location.reload();
                                        }
                                    }
                                }
                            });
                        }
                    });
                }
            },
            "Cancel": {
                text: '取消', "class": '', click: function () {
                    dialog.hide();
                }
            }
        }

    });
}
function sendExcel(){
    if ( $("._ids:checked").length == 0 ) {
        dialog.tip({msg:'请选择需要导出的联系人'});
        return false;
    }
    console.log($('#_form'));
    $('#_form').attr('action','/Contact/toExcel').submit();

}


function ImportOk(){
    dialog.tip({
        msg:'联系人导入成功',
        buttons:{
            "Done": { text:'确定',"class": 'btn-done', click: function() { window.location.reload();   } }
        }
    });
}
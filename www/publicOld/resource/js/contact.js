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

function sendExcel(){
    if ( $("._ids:checked").length == 0 ) {
        dialog.tip({msg:'请选择需要导出的联系人'});
        return false;
    }
    $('#_form').attr('action','/contact/toExcel').submit();

}

function ImportOk(){
    dialog.tip({
        msg:'联系人导入成功',
        buttons:{
            "Done": { text:'确定',"class": 'btn-done', click: function() { window.location.reload();   } }
        }
    });
}
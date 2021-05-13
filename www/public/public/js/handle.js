
function handleAdd(){

    _form('#_handle_add_form',function(rs){
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


function handleSort(){
    $("#_handle_sort_form").attr('action','/Handle/Sort');
    _form('#_handle_sort_form',function(rs){
        dialog.tip({
            msg:rs.msg,
            buttons:{
                "Done": { text:'确定',"class": 'btn-done', click: function() { window.location.reload();   } }
            }
        });
    });
}

function handleRemove(id){   
    dialog.tip({
        msg:'确定要删除吗?',
        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                ajax({
                    type: "POST",
                    dataType: 'json',
                    url: 'index.php?s=/Device/deleteLog',
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


function handleRemoves(){   
    dialog.tip({
        msg:'确定要删除这些吗?',

        buttons:{
            "Ok": { text:'确定',"class": 'btn-done', click: function() { 
                $("#_handle_sort_form").attr('action','/Handle/Removes');
                _form('#_handle_sort_form',function(rs){
                    dialog.tip({
                        msg:rs.msg,
                        buttons:{
                            "Done": { text:'确定',"class": 'btn-done', click: function() { window.location.reload();   } }
                        }
                    });
                });
            }},
            "Cancel": { text:'取消',"class": '', click: function() {  dialog.hide(); } }
        }
        
    });
}


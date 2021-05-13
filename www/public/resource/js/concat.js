//添加、编辑联系人 分组提交方法
function Add() {
    _form('#_form', function (rs) {
        if (rs.status == 200) {
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
        } else {
            dialog.tip({msg: rs.msg});
        }
    });
}

//删除联系人
function ConcatRemove(id) {
    dialog.tip({
        msg: '确定要删除吗?',
        buttons: {
            "Ok": {
                text: '确定', "class": 'btn-done', click: function () {
                    ajax({
                        type: "POST",
                        dataType: 'json',
                        url: 'index.php?s=/Concats/delete',
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

//删除联系人分组
function GroupRemove(id) {
    dialog.tip({
        msg: '确定要删除吗?',
        buttons: {
            "Ok": {
                text: '确定', "class": 'btn-done', click: function () {
                    ajax({
                        type: "POST",
                        dataType: 'json',
                        url: 'index.php?s=/Concats/deleteGroup',
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

function import_excel(url) {
    $("#text").html($("#file").val());
    var xhr;
    var fileObj = document.getElementById("file").files[0]; // js 获取文件对象
    // var url =  "http://localhost:8080" + "/api/attachment/upload"; // 接收上传文件的后台地址

    var form = new FormData(); // FormData 对象
    form.append("file", fileObj); // 文件对象

    xhr = new XMLHttpRequest();  // XMLHttpRequest 对象
    xhr.open("post", url, true); //post方式，url为服务器请求地址，true 该参数规定请求是否异步处理。
    xhr.onload = uploadComplete; //请求完成
    xhr.onerror = uploadFailed; //请求失败

    xhr.upload.onloadstart = function () {//上传开始执行方法
        ot = new Date().getTime();   //设置上传开始时间
        oloaded = 0;//设置上传开始时，以上传的文件大小为0
    };

    xhr.send(form); //开始上传，发送form数据
    //上传成功响应
    function uploadComplete(evt) {
        //服务断接收完文件返回的结果
        var data = JSON.parse(evt.target.responseText);
        if (data.code) {
            dialog.tip({
                msg: data.msg,
                buttons: {
                    "Done": {
                        text: '确定', "class": 'btn-done', click: function () {
                            if (data.code == 200) {
                                window.location.reload();
                            } else {
                                dialog.hide();
                            }
                        }
                    }
                }
            });
        }
    }

    //上传失败
    function uploadFailed(evt) {
        alert("上传失败！");
    }
}
function telToAccount(url, del) {
    var str = '';
    $("input[name='ids[]']").each(function (k, v) {
        if ($(v).prop('checked')) {
            str += $(v).val() + ',';
        }
    });
    var account_id = $("#select_userid").val();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url,
        data: "ids=" + str + "&account_id=" + account_id + "&del=" + del,
        success: function (data) {
            if (data.code) {
                dialog.tip({
                    msg: data.msg,
                    buttons: {
                        "Done": {
                            text: '确定', "class": 'btn-done', click: function () {
                                if (data.code == 200) window.location.reload(); else dialog.hide();
                            }
                        }
                    }
                });
            }
        }
    });
}
// var wsflag = 0;
function ws_contact(id, url) {
    // if (wsflag == 1) {
    //     return;
    // }

    var ws_address = $("#ws_address").val();
    var ws_line = $("#ws_line").val();
    var switch_num = $("#switch_num").val();
    var tel = $("#tel").val();
    // var district_id=$("#district_id").val();
    var district_code = $("#district_code").val();
    // var district_name=$("#district_id option:selected").text();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url,
        data: "id=" + id + "&ws_address=" + ws_address + "&ws_line=" + ws_line + "&switch_num=" + switch_num + "&district_code=" + district_code + "&tel=" + tel,
        success: function (data) {
            if (data.code == 200) {
                if (data.data.id) {
                    crm_show(data.data.id)
                } else {
                    $("#crm_window").find("input").val("");
                    $("textarea[name=crm_note]").val("");
                    $("textarea[name=crm_note_list]").val("");
                    $("select[name=crm_group_id]").val("");
                    $("select[name=crm_feedback_type]").val("");
                    $("select[name=crm_source_id]").val("");
                    $("select[name=crm_important_level]").val("");
                    $("#crm_call_logs_body").html("");
                    $("input[name=crm_id]").val(-1);
                    $("input[name=crm_tel1]").val(data.data.tel);
                    $("input[name=crm_remind_time]").val("");
                    $("#crm_window").show();
                }
                //实现化WebSocket对象，指定要连接的服务器地址与端口
                var socket = new WebSocket("ws://" + ws_address + ":1818");
                //打开事件
                socket.onopen = function () {
                    // wsflag = 1;
                    console.log('websocket onopen');
                    $.ajax({
                        type: "POST",
                        dataType: 'json',
                        url: "./index.php?s=/Concat/dailed",
                        data: "id=" + id,
                        success: function () {
                            var dail_id = 'is_dailed_' + id;
                            $("#" + dail_id).text('是');
                        }
                    })
                    if ($("#cancelDail")) {
                        $("#cancelDail").val(data.data.tel);
                    }
                    // socket.send("line=1number=13522649022callOutId=12389522abc>");
                    // socket.close();
                    var timestamp = (new Date()).valueOf();
                    socket.send("line=" + ws_line + "number=" + data.data.tel + "callOutId=" + timestamp);
                    socket.close();
                    crm_tip(data.data.phone_msg);
                    // crm_tip("已经拨号，弹屏6秒后请拿起听筒！6秒后自动消失!<br/>"+data.data.phone_msg);
                    // setTimeout(function () {
                    //     dialog.hide();
                    // }, 6000);
                };
                //获得消息事件
                // socket.onmessage = function (msg) {
                //     console.log('websocket onmessage');
                //     console.log(msg);
                // };
                // //关闭事件
                // var wsCloseFlag=0;
                socket.onclose = function () {
                    // wsflag = 0

                };
                //发生了错误事件
                socket.onerror = function (e) {
                    console.log('websocket error:');
                    console.log(e);
                    socket.close();
                }
            } else {
                dialog.tip({
                    msg: data.msg,
                    buttons: {
                        "Done": {
                            text: '确定', "class": 'btn-done', click: function () {
                                dialog.hide();
                            }
                        }
                    }
                });
            }
        }
    });
}
function feedbackChange(url, optionId, id) {

    var val = $("#" + optionId).val();
    if ($("#is_dailed_" + id).text() == '否') {
        alert('请先拨号！');
        return
    }
    // alert(val)
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url,
        data: "id=" + id + "&feedback_type=" + val,
        success: function (data) {
            if (data.code) {
                dialog.tip({
                    msg: data.msg,
                    buttons: {
                        "Done": {
                            text: '确定', "class": 'btn-done', click: function () {
                                dialog.hide();
                            }
                        }
                    }
                });
            }
        }
    });
}


function Calltel(url, del) {

    var tel = $("#tel").val();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url,
        data: "tel=" + del + "&type=call&phone=" + tel,
        success: function (data) {
            if (data.code) {
                dialog.tip({
                    msg: data.msg,
                    buttons: {
                        "Done": {
                            text: '确定', "class": 'btn-done', click: function () {
                                if (data.code == 200) window.location.reload(); else dialog.hide();
                            }
                        }
                    }
                });
            }
        }
    });
}
function telTodel(url, del) {

    var tel = $("#tel").val();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url,
        data: "tel=" + del + "&type=del&phone=" + tel,
        success: function (data) {
            if (data.code) {
                dialog.tip({
                    msg: data.msg,
                    buttons: {
                        "Done": {
                            text: '确定', "class": 'btn-done', click: function () {
                                if (data.code == 200) window.location.reload(); else dialog.hide();
                            }
                        }
                    }
                });
            }
        }
    });
}
// function cancelDialed() {
//     var ws_address = $("#ws_address").val();
//     var socket = new WebSocket("ws://" + ws_address + ":1818");
//     //打开事件
//     socket.onopen = function () {
//         var timestamp = (new Date()).valueOf();
//         var ws_line = $("#ws_line").val();
//         // line=1number=STOPcallOutId=12389522abc>
//         socket.send("line=" + ws_line + "number=STOPcallOutId=" + timestamp);
//     };
// dialog.tip({
//     msg: "",
//     buttons: {
//         "Done": {
//             text: '确定', "class": 'btn-done', click: function () {
//                 dialog.hide();
//             }
//         },
//     }
// });
// }

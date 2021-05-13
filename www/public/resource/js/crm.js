function crm_show(id) {

    ajax({
        type: "POST",
        dataType: 'json',
        url: 'index.php?s=/Admin/Crm/ConcatInfo',
        data: "id=" + id,
        success: function (rs) {
            if (rs.code == 200) {
                var prefix = 'crm_';
                for (x in rs.data) {
                    if ($("input[name=" + prefix + x + "]")) {
                        $("input[name=" + prefix + x + "]").val(rs.data[x])
                    }
                }
                $("textarea[name=crm_note]").val(rs.data['note']);
                $("textarea[name=crm_note_list]").val(rs.data['note_list']);
                $("select[name=crm_group_id]").val(rs.data['group_id']);
                $("select[name=crm_feedback_type]").val(rs.data['feedback_type']);
                $("select[name=crm_source_id]").val(rs.data['source_id']);
                $("select[name=crm_important_level]").val(rs.data['important_level']);
                $("#crm_call_logs_body").html(rs.data['call_logs']);
                $("#crm_window").show();
            } else {
                dialog.tip({
                    msg: rs.msg,
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
var crm_form_posting = false;
function crm_form(form_id) {
    if (crm_form_posting == true) {
        return false;
    }
    var fff;

    if (typeof form_id == 'object') {
        fff = form_id;
    } else {
        fff = $(form_id);
    }
    var url = fff.attr('action');
    var data = fff.serialize();
    if (!url || !data) {
        return false;
    }
    _form_posting = true;
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url,
        data: data,
        success: function (rs) {
            crm_form_posting = false;
            dialog.tip({
                msg: rs.msg,
                buttons: {
                    "Done": {
                        text: '确定并刷新页面', "class": 'btn-done', click: function () {
                            window.location.reload();
                        }
                    },
                    "Done1": {
                        text: '确定', "class": 'btn-done', click: function () {
                            dialog.hide();
                        }
                    }
                }
            });
            if ($("textarea[name=crm_note]").val()) {
                $("textarea[name=crm_note_list]").val($("textarea[name=crm_note_list]").val() + "\n" + $("textarea[name=crm_note]").val());
                $("textarea[name=crm_note]").val("")
            }
        }
    });
}
function popDeviceCrm(device_call_id,id,tel1) {
    var tel1 = tel1 || 0
    ajax({
        type: "POST",
        dataType: 'json',
        url: 'index.php?s=/Admin/Crm/ConcatInfo',
        data: "device_call_id=" + device_call_id+'&id='+id+'&tel1='+tel1,
        success: function (rs) {
            if (rs.code == 200) {
                var prefix = 'crm_';
                for (x in rs.data) {
                    if ($("input[name=" + prefix + x + "]")) {
                        $("input[name=" + prefix + x + "]").val(rs.data[x])
                    }
                }
                $("textarea[name=crm_note]").val(rs.data['note']);
                $("textarea[name=crm_note_list]").val(rs.data['note_list']);
                $("select[name=crm_feedback_type]").val(rs.data['feedback_type']);
                $("select[name=crm_group_id]").val(rs.data['group_id']);
                $("select[name=crm_source_id]").val(rs.data['source_id']);
                $("select[name=crm_important_level]").val(rs.data['important_level']);

                if(typeof pop_tel1!=="undefined"){
                    $("input[name=crm_tel1]").val(pop_tel1)
                }
                $("#crm_call_logs_body").html(rs.data['call_logs']);
                $("#crm_window").show();
            } else {
                dialog.tip({
                    msg: rs.msg,
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
function crmTelLayer() {
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: './index.php?s=/Admin/crm/telLayer',
        data: "",
        success: function (data) {
            if (data.code == 200) {
                if ($("#crm_window")){
                    if (data.data.id) {
                        popDeviceCrm(0,data.data.id)
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
                }
                if(1||document.hidden){
                    setNotification(
                        'index.php?s=/Admin/Device/showLogs&id='+data.data.device_id+'&tel1='+data.data.tel+'&popDeviceCrmId='+data.data.device_call_id,
                        data.data.tel
                    )
                }
            }
        }
    })
    window.setTimeout(crmTelLayer, 3000);
}

if (typeof needCrmTelLayer != 'undefined' && needCrmTelLayer) {
}
crmTelLayer();
function crm_tip(msg) {
    $('#crm_tip').show();
    $('#crm_tip_z_index').show()
    $('#crm_tip_content').html(msg)
    setTimeout(function () {
        $('#crm_tip').hide();
        $('#crm_tip_z_index').hide()
    }, 6000);
}
function cancelDialed() {
    var ws_address = $("#ws_address").val();
    var socket = new WebSocket("ws://" + ws_address + ":1818");
    //打开事件
    socket.onopen = function () {
        var timestamp = (new Date()).valueOf();
        var ws_line = $("#ws_line").val();
        // line=1number=STOPcallOutId=12389522abc>
        socket.send("line=" + ws_line + "number=STOPcallOutId=" + timestamp);
    };
    return;
}
function setNotification(url,tel){
    var notify = new Notification("您有新的来电，请及时接听",{
        body: '来电号码: '+tel,
        lang:"zh-CN",
        icon:"http://backend.jin6.com/@webroot/uploads/image/20180423/1524455833949078.png"
    });
    notify.onshow = function() {
        window.open(url, '_blank');
        console.log('Notification showning!');
    };
    notify.onclick = function() {
        console.log('Notification have be click!');
        notify.close();
    };
    notify.onerror = function() {
        console.log('error!');
        // 手动关闭
        notify.close();
    };
    notify.onclose = function(){
        console.log("close");
    }
}

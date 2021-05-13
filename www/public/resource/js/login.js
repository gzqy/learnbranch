$(document).ready(function () {
    change_code();
    $("body").keydown(function(event) {
        if (event.keyCode == "13") {
            Login();
        }
    }); 
});
function Login(){
    _form('#_login_form',function(rs){
        if (rs.status == 200 ) {
            if ( rs.url ) {
                dialog.tip({
                    msg:rs.msg,
                    buttons:{
                        "Done": { text:'确定',"class": 'btn-done', click: function() { 
                            go(rs.url);
                        } }
                    }
                });
            } else {
                go('./index.php');
            }
            
        }else{
            change_code();
            dialog.tip({msg:rs.msg});
        }
    });
}

function change_code(){
    $("#_Validate_Code").attr("src", 'index.php?s=/Admin/Login/validatecode');
}
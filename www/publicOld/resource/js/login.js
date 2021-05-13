$(document).ready(function () {
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
                go('/');
            }
            
        } else{
            change_code();
            dialog.tip({msg:rs.msg});
        }
    });
}

function change_code(){
    var t = Math.random();
    $("#_Validate_Code").attr("src", '/login/validatecode?t='+t);
}
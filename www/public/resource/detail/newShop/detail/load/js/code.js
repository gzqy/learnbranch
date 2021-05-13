/*
* @Author: Marte
* @Date:   2017-05-31 19:34:44
* @Last Modified by:   Marte
* @Last Modified time: 2017-05-31 19:40:09
*/

    var getCode = document.getElementById("getCode");
    var num = 60;
    getCode.onclick=function(){
        // clearInterval(timer);
        this.disabled = true;
        timer = setInterval(limit,1000);
        function limit(){
            num--;
            if(num >= 0){
                getCode.style.cursor= 'not-allowed';
                getCode.style.backgroundColor="#eee";
                getCode.value= "还剩"+num+"秒";
            }else{
                getCode.style.cursor= 'pointer';
                getCode.value= "重新发送";
                getCode.disabled= false;
                clearInterval(timer);
                num = 60;
            }
        }
    }
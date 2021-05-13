var validate={};

validate.email=function(email){
    
    var pattern = /^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
    return pattern.test(email);
}

validate.num=function(num){
    
    var pattern = /^\d+$/;
    return pattern.test(num);
}

validate.letter=function(letter){
    
    var pattern = /^[a-zA-Z]+$/;
    return pattern.test(letter);
}

validate.keynum=function(){
    var currKey=0,e=e||event; 
        currKey=e.keyCode||e.which||e.charCode;
    if((currKey>=48 && currKey<=57) || (currKey>=97 && currKey<=105)){
        return true;
    }
    return false;
}

validate.keyword=function(){
    
    var currKey=0,e=e||event; 
        currKey=e.keyCode||e.which||e.charCode;
    if(currKey>=65 && currKey<=90){
        return true;
    }
    return false;
}

validate.china=function(word){
    
   var pattern = /^[\u4e00-\u9fa5]+$/; 
   return pattern.test(word);
}


validate.downNum=function(){
    
    var currKey=0,e=e||event; 
        currKey=e.keyCode||e.which||e.charCode;
        //数字键 || 方向键 || Backspace || Delete
        if((currKey>=48 && currKey<=57) || (currKey>=96 && currKey<=105) || (currKey>=37 && currKey<=40) || currKey==8 || currKey==46){
            return true;
        }
        return false;
}

validate.downLetter=function(){
    
    var currKey=0,e=e||event; 
        currKey=e.keyCode||e.which||e.charCode;
        //字母 || 方向键 || Backspace || Delete
        if((currKey>=65 && currKey<=90) || (currKey>=37 && currKey<=40) || currKey==8 || currKey==46){
            return true;
        }
        return false;
}

validate.enter=function(){
    
    var currKey=0,e=e||event; 
        currKey=e.keyCode||e.which||e.charCode;
        if(currKey==13){
            return true;
        }
        return false;
}
/*
* @Author: Marte
* @Date:   2017-05-03 11:24:54
* @Last Modified by:   Marte
* @Last Modified time: 2017-05-17 15:19:02
*/
$(function(){
   // 一级导航点击切换效果
    $('.oneNav li').click(function(event) {
        $(this).addClass('cur').siblings().removeClass('cur');
    });
    // 一级导航点击切换效果
    //
   // 一级导航一级二级导航效果
   $('.oneNav li').hover(function() {
       $(this).addClass('cur').siblings().removeClass('cur');
   }, function() {
       $(this).removeClass('cur');
   });
   // 一级导航一级二级导航效果

   // 点击切换设置
   $('.head .about .something p').click(function(event) {
      if($(this).children('em').html() == '▼'){
        $(this).children('em').html('▲');
        $('.setNav').show();
      }else{
        $(this).children('em').html('▼');
        $('.setNav').hide();
      }
   });
   // 点击切换设置

})
/*
* @Author: Marte
* @Date:   2017-05-03 11:24:54
* @Last Modified by:   Marte
* @Last Modified time: 2017-05-17 15:23:46
*/
$(function(){
   $('.content .con .nav').addClass('h');
   $('.h dd').hover(function() {
     $(this).addClass('cur').siblings('dd').removeClass('cur');
   }, function() {
     $(this).removeClass('cur');
   });
   $('.h dd').click(function(event) {
     $(this).addClass('cur').siblings('dd').removeClass('cur');
   });
})
/* Chinese initialisation for the jQuery UI date picker plugin. */
/* Written by Ressol (ressol@gmail.com). */
jQuery(function($){
	$.datepicker.regional['EN'] = {
		closeText: 'Close',
		prevText: '&#x3c;Last',
		nextText: 'Next&#x3e;',
		currentText: 'Today',
		monthNames: ['January','February','March','April','May','June',
		'July','August','September','October','November','December'],
		monthNamesShort: ['1','2','3','4','5','6',
		'7','8','9','10','11','12'],
		dayNames: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
		dayNamesShort: ['Sun.','Monday','Tues.','Wed.','Thur.','Fri.','Sat.'],
		dayNamesMin: ['Sun.','Mon.','Tues.','Wed.','Thur.','Fri.','Sat.'],
		weekHeader: 'Week',
		dateFormat: 'yy/mm/dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: '&nbsp;'};
	$.datepicker.setDefaults($.datepicker.regional['EN']);
});

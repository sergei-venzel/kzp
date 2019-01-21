$('.order div.details').css('display','none');
	
$('span.date').click(function() {

	var prnt = $(this).parents('div.order');

	var ch = prnt.children('div.details');
	var st = ch.css('display');
	if(st === 'none')
		ch.css('display','block');
	else
		ch.css('display','none');
});

$('.full').click(function() {

	var id = parseInt($(this).attr('id').replace(/^o_/,''));
	window.location = loc_scr+'?accept='+id;
});

$('.drop').click(function() {

	var id = parseInt($(this).attr('id').replace(/^o_/,''));
	var q = confirm('Удалить заказ?');
	if(q !== false)
		window.location = loc_scr+'?del_order='+id;
});

$('#save_answer').click(function() {
	
	var obj = $(this);
	var oldVal = obj.val();
	$('#err').empty();
	obj.attr('disabled',true).val('Ожидаем');
	$.post(
		loc_scr,
		{answer:$('#answer').val(),asave:1},
		function(json) {
			
			if(json.err === '') {
				obj.attr('disabled',false).val(oldVal);
			}
			else
				$('#err').html(json.err);
		},
		'json'
	);
});
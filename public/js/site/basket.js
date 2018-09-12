$('div.inbasket').on('click', function() {
	
	var obj = $(this);
	var id = parseInt(obj.attr('id').replace(/^com_/,''));
	obj.css('display','none');
	var uw = u_wait(obj.parents('div.card'),'Добавление<br />в&nbsp;корзину...','prepend');
	$.getJSON(
		r_uri,
		{add_to_basket:id},
		function(json) {
			
			uw.remove();
			obj.css('display','block');
			if(json.err == '') {
				
				var sw = message_pane(obj.parents('div.card'),'<p>Товар добавлен в корзину</p><p><a href="'+basket_page+'">оформление заказа</a></p>','bsk','prepend',{offTop:50,offLeft:2});
				$('#b_q b').text(json.items);
				$('#b_s b').text(json.total);
				setTimeout(close_bal,10000);
			}
		}
	);
});

function close_bal() {
	
	$('div#bsk').remove();
}

$('#elements span.remove').click(function() {
	
	obj = $(this);
	var id = parseInt(obj.attr('id').replace(/^product_/,''));
	
	if(id != NaN && id > 0) {
		
		if(confirm('Удалить товар из корзины?')) {
			
			$('input#recalc').attr('disabled',true);
			$.getJSON(
				ajs,
				{remove_from_basket:id},
				function(json) {
					
					$('input#recalc').attr('disabled',false);
					if(json.err == '') {
						
						if(json.r_item == id)
							obj.parent('div#item').remove();
						
						$('#b_q b').text(json.items);
						$('#b_s b').text(json.total);
						$('span#final').text(json.total);
						if(json.ru_total)
							$('span#final_ru').text(json.ru_total);
							
						var cnt = $('div#elements div#item').length;
						if(cnt == 0) {
							$('div#elements').html('<div class="empty_mess"><p>Ваша корзина пуста.</p><p>Для выбора товара, перейдите в каталог товаров.</p></div>');
							/*$('#b_q b').text('0');
							$('#b_s b').text('0');*/
						}
					}
				}
			);
		}
	}
	
});

$('input[name="recalc"]', '#basket_order').on('click', function(e) {

    var $form = $(this).parents('form');
    $('.require', $form).prop('disabled', true);
});

$('#basket_order').ajaxForm(
	{
		url:$('#basket_order').attr('action'),
		type:'post',
		dataType:'json',
        beforeSubmit:preAct,
		success:vld
	}
);

function preAct(formData, jqForm, options) { 
    
 	var act = 'order';
 	for(var i=0; i < formData.length; i++)
 	{
 		if(formData[i].name === 'recalc')
 			act = 'recalc';
 	}

 	if(act === 'order') {

 		$('input#submt').attr('disabled',true).css({cursor:'wait',backgroundPosition:'left -22px'});
 	}
 	else {
 		
 		$('input#recalc').attr('disabled',true).css({cursor:'wait',backgroundPosition:'left -22px'});
 	}
    
    return true; 
}

function vld(json, statusText, xhr, $form) {
	
	if(json.err === '') {

	    if(json.act === 'recalc') {

            $('.require', $form).prop('disabled', false);
	        $('input#recalc').attr('disabled',false).css({cursor:'pointer',backgroundPosition:'left top'});
			$('#b_q b').text(json.items);
			$('#b_s b').text(json.total);
			$('span#final').text(json.total);
			if(json.ru_total)
				$('span#final_ru').text(json.ru_total);
			
			var pr = json.recalc_items;
			if(pr.length > 0) {
				
				for(var i=0; i<pr.length; i++) {
					
					$('.i_'+pr[i].id+' .sum span b').text(pr[i].value);
				}
			}
			
			if(json.kill.length > 0) {
				
				for(var i=0; i<json.kill.length; i++) {
					$('div.i_'+json.kill[i]).remove();
				}
			}
			
			var cnt = $('div#elements div#item').length;
			if(cnt == 0) {
                $('div#elements').html('<p>Ваша корзина пуста.</p><p>Для выбора товара, перейдите в каталог товаров.</p>');
            }
		}
		else {
			
			if(json.fields == 1 || json.code == 1) {
				
				$('img#captcha').attr('src','/vc/CaptchaSecurityImages.php?v='+Math.random());
				$('input#cptch').val('');
				
				$('input#submt').attr('disabled',false).css({cursor:'',backgroundPosition:'left bottom'});
				
				$('div.o_mess').empty();
				if(json.fields == 1)
					$('div.o_mess').append('<p>Обязательные поля должны быть корректно заполнены.</p>');
				
				if(json.code == 1)
					$('div.o_mess').append('<p>Неверно введен код с картинки.</p>');
			}
			else {
				
				$('#b_q b').text('0');
				$('#b_s b').text('0');
				$('div#elements').html('<div class="empty_mess">Ваша заявка отправлена.</div>');
				$('#guidemess').css('display','inline');
			}
		}
	}
	else {
		$('div#elements').html('<p class="err_mess">Ошибка: '+json.err+'</p>');
	}
}
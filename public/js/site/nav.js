$('div.preview a.highslide').click(function() {
		
	return hs.expand($(this).get(0));
});

function u_wait(obj,str,inplace,wID) {
	
	wID = wID || 'uw_' + Math.floor(Math.random() * 26) + Date.now();
	var fict = document.createElement('div');
	var aw = document.createElement('div');
	
	$(aw).attr('class','u_wait boxed corner-all').html(str).prependTo($(fict));
	$(fict).attr('class','fict').attr('id',wID);
	
	switch(inplace) {
	
		case 'before':
			$(fict).insertBefore($(obj));
		break;
		
		case 'after':
			$(fict).insertAfter($(obj));
		break;
		
		case 'append':
			$(fict).appendTo($(obj));
		break;
		
		case 'prepend':
			$(fict).prependTo($(obj));
		break;
		
		default:
			$(fict).insertBefore($(obj));
	}
	
	return $(fict);
}

function message_pane(obj, html, pID, inplace) {

	obj = $(obj);
	var eID;
	if(pID && pID !== '')
		eID = pID;
	else
		eID = 'warn_message';
	$('#'+eID).remove();
	var fict = $('<div/>', {'class':'fict','id':eID});
	var aw = $('<div/>', {'class':'su_mess shadow'});
	
	switch(inplace) {
	
		case 'before':
			fict.insertBefore(obj);
		break;
		
		case 'after':
			fict.insertAfter(obj);
		break;
		
		case 'append':
			fict.appendTo(obj);
		break;
		
		case 'prepend':
			fict.prependTo(obj);
		break;
		
		default:
			fict.insertBefore(obj);
	}
	
	aw.html(html).prependTo(fict);
	
	var clb = $('<span/>', {'class':'close','title':'Закрыть'});
	clb.prependTo(aw).click(function() {
		
			fict.remove();
		});
	
	var aWidth = aw.css('width');
	var leftOff = /*-parseInt(aWidth)/2 + */'0px';
	
	aw.css('width','0px');
	
	aw.animate({width:aWidth},{queue:false,duration:700})
		.animate({top:'20px'},{queue:false,duration:700})
		.animate({left:leftOff},{queue:false,duration:700});
		
	
	return fict;
}

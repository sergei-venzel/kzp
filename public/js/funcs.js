
function startclock() {
  stopclock();
  //timerID = setInterval("non_stop()", 60000);
}

function stopclock() {
  if (timerID)
    clearInterval(timerID);
  timerID = null;
}


function double_confirm() {
	
	if(confirm('Еще раз подумайте, стоит ли это делать?')) {
		
		return confirm('Если сейчас снова нажмете "OK",\r\nто возврата не будет!');
	}
	else
		return false;
}



function trig(elm) {
    
    var elem = document.getElementById(elm);
    
    if(elem) {
    	
    	var elem_disp = elem.style.display;
	    
	    if(elem_disp=="block") {
	        elem.style.display="none";
	    }
	    else {
	        elem.style.display="block";
	    }
    }
}

function react_on_check(fld,r_elm) {
	
	var elem = document.getElementById(r_elm);
	
	if(elem && fld) {
		
		if(fld.checked == true)
			elem.style.display = 'none';
		else {
			
			if(document.all)
				elem.style.display = 'block';
			else {
				
				if(elem.tagName == 'TR')
					elem.style.display = 'table-row';
				else
					elem.style.display = 'block';
			}
		}
	}
}

function show_save_alert(msg) {

    var timemark=null;
	clearTimeout(timemark);

    timemark = setTimeout(function() {
        save_alert(msg);
        timemark = setTimeout(close_alert, 1500);
	}, 1);
}

function close_alert() {
    var elm = document.getElementById("sv");
    if(elm) {
    
    	elm.style.top='-3000px';
    	elm.style.left='-3000px';
	}
}

function save_alert(msg) {
    
    var elm = $('#sv');
    var txt = $('#a_txt');

    if(!elm.length) {
    
        elm = $('<div id="sv" class="save_alert"></div>');
        txt = $('<div id="a_txt" class="text"></div>');
        elm.append(txt).append('<div class="shadow">&nbsp;</div>');
        $('body').append(elm);
	}

    elm.css('top', getVertPos());
    elm.css('left', getHorPos());

    if(txt.length) {
        txt.html(msg);
    }
}

function alert_elm(msg) {
	
	
	var oldElm = document.getElementById("sv");
	if(oldElm) {
		document.body.removeChild(oldElm);
	}
	
	var first_d = document.createElement('DIV');
	first_d.setAttribute('id','sv');
	first_d.className = 'save_alert';
	first_d.style.top = getVertPos();//'50px';
	first_d.style.left = getHorPos();
	
	var sec_d = document.createElement('DIV');
	sec_d.className = 'text';
	sec_d.setAttribute('id','a_txt');
	
	sec_d.innerHTML = msg;
	
	third_d = document.createElement('DIV');
	third_d.className = 'shadow';
	third_d.innerHTML = '&nbsp;';
	
	first_d.appendChild(sec_d);
	first_d.appendChild(third_d);
	
	return first_d;
}


function getHorPos() {
	
	var w_width = getInsideWindowWidth();
    if(w_width == 0)
    	w_width=400;
    
    return (w_width-200)/2 + 'px';
}

function getVertPos() {
	
	var height = getInsideWindowHeight();
    if(height == 0)
    	height=400;
    
    return (height-100)/2 + 'px';
}


function wait() {
	
	var elm = alert_elm('<img src="/admin/images/waiting.gif" />');
	
	if(elm) {
		document.body.appendChild(elm);
	}

	return elm;
}

function getInsideWindowWidth() {
    if (window.innerWidth) {
        return window.innerWidth;
    } else if (document.all) {
        // measure the html element's clientWidth
        return document.body.parentElement.clientWidth;
    } else if (document.body && document.body.clientWidth) {
        return document.body.clientWidth;
    }
    return 0;
}



function getInsideWindowHeight() {
    if (window.innerHeight) {
        return window.innerHeight;
    } else if (document.all) {
        // measure the html element's clientHeight
        return document.body.parentElement.clientHeight;
    } else if (document.body && document.body.clientHeight) {
        return document.body.clientHeight;
    }
    return 0;
}



function getTinyHtml(elm) {

	var ifr = document.getElementById(elm);
	
	if(ifr) {
	
		var ifr_document = ifr.contentWindow.document;
		
		if(ifr_document) {
		
			// for Opera < 9.06
			fuck_opera(ifr_document);
			//
			
			return ifr_document.getElementById('tinymce').innerHTML;
		}
		else return false;
	}
	else return false;
}

function fuck_opera(obj) {
	
	if(!obj.getElementById('tinymce')) {
		
		var test = obj.getElementsByTagName('HTML');
	
		if(test) {
			
			var t_html = test[0].getElementsByTagName('BODY');
			if(t_html) {
				
				var b_id = t_html[0].getAttribute('id');
				
				if(b_id != 'tinymce')
					t_html[0].setAttribute('id','tinymce');
			}
		}
	}
}

function update_from_tiny(pref,url,cont_id) {

	var cont = getTinyHtml(pref + '_ifr');
	
	var real_id = cont_id;
	if(!cont_id || cont_id==0 || cont_id == '')
		real_id = 0;
	
	var return_result=false;
	
	if(cont) {
		
		wait();
		JsHttpRequest.query(
		    url,
		    {
		        'save_content': real_id,
		        'content': cont
		    },
		     
		    function(result) {
		    
		        if (result['updated']==1) {
		            
	                show_save_alert('Данные сохранены.');
	                if(result['new_id'])
	                	edited_id = result['new_id'];
		        }
		        else {
		        	
		        	show_save_alert('Данных для сохранения нет.');
		        }
		    },
		        true
		);
	}
}

function switcher(elm_1,elm_2) {
	
	var first_elm = document.getElementById(elm_1);
	var second_elm = document.getElementById(elm_2);
	
	if(first_elm && second_elm) {
		
		if(first_elm.style.display != 'none') {
			
			first_elm.style.display = 'none';
			second_elm.style.display = 'block';
		}
		else {
			
			first_elm.style.display = 'block';
			second_elm.style.display = 'none';
		}
	}
}



function hider(plug,plugable) {
    
    //var but = document.getElementById(plug);
    
    for(var i=0; i<plugable.length; i++) {
        var process = document.getElementById(plugable[i]);
        var disp = process.style.display;
        if(disp=="block") {
            process.style.display="none";
            plug.style.backgroundImage="url(images/hide.gif)";
        }
        else {
            process.style.display="block";
            plug.style.backgroundImage="url(images/show.gif)";
        }
    }
}

function clean_cache(url,fname,exp) {
    
    wait();
    JsHttpRequest.query(
        url, // backend
        {
            'to_clean': fname
        },
        // Function is called when an answer arrives. 
        function(result) {
        
            if (result) {
                
                show_save_alert('Кэш удален');
                var for_eval = "'" + exp + "'";
                eval(for_eval);
            }
            else {
            	
            	show_save_alert('Миссия не выполнима!');
            }
        },
            false  // do not disable caching
    );
    return false;
}


function sort_order(act_page,pid,qstr,dis_id) {

	$(dis_id).sortable('disable');
	
/*	alert(qstr +'\r\n'+t_qstr);*/

	if(qstr != t_qstr) {
	
		wait();
		JsHttpRequest.query(
	        act_page + '?' + qstr, // backend
	        {
	            'update_level': pid,
	            'save_content': 1
	        },
	        // Function is called when an answer arrives.
	         
	        function(result) {
	        
	            if (result['updated']==1) {
	                
	                $(dis_id).sortable('enable');
	                show_save_alert('Порядок изменен.');
	            }
	            else {
	            
	                show_save_alert('Изменений нет.');
				}
	        },
	            true
	    );
	    
	    t_qstr = qstr;
	}
	else
		$(dis_id).sortable('enable');
}


function get_select(handler,mthd,disp_cont,f_name,sid) {

	var container = document.getElementById(disp_cont);
	
	if(container) {
	
		container.innerHTML='<img src="/admin/images/hprgs.gif" />';
		JsHttpRequest.query(
	        handler, // backend
	        {
	            'force': mthd
	        },
	         
	        function(result, errors) {
	        
	            if (result['done']==1 && result['data'] != 0) {
	                
	                var sel = document.createElement('SELECT');
	                sel.setAttribute('name',f_name);
	                sel.setAttribute('id',f_name);
	                
	                sel.options.length = 0;
	                
	                for(var i=0; i<result['data'].length; i++) {
	                
	                	var selected=false;
	                	if(result['data'][i].id == sid)
	                		selected=true;
	                	sel.options[i] = new Option(result['data'][i].p_name,result['data'][i].id,false,selected);
					}
					
					container.innerHTML='';
					container.appendChild(sel);
	            }
	            else
	            	container.innerHTML='ошибка при получении данных';
	        },
	            true
	    );
	}
}

function show_help(eid) {

	var help_holder = document.getElementById('hContent');
	var to_show = document.getElementById(eid);
	
	if(help_holder && to_show) {
	
		var items = help_holder.getElementsByTagName('LI');
		
		if(items) {
		
			for(var i=0; i<items.length; i++) {
			
				//alert(items[i].className);
				if(items[i].className == 'helpEl')
					items[i].style.display = 'none';
			}
		}
		
		to_show.style.display = 'block';
	}
}

function postForm(ed) {
	
	var editAreaId = ed.id;
	var frm = $('#'+editAreaId).parents('form');
	var sbut = frm.find('input[type="submit"]');
	if(sbut.length>0) {
		sbut.click();
		return true;
	}
	
	var content = ed.getContent(); //-> get the processed content
	content = content.replace(/\+/g, "&#43");
	content = content.replace(/\\/g, "&#92");
	
	var saving_id = $('#saving_id').val();
	
	if(typeof(content) != 'undefined') {
		
		wait();
		JsHttpRequest.query(
		    frm.attr('action'),
		    {
		        'save_content': saving_id,
		        'content': content
		    },
		     
		    function(result) {
		    
		        if (result['updated']==1) {
		            
	                show_save_alert('Данные сохранены.');
	                if(result['new_id'])
	                	edited_id = result['new_id'];
		        }
		        else {
		        	
		        	show_save_alert('Данных для сохранения нет.');
		        }
		    },
		        true
		);
	}
	
}


function abs(num) {
	
	var rnum=0;
	if(!isNaN(num)) {
		rnum = Math.abs(num);
	}
	return rnum;
}

function force_int(inObj) {
	var obj = inObj;
	var vl = parseInt(obj.val(),10);
	if(obj.val() != '') {
		obj.val(abs(vl));
	}
}

function force_float(inObj) {
	var obj = inObj;
	var vl = abs(parseFloat(obj.val().replace(/,/,'.')));
	obj.val(vl);
	return vl;
}

/**
 *
 * @param {jQuery} $
 * @param {Object} obj
 * @returns {{top: *, left: Number}}
 */
function toScreenCenter($, obj) {

	var area = $(window);
	var offsetTop = parseInt((area.height() - obj.height()) / 2) + area.scrollTop();
	var offsetLeft = parseInt((area.width() - obj.width()) / 2);

	$(obj).css({top:offsetTop+'px', left:offsetLeft+'px'});

	return obj;
}

function dbg(vr) {
    console.log(vr);
}

function arrayUnique(value, index, self) {
    return self.indexOf(value) === index;
}

function isObject(o) { return Object.prototype.toString.call(o).toLowerCase() === '[object object]'; }
function isArray(o) { return Object.prototype.toString.call(o).toLowerCase() === '[object array]'; }
function isBoolean(o) { return Object.prototype.toString.call(o).toLowerCase() === '[object boolean]'; }
function isNumber(o) { return Object.prototype.toString.call(o).toLowerCase() === '[object number]'; }
function isString(o) { return Object.prototype.toString.call(o).toLowerCase() === '[object string]'; }
function isNull(o) { return Object.prototype.toString.call(o).toLowerCase() === '[object null]'; }
function isFunction(o) { return Object.prototype.toString.call(o).toLowerCase() === '[object function]'; }
function isInt(n){ return Number(n) === n && n % 1 === 0; }
function isFloat(n){ return Number(n) === n && n % 1 !== 0; }
function isSingle(o) { return !(isArray(o) || isObject(o) || isFunction(o)); }
function isComplex(o) { return (isArray(o) || isObject(o)); }
function toNumType(o) { var t = Number(o); return isNaN(t)?o:t; }
function isNumeric(o) { return !isNaN(Number(o)); }
function getType(o) { return Object.prototype.toString.call(o).toLowerCase().split(' ')[1].replace(/[^\w]*/g, ''); }

(function($) {

    $.fn.dynamicSection = function(opts) {

        opts = opts || {};
        opts = $.extend(
            {
                onremove:false
            }
            , opts
        );

        return this.each(function(ind, elm) {

            elm = $(elm);

            var closeBtn = $('<span class="fa fa-btn fa-close attention"></span>');
            closeBtn.bind('click', function(e) {

            	if(isFunction(opts.onremove)) {

            		opts.onremove.call(elm);
				}
                elm.remove();
            });
            elm.append(closeBtn);
        });
    }

})(jQuery);
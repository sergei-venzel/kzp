<?php

function smarty_function_html_list_top_menu($params, &$smarty) {
	
	if(!isset($params['data'])) {
	
		$smarty->trigger_error("html_list: missing 'data' parametr");
		return;
	}
	
	if(!is_array($params['data'])) {
	
		$smarty->trigger_error("html_list: parametr 'data' must be Array");
		return;
	}
	
	require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
    

    !empty($params['class']) ? $list_class = $params['class'] : $list_class = '';
	
	$html_result = '';
	smarty_function_html_list_top_menu_child($html_result,$params['data'],$list_class);
	
	return $html_result;
}

function smarty_function_html_list_top_menu_child(&$html,$li_arr,$list_class='') {

	 if(is_array($li_arr) && !empty($li_arr)){
	
         if ($list_class !='')
            $ul_class = ' class="'.$list_class.'"';
         else
	        $ul_class = $li_arr['class'] ? 'class="'.$li_arr['class'].'"' : '';
            
		$ul_id = $li_arr['id'] ? 'id="'.$li_arr['id'].'"' : '';
		
		$html .='<ul '.$ul_class.' '.$ul_id.'>';
		foreach($li_arr as $val) {
			
			
			if(is_array($val)) {
			
				$li_events = $val['events'] ? $val['events'] : '';
				$li_class = $val['class'] ? 'class="'.$val['class'].'"' : '';
				$html .='<li '.$li_events.' '.$li_class.'>';
				$html .= $val['value'];
			}
			if(isset($val['childs']) && is_array($val['childs']) && !empty($val['childs'])) {
				smarty_function_html_list_top_menu_child($html,$val['childs']);
			}
			if(is_array($val))
				$html .='</li>';
		}
		$html .='</ul>';
	}
}

?>
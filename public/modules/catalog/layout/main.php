<?php defined('SYSPATH') or die('Out of');
error_reporting(E_ALL);

if(isset($_GET['gallery'])) $proc_cat = (int)$_GET['gallery'];
else $proc_cat=0;

$display_main_picture = $proc_cat==0 ? TRUE : FALSE;

if(isset($_GET['item'])) $act_item = (int)$_GET['item'];
else $act_item=0;

require_once('catalog/class.catalog.php');
$gallery = new gallery();

if(isset($_GET['add_to_basket']) AND isset($_SESSION['basket']['is_active'])/* AND $basket_active !== FALSE*/)
{
	$response = '';
	$response->err = '';
	
	
	
	if(!isset($_SESSION['basket']['order'][(int)$_GET['add_to_basket']]))
		$_SESSION['basket']['order'][(int)$_GET['add_to_basket']] = 1;
	
	
	//write_log(__LINE__,'cat_menu.txt');
	$result = $gallery->count_basket_items($_SESSION['basket']['order']);
	if($result !== FALSE)
	{
		$basket_sum = 0;
		foreach($result as $item)
		{
			$basket_sum += $item->{'sum_'.$item->id};
		}
        if($gallery->cur_factor > 0) {
            $basket_sum = $basket_sum * $gallery->cur_factor;
        }
		$_SESSION['basket']['total'] = $basket_sum;
		$response->total = number_format(floatval($basket_sum),1,'.',' ');
		$response->items = count($result);
	}
	else
		$response->err = 1;
	//write_log(__LINE__,'cat_menu.txt');
	//sleep(30);
	echo json_encode($response);
	exit;
}

$gallery_page_name='';

$showcase_items = FALSE;
$tpl->assign('catalog_page',$act_page_id);
if($proc_cat>=0 ) {
    
    if ($proc_cat>0) {
        $cat_res = $db->get_extreme_value($gallery->tree_table,'id,rid,cat_text,p_name,p_title,p_keywords,p_description,thumb,no_photos,p_bg','publish=1 and id=\''.$proc_cat.'\'','file: '.__FILE__.'line:'.__LINE__);
        
            
        if($cat_res) {
            $title             = $cat_res->p_title;
            $description       = $cat_res->p_description;
            $keywords          = $cat_res->p_keywords;
            $gallery_page_name = $cat_res->p_name;
            $tpl->assign('category_content', $cat_res->cat_text);
        }
        
        if($title == '') {
            $title = $cat_res->p_name;
        }
    }
    
    if($proc_cat==0 ) {
        $content_res = $db->get_extreme_value($page->table,'content','id=\''.$act_page_id.'\'');
        $tpl->assign('page_content',$content_res->content);
        
        if($showcase = $gallery->showcase_items())
        {
        	$elements = 3;
        	$showcase_items = array_chunk($showcase,$elements);
        	//
        	foreach($showcase_items as $key=>$val)
        	{
        		$cnt = count($val);
        		if($cnt<$elements)
        		{
        			for($i=0;$i<($elements-$cnt);$i++)
        				$showcase_items[$key][] = FALSE;
				}
			}
			
			//print_r($showcase_items);
		}
        $tpl->assign('std','/'.$gallery->photo_dir_pref);
        $tpl->assign('th',$gallery->thumb_dir.'/');
        $tpl->assign('showcase',$showcase_items);
    }

}

if ($act_item > 0) {

    $single = $db->select_obj($gallery->photo_table, 'id,item_name,photo,up_files,spec,short,c_gal,publish,price,quantity,keywords,title,meta_description', 'publish=1 and id=\'' . $act_item . '\'', 'file: ' . __FILE__ . 'line:' . __LINE__);

    if ($single) {
        $title            = ! empty($single[0]->title) ? $single[0]->title : $single[0]->item_name;
        $cat_res->p_name  = $single[0]->item_name;
        $cat_res->p_title = $single[0]->item_name;
        if ( ! empty($single[0]->keywords)) {
            $keywords = $single[0]->keywords;
        }
        if ( ! empty($single[0]->meta_description)) {
            $description = $single[0]->meta_description;
        }

        $gallery_page_name = $single[0]->item_name;
    }
}

if ($gallery_page_name == '') {
    $tpl->assign('head_line', false);
}
else {
    $tpl->assign('head_line', $gallery_page_name);
}

$page_html='';

$tpl->assign('scripts',array('/js/site/highslide.packed.js', '/js/site/jquery.cycle.all.js'));

include(PUBPATH . 'header.php');



                        // CATALOG CONTENT

if ($act_item > 0) {


    $tpl->assign('thumbs', '/' . $gallery->photo_dir_pref . $proc_cat . $gallery->thumb_dir . '/');
    $tpl->assign('big', '/' . $gallery->photo_dir_pref . $proc_cat . '/');
    $item_data = false;
    if (isset($single[0])) {
        $item_data        = $single[0];
        $item_data->price = number_format(floatval($item_data->price), 1, '.', ' ');
        if ($gallery->cur_factor > 0) {
            $item_data->ruprice = number_format(floatval($item_data->price * $gallery->cur_factor), 1, '.', ' ');
        }
    }
    $cat_nav = array();
    $gallery->get_site_navigation($cat_nav, array(), 0, 0, $page_info);

    $tpl->assign('catalog_menu', $cat_nav);
    $tpl->assign('item', $item_data);
    $page_html .= $tpl->fetch('catalog_item.tpl');


}
elseif($proc_cat>=0) {
    
    
    if(isset($_GET['page']))
    	$page_show = (int)$_GET['page'];
    else
    	$page_show = 1;
    
    $items_list = $gallery->list_cat_items($proc_cat,$basket_active,(($page_show-1)*catalog_portion));
    //logger($items_list['products']);
    $tpl->assign('gallery_page_name',$gallery_page_name);
    //$tpl->assign('list_gallery',$blocks_gallery);
    $tpl->assign('thumbs','/'.$gallery->photo_dir_pref.$proc_cat.$gallery->thumb_dir.'/');
    $tpl->assign('big','/'.$gallery->photo_dir_pref.$proc_cat.'/');
    $tpl->assign('root_category',$gallery->get_root_category($proc_cat));
    $tpl->assign('items_list',$items_list['products']);
    $pagination = FALSE;
    if((int)catalog_portion > 0) {
    
    	$pagination = array();
    	$page_cnt = ceil($items_list['cnt']/catalog_portion);
    	for($i=1; $i<=$page_cnt;$i++)
    		$pagination[] = $i;
	}
    
    $tpl->assign('page_show',$page_show);
    $tpl->assign('display_main_picture',$display_main_picture);
    $tpl->assign('pagination',$pagination);
    $cat_nav = array();
	$gallery->get_site_navigation($cat_nav,array(),0,0,$page_info);
	
	$tpl->assign('catalog_menu',$cat_nav);
    
    $page_html .= $tpl->fetch('catalog_category.tpl');
    
}

include(PUBPATH . 'footer.php');

// CACHING
echo $page_html;

store_cache($page_html,$_SERVER['QUERY_STRING'],isset($_GET['preview'])?$_GET['preview']:false);


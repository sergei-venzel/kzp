<?php
if(!defined('base')) {
	header('Location: /');
	exit;
}
$tpl->assign('div_room_class','common');
$tpl->assign('main_class','page');
//$tpl->assign('scripts', array('/js/site/jquery.cycle.lite.js'));

include(PUBPATH . 'header.php');
session_write_close();

$tpl->assign('head_line',$page_content->p_name);
$content_res = $db->get_extreme_value($page->table,'content','id=\''.$act_page_id.'\'');
$tpl->assign('page_content',$content_res->content);


require_once($relative.'lib/class.image.php');
$image = new image();


require_once($relative.'lib/class.modules.php');
$module = new modules();

$price_list = array();

$tpl->assign('price_list',$price_list);

$announce_news = array();
if ($module->is_active_module('news')) {
    
    require_once('news/class.news.php');

    $display_news = new news();

    $tbl = $display_news->main_table;
    $display_news->load_settings();
    
    $announce_portion = constant(get_class($display_news).'_announce_portion');

    $from=0;

    if ($display_news->list_news($from,$announce_portion)){
        $announce_news = $display_news->list_news($from,$announce_portion);
    }
}
    
$tpl->assign('announce_news',$announce_news);

$page_html .= $tpl->fetch('home.tpl');

include($relative.'footer.php');

echo $page_html;

store_cache($page_html,$_SERVER['QUERY_STRING'],$_GET['preview']);
?>

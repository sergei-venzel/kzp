<?php defined('SYSPATH') or die('Out of');


$tpl->assign('div_room_class','common');
$tpl->assign('main_class','page');

include(PUBPATH . 'header.php');

$tpl->assign('head_line',$page_content->p_name);
$content_res = $db->get_extreme_value($page->table,'content','id=\''.$act_page_id.'\'');
$tpl->assign('page_content',$content_res->content);


require_once('class.image.php');
$image = new image();


require_once('class.modules.php');
$module = new modules();

require_once('catalog/class.catalog.php');
$gallery = new gallery();

$section_menu_id = 0;

if (isset($_GET['smi'])) {
    $section_menu_id = (int) $_GET['smi'];
}

$grouped_cats = $gallery->groupedCategoies($page_info->proc_cat, $section_menu_id);
$tpl->assign('sections_navigation', $grouped_cats);

$page_html .= $tpl->fetch('default.tpl');

include(PUBPATH . 'footer.php');

echo $page_html;

store_cache($page_html,$_SERVER['QUERY_STRING'],isset($_GET['preview']));

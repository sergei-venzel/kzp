<?php
if(!defined('SYSPATH')) {
	header('Location: /');
	exit();
}

session_write_close();

require_once('class.modules.php');
$module = new modules();

$title = 'Результаты поиска';
$keywords = '';
$description = '';

include(PUBPATH . 'header.php');

echo $page_html;

$bred_arr = $page->bred_crumb($act_page_id);

if(!empty($bred_arr)) {
    $bred_arr = array_reverse($bred_arr);
}

$tpl->assign('breadcrumb',$bred_arr);
$tpl->assign('head_line','Результаты поиска');

require_once('search/class.search.php');

$s_results = new search();

$tpl->assign('wanted',$s_results->get_lower(trim(strip_tags(urldecode($s_results->quotes($_GET['ss']))))));

$block_results = $s_results->get_modules_searching($_GET['ss']);

$tpl->assign('blocks',$block_results);

$tpl->display('search_results.tpl');

$page_html='';
include(PUBPATH . 'footer.php');
echo $page_html;


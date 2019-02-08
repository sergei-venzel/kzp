<?php
$title       = ! empty($title) ? $title : $_SERVER['HTTP_HOST'];
$header_data = array('keywords' => $keywords, 'description' => $description, 'title' => $title);
$tpl->assign('header_data', $header_data);
$tpl->assign('top_active', $top_id);


if (isset($_GET['gallery'])) {
	$proc_cat = (int) $_GET['gallery'];
}
else {
	$proc_cat = 0;
}


$page_info              = new stdClass();
$page_info->act_page_id = $act_page_id;
$page_info->top_id      = $top_id;
$page_info->proc_cat    = $proc_cat;

$top_menu = array();
if ($all_top_page = $page->get_top_menu_navigation()) {
	foreach ($all_top_page as $val) {

		if ($val->external == 1) {
			$page_link = 'href="http://' . ($val->ext_link != '' ? htmlspecialchars($val->ext_link) : $_SERVER['HTTP_HOST']) . '" target="_blank" title="Страница откроется в новом окне"';
		}
		else {
			$page_link = 'href="/?set=' . $val->id . '"';
		}

		$top_menu[$val->id] = '<a ' . $page_link . '>' . $val->p_name . '</a>';
	}
}


require_once('class.modules.php');
$module = new modules();

$basket_link   = false;
$basket_active = false;

if ($module->is_active_module('orders') AND $module->is_active_module('catalog')) {

    session_id();
    session_start();

    $_SESSION['basket']['is_active'] = true;

    require_once('orders/class.orders.php');
    $orders = new orders();

    if ($orders->modules_page_id) {
        $basket_link   = '?set=' . $orders->modules_page_id;
        $basket_active = true;
    }

}

$basket_items = 0;
$basket_sum   = 0;

if (isset($_SESSION['basket']['order'])) {

    $basket_items = count($_SESSION['basket']['order']);
	$basket_sum   = number_format(floatval($_SESSION['basket']['total']), 1, '.', ' ');

    session_write_close();
}

$tpl->assign('basket_items', $basket_items);
$tpl->assign('basket_sum', $basket_sum);
$tpl->assign('basket_link', $basket_link);
$tpl->assign('basket_active', $basket_active);


$tpl->assign('bradcrumb', $page->bradcrumb($act_page_id));
$tpl->assign('top_pages', $page->main_pages_list($top_id));
$tpl->assign('sub_pages', $page->childs_pages_list($act_page_id));
$tpl->assign('top_menu', $top_menu);

if ($module->is_active_module('zones')) {
	require_once('zones/class.zones.php');
	$zones = new visitorzones();

	$tpl->assign('foot_zone', $zones->get_content('foot'));
	$tpl->assign('left_zone', $zones->get_content('side_left'));
	//$tpl->assign('left_zone',$zones->get_content('side_right'));
	//logger($zones->get_content('top'),'dbg.inc');
}

$page_html = $tpl->fetch('header.tpl');

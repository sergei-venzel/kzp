<?php

if ( ! defined('SYSPATH')) {
    header('Location: /');
    exit();
}
$tpl->assign('div_room_class', 'common');
$tpl->assign('main_class', 'page');

include(PUBPATH . 'header.php');

session_write_close();

require_once('class.modules.php');
$module = new modules();

$price_list = array();

$announce_news = array();

$tpl->assign('announce_news', $announce_news);

$bred_arr = $page->bred_crumb($act_page_id);

if ( ! empty($bred_arr)) {
    $bred_arr = array_reverse($bred_arr);
}

$tpl->assign('breadcrumb', $bred_arr);
$tpl->assign('head_line', $page_content->p_name);

$site_links = array();

$site_links[0]['title'] = 'Страницы сайта';
$site_links[0]['pages'] = array();
$page->site_map($site_links[0]['pages']);

$temp = $page->alone_pages();
if ( ! empty($temp)) {
    $site_links[1]['title'] = 'Отдельные страницы';
    $site_links[1]['pages'] = $temp;
}


require_once('class.modules.php');
$module = new modules();


// Collect modules site maps
$mods = $db->select_obj($page->table, 'DISTINCT layout', 'layout != \'common\'');

if ($mods) {

    foreach ($mods as $mname) {

        $required_file = $mname->layout . '/class.' . $mname->layout . '.php';
        @require_once($required_file);

        $tmp = new $mname->layout;

        if ($module->is_active_module($mname->layout)) {

            if (method_exists($tmp, 'module_site_map')) {

                $site_links[] = $tmp->module_site_map();
            }
        }
    }
}
// END

$tpl->assign('site_links', $site_links);

$page_html .= $tpl->fetch('site_map.tpl');

include(PUBPATH . 'footer.php');

echo $page_html;

store_cache($page_html, $_SERVER['QUERY_STRING'], $_GET['preview']);

?>
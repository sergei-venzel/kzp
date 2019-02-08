<?php defined('SYSPATH') OR die('No direct access alowed');

session_write_close();

require_once('news/class.news.php');

$acting_module = new news_site();
$news_from     = 0;
if (isset($_GET[ $acting_module->get_portion_prefix ])) {

    $news_from = (int) $_GET[ $acting_module->get_portion_prefix ];
}

$single_item   = false;
$sectionId = false;
if(isset($_GET['section'])) {
    $sectionId = (int)$_GET['section'];
    if($sectionId <= 0) {
        $sectionId = false;
    }
}
$announce_list = $acting_module->news_portion( $news_from, $sectionId );

if ($main_url_set) {
    $base_uri = $main_url_set;
} else {
    $base_uri = '/?';
    if (isset($_GET['set']) AND ! isset($_GET['top'])) {

        $base_uri .= 'set=' . (int) $_GET['set'];
    } elseif ( ! isset($_GET['set'])) {
        $base_uri .= 'set=' . $act_page_id;
    } else {
        $base_uri .= 'top=' . (int) $_GET['top'] . '&set=' . (int) $_GET['set'];
    }
}

if (isset($_GET['news_item'])) {
    $single_item = $acting_module->get_item( $_GET['news_item'], true );
}

if ( ! $single_item) {
    $tpl->assign('paginate', $acting_module->paginate($base_uri, $sectionId));
    $headline = $page_content->p_name;
}
else {
    $headline    = $single_item->title;
    $title       = ! empty($single_item->meta_title) ? $single_item->meta_title : $single_item->title;
    $keywords    = $single_item->keywords;
    $description = $single_item->description;
}

$tpl->assign( 'head_line', $headline );
$tpl->assign( 'scripts', array('/js/site/highslide.packed.js', '/js/site/jquery.cycle.all.js') );
$tpl->assign( 'styles', array('news_visitor.css?v=1.0.1') );
$module_styles = array('news_visitor.css');

include(PUBPATH . 'header.php');

$tpl->template_dir = 'modules/news/';
$tpl->assign( 'newses', $announce_list );
$tpl->assign('newsSections', $acting_module->getSections());

$tpl->assign( 'base_uri', $base_uri );

if ($single_item) {
    $tpl->assign( 'single', $single_item );
    $page_html .= $tpl->fetch( 'news_single.tpl' );
} else {
    $page_html .= $tpl->fetch( 'news_list.tpl' );
}

include(PUBPATH . 'footer.php');
echo $page_html;

store_cache( $page_html, '', isset($_GET['preview']) ? true : false );
?>
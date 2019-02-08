<?php
error_reporting(E_ALL);

if(file_exists('install.php')) {

//    header('Content-Type: text/html; charset=UTF-8');
    require_once('install.php');
    if(!defined('installed')) {
        header('Location: install/');
        exit();
    }
}
else {
    header('Location: install/');
    exit();
}

require_once('config.php');

$relative = RELATIVE_PATH;

$stored_file = cache_file_name($_SERVER['QUERY_STRING']);

if(defined('caching') && caching==1 && file_exists($stored_file) && (time() - filemtime($stored_file) < cache_time)) {
    
    include($stored_file);
    //echo 'included from cache';
    exit();
}

/**
 * @var db $db
 */
$db = Registry::getInstance()->get('db');

require_once('class.pages.php');
require_once('pageset.php');

$page = new pageset();

if (isset($_GET['set']) && abs( (int) $_GET['set'] ) > 0) {
    $act_page_id = abs( (int) $_GET['set'] );
} else {
    $act_page_id = $page->default_page;
}
    

$inc_file = null;

if(isset($_GET['page']) && $_GET['page']=='results') {
    
    $inc_file = $page->layout_script('search');
}
else {
    
    $page_content = $db->select_obj($page->table,'p_title,p_name,p_keywords,p_description,layout,bg',' id=\''.$act_page_id.'\' and publish=1','file: '.__FILE__.'line:'.__LINE__);
    
    if($page_content) {
        $page_content=$page_content[0];
        $inc_file = $page->layout_script($page_content->layout);
        $title = $page_content->{'p_title'};
        $description = $page_content->{'p_description'};
        $keywords = $page_content->{'p_keywords'};
    }
}


//echo $inc_file;
if (isset($_GET['top'])) {
    $top_id = (int) $_GET['top'];
} elseif (isset($_GET['set'])) {
    $top_id = (int) $_GET['set'];
} else {
    $top_id = $act_page_id;
}

$actual_theme = 'default';
require_once('Smarty.class.php');
$tpl = new Smarty();

require_once('actual.php');

$tpl->template_dir = 'templates/'.$actual_theme;
$tpl->assign('theme_path','templates/'.$actual_theme);

if(file_exists($inc_file)) {

	include($inc_file);
}
else {

    include('404.php');
}

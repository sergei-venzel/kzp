<?php
// Auth LAYER
error_reporting(E_ALL);
session_id();
session_start();

require_once(dirname(dirname(dirname(__DIR__))) . '/config.php');

if(!is_ajax()) {

    header('Location: /');
    exit();
}

$answer = '';

$relative = RELATIVE_PATH;

if(!defined('installed')) {

    session_destroy();
    resumeAjax(array('redirect' => '/install/'), 'error');
}

if(isset($_GET['action']) && $_GET['action']=='logout') unset($_SESSION['user']);

if(!isset($_SESSION['user']) || $_SESSION['user'] != 'admin') {
    session_destroy();
    resumeAjax(array('redirect' => '/admin/auth.php'), 'error');
}

$action = '';
if(isset($_REQUEST['action'])) {
    $action = trim(strip_tags($_REQUEST['action']));
}

$db = Registry::getInstance()->get('db');
require_once('class.view.php');
require_once('catalog/class.catalog.php');

switch($action) {

    case 'product_links':

        $catalog = new catalog();
        $catalog->load_settings();
        $gallery = new gallery();

        $answer = $gallery->commonProductsData();

        break;
}

resumeAjax($answer);
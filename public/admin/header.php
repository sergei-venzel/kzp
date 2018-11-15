<?php defined('SYSPATH') or die('Out of');
error_reporting(E_ALL);

require_once('actual.php');

if(isset($actual_theme))
    define('actual_theme', $actual_theme);
else
    define('actual_theme','default');


if(isset($_GET['page']) && (int)$_GET['page'] > 0) $_SESSION['page']=(int)$_GET['page'];

$acting_page = isset($_SESSION['page']) ? $_SESSION['page'] : false;

//if(!$page) $page=1;
//echo RELATIVE_PATH;
require_once('class.admin_menu.php');

$menu = new admin_menu();

if(!isset($page_title)) {
    if(isset($menu->menu_items[$acting_page])) {
        $page_title = $menu->menu_items[$acting_page]['title'];
    }
    else {
        $page_title = '';
    }
}

// Pages variables
if(!isset($form_action)) $form_action=$_SERVER['PHP_SELF'];
if(!$page_title) $page_title='';
else $page_title=' :: '.$page_title;
// Pages variables

?><!DOCTYPE HTML>
<html>
<head>
    <title>Site admin<?=$page_title;?></title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <link rel="stylesheet" type="text/css" href="/admin/style.css?v=1.0.1" />
    <?php
    if(isset($add_styles) && !empty($add_styles)) {

        foreach($add_styles as $inc_style) {

            ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $inc_style;?>" />
            <?php
        }
    }
    ?>
    <link rel="stylesheet" type="text/css" href="/templates/<?=actual_theme?>/preview.css" />
    <?php

    if(isset($fuck)) {
        ?>
        <script src="/js/jquery-latest.js"></script>
        <?php
    }
    else {
        ?>
        <script src="/js/site/jquery.js"></script>
        <?php
    }
        ?>

	<script src="/js/ui.core.js"></script>
    <script src="/js/ui.sortable.js"></script>
    <script src="/js/ui.resizable.js"></script>
    <script src="/js/ui.draggable.js"></script>
    <script src="/js/ui.dialog.js"></script>
    <script src="/js/colorpicker.js"></script>
    <script language="JavaScript" src="/js/funcs.js?v=1.0.2"></script>
    <?php
    if(isset($add_scripts) && !empty($add_scripts)) {

        foreach($add_scripts as $inc_script) {

            ?>
            <script src="<?php echo $inc_script;?>"></script>
            <?php
        }
    }
    ?>
    <script language="JavaScript" src="/js/JsHttpRequest.js"></script>
</head>
<body>

<script language="JavaScript">
<!--
var timerID = null;

$(document).ready(function(){
    startclock();
  });

function non_stop() {
    
    var signal = document.getElementById('mon');
    if(signal) {
        
        signal.style.display='block';
        signal.innerHTML = (new Date()).toLocaleTimeString();
        
        JsHttpRequest.query(
	        '<?=RELATIVE_PATH?>admin/nonstop.php', // backend
	        {
	            'usual': '<?=session_id();?>'
	        },
	        // Function is called when an answer arrives. 
	        function(result) {
	        
	            if (result['answer']==1) {
	                
	                if(signal)
	                    signal.style.display='none';
	                    signal.innerHTML = '&nbsp;';
	            }
	        },
	            true
	    );
	    return true;
    }
}


function top_clean(monit,holder) {
    
    var fname='all';
    
    document.getElementById(monit).style.display='inline';
    JsHttpRequest.query(
        '<?=RELATIVE_PATH?>admin/cleaner.php', // backend
        {
            'to_clean': fname
        },
        
        function(result) {
        
            if (result) {
                document.getElementById(holder).style.display = result["but_disp"];
            }
        },
            false  // do not disable caching
    );
    return true;
}
//-->
</script>
<div>
<table cellpadding="0" cellspacing="0" class="container">
        <?
        if(!isset($hide_left_menu)) {
        
        	echo '<td class="menu">';
        	echo $menu->build_menu($acting_page);
        	echo '</td>';
		}
        ?>
    <td class="cont">
    <div class="main">

<?
if(isset($_GET['saving_process']) && $_GET['saving_process']=='success') {
    ?>
<script>
$(document).ready(function(){
    wait();
	show_save_alert('Данные сохранены.');
  });
</script>
    <?
}
?>
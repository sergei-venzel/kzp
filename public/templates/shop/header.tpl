<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="{$header_data.description}" />
<meta name="keywords" content="{$header_data.keywords}" />
<title>{$header_data.title}</title>
<link rel="stylesheet" type="text/css" href="{$theme_path}/style.css?v=1.6.0" />
<!--[if IE 6]> <link href="{$theme_path}/style_ie6.css" rel="stylesheet" type="text/css"> <![endif]-->
<link rel="stylesheet" type="text/css" href="{$theme_path}/highslide.css" />
<link rel="stylesheet" type="text/css" media="print" href="{$theme_path}/print.css" />
<!--<link rel="shortcut icon" type="/testimage/ico" href="/favicon.ico" />-->
{if $styles}
    {foreach from=$styles item=ssrc}
        <link rel="stylesheet" type="text/css" href="{$theme_path}/{$ssrc}" />
    {/foreach}
{/if}
{*<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />*}



    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=BGBOxB9r6v">
    <link rel="icon" type="image/png" href="/favicon-32x32.png?v=BGBOxB9r6v" sizes="32x32">
    <link rel="icon" type="image/png" href="/favicon-16x16.png?v=BGBOxB9r6v" sizes="16x16">
    <link rel="manifest" href="/manifest.json?v=BGBOxB9r6v">
    <link rel="mask-icon" href="/safari-pinned-tab.svg?v=BGBOxB9r6v" color="#5bbad5">
    <link rel="shortcut icon" href="/favicon.ico?v=BGBOxB9r6v">
    <meta name="apple-mobile-web-app-title" content="Kachay-Zhelezo">
    <meta name="application-name" content="Kachay-Zhelezo">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png?v=BGBOxB9r6v">
    <meta name="theme-color" content="#ffffff">



<meta name="google-site-verification" content="h5BOqqOJapai5E3VNzah_ILzuZ7XXhA-OjzTmCXdNz8" />
<script type="text/javascript" language="JavaScript" src="/js/site/jquery-1.11.2.min.js"></script>
<script type="text/javascript" language="JavaScript" src="/js/site/jquery-ui.min.js"></script>
<script type="text/javascript" language="JavaScript" src="/js/site/jquery.form.js"></script>
{if $scripts}
{foreach from=$scripts item=src}
<script type="text/javascript" src="{$src}"></script>
{/foreach}
{/if}
<script language="JavaScript">
$.getScript('/js/site/common.js');
</script>
</head>
<body>

<div class="fix_main noprint">
    
    <div class="top_block">
        
        <div class="left_top_block">
            <a href="/" title="Купить стероиды, анаболики, заказать анаболические стероиды в интернет-магазине"><img src="{$theme_path}/images/logo.gif" alt="logo" title="Купить стероиды, анаболики, заказать анаболические стероиды в интернет-магазине" /></a>
        </div>
        <div class="boxed header-titles">
            <div class="site-title boxed">Kachay-Zhelezo.biz</div>
            <h2 class="site-title-2 boxed">ИНТЕРНЕТ-МАГАЗИН СТЕРОИДОВ</h2>
        </div>
        <div class="right_top_block">
            
            {if $top_menu}
            {common_list data=$top_menu class="top_menu" flag=$top_active active="current"}
            {else}
            <div class="top_menu">&nbsp;</div>
            {/if}
            
            <div class="search_block">
                <form method="get" action="" name="search">
                    <input type="hidden" value="results" name="page" />
                    <table class="search">
                        <tr>
                            <td><input type="text" id="s_str" class="search_box boxed" value="" name="ss" placeholder="поиск" /></td>
                            <td><input type="submit" class="search_img boxed" value="" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        
        </div>
        
    </div>
    
    
   {if $top_pages}
    <div class="menu corner-all boxed">
        <div class="middle_block_menu boxed">
            {common_list data=$top_pages class="main_menu" flag=$top_active active=""}
        {*{html_table_high_level loop=$top_pages rows=1 table_attr='' end_td_class='border_img_none'}*}
        
        </div>
    </div>
    {/if}

    
</div>



{*{debug}*}

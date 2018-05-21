<?php
error_reporting( 0 );
session_id();
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

require_once(RELATIVE_PATH . 'themes/actual.php');

if (isset($actual_theme)) {
    define( 'actual_theme', $actual_theme );
} else {
    define( 'actual_theme', 'default' );
}

$relative = RELATIVE_PATH;

if ( ! defined( 'installed' )) {

    session_destroy();
    header( 'Location: ' . $relative . 'install/' );
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['user']);
}

if ( ! isset($_SESSION['user']) || $_SESSION['user'] != 'admin') {
    session_destroy();
    header( 'Location: ' . $relative . 'admin/auth.php' );
    exit();
}
// Auth LAYER


if ($_GET['page']) {
    $page_id = (int) $_GET['page'];
} elseif ($_POST['page']) {
    $page_id = (int) $_POST['page'];
} else {
    $page_id = 0;
}

require_once('../lib/class.html.php');
$html = new html();

require_once('../lib/class.db.php');
$db = new db();

require_once('../lib/class.pages.php');
$pages    = new pages();
$db_table = $pages->table;

$loc_script = 'panel.php';

$save_process = false;

require_once($relative . 'js/JsHttpRequest.php');
$JsHttpRequest = new JsHttpRequest( "windows-1251" );

if (isset($_REQUEST['save_content'])) {

    $res = 0;
    $data = '';
    if (((strip_tags( $html->img_to_swf( $_REQUEST['content'] ), '<img>' )) != '') && (strlen( strip_tags( $html->img_to_swf( $_REQUEST['content'] ), '<img>' ) ) > 7)) {
        $data->{'panel_content'} = str_replace( array("\t"), '', $html->img_to_swf( $_REQUEST['content'] ) );
    } else {
        $data->{'panel_content'} = '';
    }


    if ($db->update_obj( $db_table, $data, ' id=\'' . $page_id . '\'' )) {
        $res = 1;
    }

    $GLOBALS['_RESULT'] = array('updated' => $res);
    exit();
}


$html_main_threat = '';
$html_viruslist = '';

if($_GET['parser']) {

    $url='http://avp.ru/';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    //                set_time_limit(60);

    $txt = '';

    if (curl_exec($ch)) {

        $txt = curl_exec($ch);
        curl_close($ch);

        $pattern = '/<td[^>]*g_06_home[^>]*>(?:\r*\n*)*<span[^>]*208150421[^>]*>(.*?)<\/span>\r*\n*<\/td>(?:\r*\n*)*<td[^>]*207367338[^>]*>/sim';
        $rez = array();
        preg_match($pattern, $txt, $rez);
        
        if (!empty($rez)) {
        
            $rez = $rez[1];
            
            $pattern = '/<!--.*?-->/sim';
            $rez2 = array();
            $rez2=preg_split($pattern,$rez,-1,PREG_SPLIT_NO_EMPTY);
            
            $txt='';
            foreach ($rez2 as $val) {
                $txt.=$val;
            }
            
            $pattern = '/<table[^>]*>(.*?)<table[^>]*>(.*?)<\/table>(.*?)<\/table>/sim';
            $rez_threat_one=array();
            preg_match($pattern, $txt, $rez_threat_one);
            
            if (!empty($rez_threat_one)) $rez_threat_one = $rez_threat_one[0];
            else $rez_threat_one = '';
            
            $pattern = '/<td[^>]*>(.*?)<\/td>/sim';
            $rez=array();
            preg_match_all($pattern,$rez_threat_one,$rez);
            
            $rez_threat_one = '';
            if (!empty($rez)) {
                
                $threat_one_content = '';
                
                $rez=$rez[1];
                foreach ($rez as $val) {
                    if (strpos($val,'<h2'))
                        $rez_threat_one.=$val;
                }
                
                $pattern='/<h2[^>]*>(.*?)<\/h2>/sim';
                $rez=array();
                preg_match($pattern,$rez_threat_one,$rez);
                
                if (!empty($rez)) $threat_one_content->h2 = $rez[1];
                
                $pattern='/<a[^>]*href="(.*?)"[^>]*>(.*?)<\/a>/sim';
                $rez=array();
                preg_match($pattern,$rez_threat_one,$rez);
                
                if (!empty($rez)) {
                   $threat_one_content->url = $rez[1];
                   $threat_one_content->url_text = $rez[2];
                }
                                
                $pattern='/<span[^>]*>(.*?)<\/span>/sim';
                $rez=array();
                preg_match_all($pattern,$rez_threat_one,$rez);
                
                if (!empty($rez)) $rez=$rez[1];
                
                if (isset($rez[0])) $threat_one_content->date = $rez[0];
                if (isset($rez[1])) $threat_one_content->text = $rez[1];
                
                
                $flag=0;
                if (!empty($threat_one_content->text)){
                    if (strpos($threat_one_content->text,'средняя')) $flag=1;
                }
                
                switch ($flag){
                    case 2:
                        break;
                    case 1:
                    default:
                        $threat_class = ' middle_threat';
                        $threat_img = '<img src="/images/orange.gif" alt="" title="" />';
                    
                }
                
                
                if ((!empty($threat_one_content->h2)) || (!empty($threat_one_content->h2))) {
                    
                    if (!empty($threat_one_content->url)) $threat_img = '<a href="'.$threat_one_content->url.'" alt="" title="">'.$threat_img.'</a>';
                                        
                    $html_main_threat .= '
                    <div class="main_threat">
                        <div class="top"><span class="top_right">&nbsp;</span></div>
                        <div class="text_threat'.$threat_class.'">
                            <div class="left_threat">
                                '.$threat_img.'
                            </div>
                            
                            <div class="right_threat">';
                            
                    if (!empty($threat_one_content->h2))
                        $html_main_threat .= '<h2>'.$threat_one_content->h2.'</h2>';
                    if (!empty($threat_one_content->date))
                        $html_main_threat .= '<span>13.01.09 20:32 MSK</span><br />';
                    if (!empty($threat_one_content->url_text)){
                        $tmp_html = $threat_one_content->url_text;
                        if (!empty($threat_one_content->url))
                            $tmp_html = '<a href="'.$threat_one_content->url.'" alt="" title="">'.$tmp_html.'</a>';
                        $html_main_threat .= $tmp_html.'<br />';
                    }
                        
                    if (!empty($threat_one_content->text))
                        $html_main_threat .= $threat_one_content->text;
                        
                    $html_main_threat .= '
                            </div>
                            <div class="line_clear">&nbsp;</div>
                        </div>
                        <div class="line_clear">&nbsp;</div>
                        <div class="bottom"><span class="bottom_left">&nbsp;</span><span class="bottom_right">&nbsp;</span></div>
                        <div class="line_clear">&nbsp;</div>
                    
                    </div>';
                }
                    
            }           
            
            
            $pattern = '/<div[^>]*>(?:\r*\n*)*<table[^>]*>(.*?)<\/table>(?:\r*\n*)*<\/div>/sim';
            $rez_viruslist=array();
            preg_match($pattern, $txt, $rez_viruslist);
            
            if (!empty($rez_viruslist)) $rez_viruslist = $rez_viruslist[0];
            else $rez_viruslist = '';
            
            $pattern = '/<td[^>]*>(.*?)<\/td>/sim';
            $viruslist=array();
            preg_match_all($pattern, $rez_viruslist, $viruslist);
            
            if (!empty($viruslist)) $viruslist = $viruslist[1];
            else $viruslist = '';
            
            if ($viruslist != '') {
                $html_viruslist .= '<h2>Последние вирусы</h2>
                ';
                
                if (count($viruslist)>1) {
                    $html_viruslist .= '<table class="main_list">';
                    for($i=0; $i<count($viruslist); $i+=2) {
                        $html_viruslist.='<tr>';
                        $html_viruslist.='<td>'.$viruslist[$i].'</td>';
                        $html_viruslist.='<td>'.str_replace('<wbr>','<span> </span>',$viruslist[$i+1]).'</td>';
                        $html_viruslist.='</tr>';
                    }
                    $html_viruslist .= '</table>';
                }
                
            }   
        }
        
    }
}
            
/*            echo $html_viruslist;*/


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

<div class="button"><a href="<?=$loc_script;?>?page=<?=$page_id;?>&parser=1">Получить информацию об угрозах с сайта http://avp.ru</a></div>
<div class="line_clear">&nbsp;</div>
    <?
    if($save_process) {
        echo '<script>
        ';
        echo 'window.opener.location=window.opener.location;';
        echo '
        </script>';
    }
    ?>
    <title>Панель угрозы</title>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    <link rel="stylesheet" type="text/css" href="/admin/style.css" />
    
    <script language="JavaScript" src="/js/funcs.js"></script>
    <script language="JavaScript" src="/js/JsHttpRequest.js"></script>
    
</head>
<body onload="window.focus();">

<?
if($page_id>0) {
    
    $cur_page = $db->select_obj($db_table,'id,rid,pid,p_name,panel_content','id=\''.$page_id.'\'','file: '.__FILE__.'line:'.__LINE__);
    if($cur_page) $cur_page=$cur_page[0];
    $sub_value='save_panel_content';
}


echo $html->wysiwyg_init($relative.'admin/',p_f_dir.$cur_page->rid);


?>

<input type="hidden" name="rid" value="<?=$cur_page->rid;?>" />
<input type="hidden" name="content_page" value="<?=$cur_page->id;?>" />

<?

if (!empty($cur_page->panel_content))
    $content=$cur_page->panel_content;
else 
    $content = '';
    
    if (($html_viruslist != '') || ($html_main_threat != '')){
        $content = '';
               
        if ($html_main_threat != '')
            $content .= $html_main_threat;
        
        $content .= '
            <h2><a href="http://www.viruslist.com/ru">Viruslist</a></h2>
            Актуальная информация <br>об интернет-угрозах
            <ul>
                <li class="quotation_marks"><a href="http://www.viruslist.com/ru/viruses/encyclopedia">Вирусная энциклопедия</a></li>
                <li class="quotation_marks"><a href="http://www.viruslist.com/ru/weblog">Веблог</a></li>
            </ul>
            <br />
            ';
        
        if ($html_viruslist != '')
            $content .= $html_viruslist;
            
        $content .= '
            <br />
            <h2><a href="http://www.spamtest.ru/">Спамтест</a></h2>
            Все о спаме
            <ul>
                <li class="quotation_marks"><a href="http://www.spamtest.ru/document">Последние публикации</a></li>
            </ul>
            <br />
            ';
    }
        
?>


<div>
<textarea name="content" style="width: 730px; height: 400px;" id="t_content"><?=$content;?></textarea>
</div>
<div>
<input type="button" name="save_content" value="Сохранить" onclick="update_from_tiny('t_content','<?=$loc_script;?>?page=<?=$page_id;?>','<?=$page_id;?>');return false;" />&nbsp;
<input type="button" value="Назад" onclick="window.opener.location; self.close();" />
</div>

</body>
</html>

<?php
$db->db_close($db->db_conn);
?>
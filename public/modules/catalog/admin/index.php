<?php
// Auth LAYER
error_reporting(E_ALL);
session_id();
session_start();

require_once('../../../config.php');

$relative = RELATIVE_PATH;

if(!defined('installed')) {
    
    session_destroy();
    header('Location: '.$relative.'install/');
    exit();
}

if(isset($_GET['action']) && $_GET['action']=='logout') unset($_SESSION['user']);

if(!isset($_SESSION['user']) || !isset($_SESSION['user']) || $_SESSION['user'] != 'admin') {
    session_destroy();
    header('Location: '.$relative.'admin/auth.php');
    exit();
}
// Auth LAYER

/**
 * @var db $db
 */
$db = Registry::getInstance()->get('db');

require_once('class.html.php');
$html = new html();
require_once('class.image.php');
$image = new image();


require_once('catalog/class.catalog.php');
$catalog = new catalog();

$catalog->load_settings();
//logger($catalog->cur_factor,'dbg.inc');
$gallery = new gallery();
//logger($gallery->cur_factor,'dbg.inc');
$validation_error='';

$loc_script = 'index.php';


if(is_ajax())
{
	$response = '';
	$response->err = '';
	$response->html = '';
	
	if(isset($_POST['change_factor']))
	{
		try
		{
			$gallery->set_factor($_POST['set_factor']);
		}
		catch(Exception $e)
		{
			$response->err = $e->getMessage();
		}
	}
	
	header('Content-Type:text/html;charset=utf-8');
	echo json_encode($response);

	exit;
}

// PROCESS
    // Add new one
if(isset($_POST['add_cat'])) {
    
    $p_name = trim(strip_tags($_POST['p_name']));
    if($p_name != '') {
        
        $data='';
        $data->p_name=$p_name;
        $data->publish=(int)$_POST['publish'];
        $data->pid = isset($_POST['pid']) ? (int)$_POST['pid'] : 0;
        if($data->pid > 0)
            $data->rid = $gallery->get_root_id($_POST['pid']);
        
        if(isset($_POST['no_photos']) && (int)$_POST['no_photos'] == 1) {
            $data->no_photos = (int)$_POST['no_photos'];
            $data->p_description = trim(strip_tags($_POST['p_description']));
        }
        $maxorder=$db->get_extreme_value($gallery->tree_table,'max(sort_order) as maxorder',' pid=\''.$data->pid.'\'');
        $data->sort_order=($maxorder->maxorder)+1;

        $data->cat_text = '';
        
        $ins_id = $db->insert_obj($gallery->tree_table, $data);
        
        if($data->pid==0 && $ins_id) {
            
            $up_data='';
            $up_data->rid = $ins_id;
            $db->update_obj($gallery->tree_table,$up_data,'id=\''.$ins_id.'\'');
        }
        
        if($ins_id) {
            $err_message='';
            $main_dir = PUBPATH . $gallery->photo_dir_pref.$ins_id;
            $thumb_dir = PUBPATH . $gallery->photo_dir_pref.$ins_id.$gallery->thumb_dir;
            $sound_dir = PUBPATH . $gallery->photo_dir_pref.$ins_id.$gallery->sound_dir;
            $anysrc_dir = PUBPATH . $gallery->photo_dir_pref.$ins_id.$gallery->anysrc_dir;
            if(@mkdir($main_dir)) {
                
                @chmod($main_dir,d_perm);
                
                if(@mkdir($thumb_dir))
                    @chmod($thumb_dir,d_perm);
                else
                    $err_message='Cannot create directory "'.$thumb_dir.'"'.__LINE__;
                if(@mkdir($sound_dir))
                    @chmod($sound_dir,d_perm);
                else
                    $err_message='Cannot create directory "'.$sound_dir.'"'.__LINE__;
                if(@mkdir($anysrc_dir))
                    @chmod($anysrc_dir,d_perm);
                else
                    $err_message='Cannot create directory "'.$anysrc_dir.'"'.__LINE__;
            }
            else
                $err_message='Cannot create directory "'.$main_dir.'"'.__LINE__;
            
            if($err_message != '')
                error_log($err_message,1,s_email);
        }
        
        header('Location: index.php');
        exit();
    }
    else
        $validation_error = '<p class="error">Обязательные поля должны быть заполнены!</p>';
}

    //Saving
    
if(isset($_POST['save_cat'])) {
    
    $p_name = trim(strip_tags($_POST['p_name']));
    if($p_name != '') {
        
        $data='';
        $data->p_name=$p_name;
        $data->p_title = trim(strip_tags($_POST['p_title']));
        $data->p_keywords = trim(str_replace(array("\r","\t","\n"),array(' ','',' '),strip_tags($_POST['p_keywords'])));
        $data->p_description = trim(str_replace(array("\r","\t","\n"),array(' ','',' '),strip_tags($_POST['p_description'])));
        $data->publish=(int)$_POST['publish'];
/*        $data->no_photos = (int)$_POST['no_photos'];*/
        
        
        if(isset($_FILES['photo']) && $_FILES['photo']['name'] != '' && !isset($_POST['photo_kill'])) {
            $new_name = md5(uniqid(rand(), true)).strrchr($_FILES['photo']['name'], '.');
/*            echo $new_name.'<br />';*/
/*            echo base.$gallery->photo_dir_pref.(int)$_POST['cat_id'].'/';*/
            $data->no_photos = $image->store_image($_FILES['photo']['tmp_name'],$_FILES['photo']['name'],PUBPATH . $gallery->photo_dir_pref.(int)$_POST['cat_id'].'/',false,false,$new_name);
        
            if($_POST['photo_old'] != '')
                @unlink(PUBPATH . $gallery->photo_dir_pref.(int)$_POST['cat_id'].'/'.$_POST['photo_old']);
        }
        if(isset($_POST['photo_kill'])) {
            $data->no_photos = '';
            @unlink(PUBPATH . $gallery->photo_dir_pref.(int)$_POST['cat_id'].'/'.$_POST['photo_old']);
        }
        
        $db->update_obj($gallery->tree_table,$data,' id=\''.(int)$_POST['cat_id'].'\'');
        
        header('Location: index.php?saving_process=success');
        exit();
    }
}
    
    // Removing
if(isset($_GET['remove_gallery']) && (int)$_GET['remove_gallery']>0) {
    
    $r_tree=array();
    $gallery->get_gal_tree($r_tree,(int)$_GET['remove_gallery']);
    $for_remove=array((int)$_GET['remove_gallery']);
    if(count($r_tree)) {
        foreach($r_tree as $val) $for_remove[]=$val->id;
    }
    
    foreach($for_remove as $v) {
        
        $dest_folder = PUBPATH . $gallery->photo_dir_pref.$v;
        if ($dir = @opendir($dest_folder)) {
            
            while (false !== ($file=@readdir($dir))) {
                if($file != '.' && $file != '..' && !is_dir($dest_folder.'/'.$file)) @unlink($dest_folder.'/'.$file);
                if($file != '.' && $file != '..' && is_dir($dest_folder.'/'.$file)) {
                    //echo $file;
                    if($d = @opendir($dest_folder.'/'.$file)) {
                        
                        while(false !== ($f=@readdir($d))) {
                            
                            if($f!='.' && $f != '..') @unlink($dest_folder.'/'.$file.'/'.$f);
                        }
                        @closedir($d);
                    }
                    @rmdir($dest_folder.'/'.$file);
                }
            }
        @closedir($dir);
        }
        @rmdir($dest_folder);
    }
    
    $db->remove_obj($gallery->tree_table,' where id in ('.join(',',$for_remove).')');
    $db->remove_obj($gallery->photo_table,' where cat_id in ('.join(',',$for_remove).')');
    
    header('Location: index.php');
    exit();
}

    // Moving Up & Down
/*if($_GET['move']) {
    
    $gallery->moving($gallery->tree_table,$_GET['move'],$_GET['id'],'pid');
    header('Location: index.php');
    exit();
}*/

require_once('../../../js/JsHttpRequest.php');

$JsHttpRequest = new JsHttpRequest("utf-8");

$updated = 0;

if(isset($_REQUEST['save_content'])) {

	if(isset($_GET['p']) && !empty($_GET['p'])) {
	
		//error_log(var_export($_GET['p'],true),3,base.'sort_order.txt');
		$updated=1;
		
		$i=1;
		foreach($_GET['p'] as $cat_id) {
		
			$data='';
			$data->sort_order = $i;
			if(!$db->update_obj($gallery->tree_table,$data,'id=\''.$cat_id.'\' and pid=\''.(int)$_REQUEST['update_level'].'\''))
				$updated = 0;
			$i++;
		}
		
	}
	//sleep(4);
	$GLOBALS['_RESULT'] = array('updated'=>$updated);
	exit();
}

// PROCESS
$tree = array();

if(isset($_REQUEST['force'])) {

	//sleep(3);
	$done = 0;
	$data = 0;
	
	if(method_exists($gallery,$_REQUEST['force'])) {
	
		$first_option='';
		$first_option->id=0;
		$first_option->p_name='В верхний уровень';
		
		$tree=array($first_option);
		call_user_method($_REQUEST['force'],$gallery,$tree);
		$gallery->catalog_tree($tree);
		
		$data = $tree;
	}
	else
		$data = 0;
	
	
	$done=1;
	
	$GLOBALS['_RESULT'] = array('done'=>$done, 'data'=>$data);
	exit();
}

$page_title = 'Catalog';

$add_styles = array('../css/style.css?v=1.0.1');

include('../../../admin/header.php');

$html->no_module_page($gallery->gallery_page_id,get_class($catalog));

echo $catalog->build_sub_menu();

?>

<div>
	<span>Курс российского рубля к доллару:</span>&nbsp;
	<input type="text" name="cur_factor" id="cur_factor" value="<?echo $gallery->cur_factor?>" />&nbsp;
	<input type="button" name="sc" value="Сохранить" id="save-factor" />
<script type="text/javascript" charset="UTF-8">
var a_scr = '<?echo $loc_script;?>';
$.getScript('cur_factor.js');
</script>
</div>

<script language="JavaScript">
var t_qstr = '';
</script>
<form enctype="multipart/form-data" action="<?php echo $form_action;?>" name="admin" method="post">
<h1>Категории.<span class="instr info" title="Help" onclick="trig('ex');">&nbsp;</span></h1>

<span class="instr new" onclick="trig('add_page');" title="Создать новую категорию">&nbsp;</span>

<?
$show_panel = (isset($_GET['edit']) || $validation_error != '') ? 'style="display:block;"' : '';
if(isset($_POST['add_cat']))
	echo '
<script language="JavaScript">
<!--
$(document).ready(function(){
    get_select(\'index.php\',\'catalog_tree\',\'s_tree\',\'pid\',\''.(int)$_POST['pid'].'\');
  });

//-->
</script>
';
?>

<div class="zone" id="add_page" <?=$show_panel?>>

<?
$form=array();

if(isset($_GET['edit']) && (int)$_GET['edit']>0) {
    
    $cat_id=(int)$_GET['edit'];
    $item=$db->select_obj($gallery->tree_table,'id,pid,p_name,publish,no_photos,p_title,p_keywords,p_description,thumb,p_bg',' id=\''.$cat_id.'\'','file: '.__FILE__.'line:'.__LINE__);
    if($item) $item=$item[0];
        
    $form['title'] = $validation_error.'<h2>Редактирование категории &laquo;'.$item->p_name.'&raquo;</h2>';
    $form['hidden_fields']=array(
    array('type'=>'hidden', 'name'=>'cat_id', 'value'=>$item->id),
    array('type'=>'hidden', 'name'=>'cat_pid', 'value'=>$item->pid)
    );
    //
    $form['fields'][]=array('txt'=>'Категория', 'type'=>'text', 'name'=>'p_name', 'require'=>1, 'value'=>htmlspecialchars($item->p_name));
    $form['fields'][]=array('txt'=>'Заголовк для браузера', 'type'=>'text', 'name'=>'p_title', 'value'=>htmlspecialchars($item->p_title));
    $form['fields'][]=array('txt'=>'Ключевые слова', 'attr' => 'maxlength="600"', 'type'=>'textarea', 'name'=>'p_keywords', 'value'=>$item->p_keywords, 'class'=>'long');
    $form['fields'][]=array('txt'=>'Краткое описание страницы', 'attr' => 'maxlength="600"', 'type'=>'textarea', 'name'=>'p_description', 'value'=>$item->p_description, 'class'=>'long');
    $form['fields'][]=array('txt'=>'Публиковать?', 'type'=>'checkbox', 'value'=>1, 'checked'=>$item->publish, 'name'=>'publish');
    
    /*$form['fields'][]=array('txt'=>'Картинка', 'type'=>'file', 'name'=>'photo');
        if($item->no_photos != '')
            $form['fields'][]=array('txt'=>'Удалить картинку?', 'type'=>'checkbox', 'name'=>'photo_kill', 'value'=>1);
    */        
    $form['fields'][]=array(
        'txt'=>'&nbsp;', 'type'=>'submit', 'name'=>'save_cat', 'value'=>'Сохранить', 'back'=>'index.php', 'cancel_value'=>'Отмена'
        );
}
else {
    
    $select=array('Верхний уровень');
    if(!empty($tree)) {
        foreach($tree as $val) {
            $pre='';
            for($i=0;$i<$val->factor;$i++) $pre .='&raquo;&nbsp;';
            $select[$val->id]=$pre.$val->p_name;
        }
    }

    $form['title'] = $validation_error.'<h2>Создать новую категорию</h2>';

    $form['fields']=array(
        array('txt'=>'Категория', 'type'=>'text', 'name'=>'p_name', 'require'=>1),
        array(
            'txt'=>'В категорию<span class="instr refresh" title="Получить список" onclick="get_select(\'index.php\',\'catalog_tree\',\'s_tree\',\'pid\',\''.(isset($_POST['pid']) ? (int)$_POST['pid'] : '').'\');">&nbsp;</span>',
            'type'=>'js',
            'value'=>$select,
            'selected'=>(isset($_POST['pid']) ? (int)$_POST['pid'] : false),
            'td_id'=>'s_tree'),
        array('txt'=>'Публиковать?', 'type'=>'checkbox', 'value'=>1, 'checked'=>1, 'name'=>'publish')
    );

    $form['fields'][]=array(
        'txt'=>'&nbsp;', 'type'=>'submit', 'name'=>'add_cat', 'value'=>'Создать'
        );
}

echo $html->_form($form);



?>

</div>

<div class="explan" style="display: none;" id="ex">Кликнуть на названии категории для работы с товаром.</div>

<?

$html_str = '<div class="menu_holder" style="width:auto;">';
$js_str = '<script>
$(document).ready(function(){
';
$gallery->sortable_tree_html($html_str,$js_str,$loc_script);
$js_str .= '});
</script>';
$html_str .= '</div>';

echo $js_str;
echo $html_str;
?>
</form>
<div id="catalog-sections" style="float:left;margin-left:20px;width:340px;border:1px solid lightgrey;padding:8px;" class="boxed">

    <div class="boxed">
        <input type="text" name="section_name" placeholder="Новый раздел Каталога" style="width:140px;" />
        <input type="number" name="sort_order" placeholder="Порядок сортировки" value="0" style="width:40px;" />
        <button data-action="index.php" id="add-section-button">Добавить Раздел</button>
    </div>

    <div class="boxed" id="sections-list"></div>

    <script>
        jQuery(document).ready(function($) {

            $.ajax(
                {
                    url:'sections.php',
                    type:'get',
                    cache:false,
                    dataType:'json',
                    data:{action:'items'},
                    success:function(response) {

                        if(response.error) {
                            show_save_alert(response.error);
                        }
                        else {
                            $('#sections-list').html(response.result);
                        }
                    }
                }
            );
            $.getScript('sections.js');
        });
    </script>
</div>
<?php
include('../../../admin/footer.php');
exit;
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

if(!isset($_SESSION['user']) || $_SESSION['user'] != 'admin') {
    session_destroy();
    header('Location: /admin/auth.php');
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
$catalog_ex = new catalog();
$catalog_ex->load_settings();
$gallery = new gallery();


$validation_error='';
$loc_script = 'items.php';

if(isset($_GET['cat_id'])) $cat_id=(int)$_GET['cat_id'];
elseif(isset($_POST['cat_id'])) $cat_id=(int)$_POST['cat_id'];
else $cat_id=0;

if($cat_id < 1) {
    header('Location: index.php');
    exit();
}


// PROCESS

// Moving Up & Down
require_once('../../../js/JsHttpRequest.php');

$JsHttpRequest = new JsHttpRequest("utf-8");

if (isset($_REQUEST['save_content']) && (int) $_REQUEST['save_content'] == 1) {

    $updated = false;

    if (isset($_GET['n']) && $cat_id) {

        $get_arr          = array(); // Start from 1
        $sort_by_sord     = array(); // Key - sort_order
        $sort_by_sord_tmp = array(); // Old sorting

        $i = 1;
        foreach ($_GET['n'] as $val) {

            $tmp = explode('~', $val);
            if ( ! empty($tmp)) {

                $get_arr[$i]           = array('sord' => $tmp[0], 'id' => $tmp[1]);
                $sort_by_sord[$tmp[0]] = array('sord' => $tmp[0], 'id' => $tmp[1]);
                $i ++;
            }
        }

        ksort($sort_by_sord);

        $i = 1;
        foreach ($sort_by_sord as $val) {

            $sort_by_sord_tmp[$i] = $val;
            $i ++;
        }

        foreach ($get_arr as $key => $val) {

            $data             = new stdClass();
            $data->sort_order = $sort_by_sord_tmp[$key]['sord'];
            $db->update_obj($gallery->photo_table, $data, 'id=\'' . $val['id'] . '\' and cat_id=\'' . $cat_id . '\'');
        }

        $updated = 1;
    }

    $GLOBALS['_RESULT'] = array('updated' => $updated);
    exit();
}
// Moving Up & Down


/*  Cache clean  */
if(isset($_REQUEST['to_clean'])) {

    if(unlink($_REQUEST['to_clean'])) {

        $GLOBALS['_RESULT'] = array('removed'=>1);
        exit();
    }
}
/*  Cache clean  */

// Add NEW Item
if(isset($_POST['add_file'])) {

    $new_item_name = trim(strip_tags($_POST['item_name']));

    if($new_item_name != '') {

        $data             = new stdClass();
        $data->cat_id     = $cat_id;
        $data->publish    = isset($_POST['publish']) ? (int) $_POST['publish'] : 0;
        $maxorder         = $db->get_extreme_value($gallery->photo_table, 'max(sort_order) as maxorder', ' cat_id=\'' . $cat_id . '\'');
        $data->sort_order = $maxorder->maxorder + 1;
        $data->item_name  = $new_item_name;

        if($_FILES['photo']['name']) {  // Add Photo

            $img_data=array(
                array('folder'=>PUBPATH.$gallery->photo_dir_pref.$cat_id.'/','width_limit'=>photo_width,'height_limit'=>photo_height,'grayscale'=>false),
                array('folder'=>PUBPATH.$gallery->photo_dir_pref.$cat_id.$gallery->thumb_dir.'/','width_limit'=>thum_width,'height_limit'=>thum_height,'grayscale'=>false)
            );

            $fname = $image->uniq_name($_FILES['photo']['name']);
            $img_info = @getimagesize($_FILES['photo']['tmp_name']);

            if ((isset($img_info)) && (($img_info['mime'] == 'image/jpeg') || ($img_info['mime'] == 'image/jpg')) ) {
                $data->photo = $image->store_image_iterate($_FILES['photo']['tmp_name'],$_FILES['photo']['name'],$img_data,$fname);
            } elseif ($img_info[0]>thum_width) {
                $validation_error='<p class="error">Ширина картинка должна быть меньше или равна '.thum_width.'px!</p>';
            } else {

                foreach($img_data as $val) {
                    $folder = $val['folder'];
                    $data->photo = $image->store_image($_FILES['photo']['tmp_name'],$_FILES['photo']['name'],$folder,false,false,$fname);
                }

            }

            //$image->create_grey($_FILES['photo']['tmp_name'],PUBPATH.'files/grey.jpg');

        }


        if ($validation_error == '') {

            $ins_id = $db->insert_obj($gallery->photo_table, $data);

            if ($ins_id) {

                $d           = new stdClass();
                $d->is_empty = 'is_empty+1';
                $db->update_int_obj($gallery->tree_table, $d, ' id=\'' . $cat_id . '\'');
            }

            header('Location: items.php?cat_id=' . $cat_id);
            exit();
        }
    }
    else $validation_error='<p class="error">Поле Наименование обязательно!</p>';
}

// Savinf edited
if(isset($_POST['save_file'])) {

    $save_name = trim(strip_tags($_POST['item_name']));

    if ($save_name != '') {

        $data            = new stdClass();
        $data->publish   = isset($_POST['publish']) ? (int) $_POST['publish'] : 0;
        $data->item_name = $save_name;

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {

            if (isset($_POST['photo_file']) && $_POST['photo_file'] != '') {

                @unlink(PUBPATH . $gallery->photo_dir_pref . $cat_id . '/' . $_POST['photo_file']);
                @unlink(PUBPATH . $gallery->photo_dir_pref . $cat_id . $gallery->thumb_dir . '/' . $_POST['photo_file']);
                //@unlink(PUBPATH.$gallery->photo_dir_pref.$cat_id.$gallery->thumb_gray.'/'.$_POST['photo_file']);
            }

            $img_data = array(
                array('folder'       => PUBPATH . $gallery->photo_dir_pref . $cat_id . '/',
                      'width_limit'  => photo_width,
                      'height_limit' => photo_height,
                      'grayscale'    => false,
                ),
                array('folder'       => PUBPATH . $gallery->photo_dir_pref . $cat_id . $gallery->thumb_dir . '/',
                      'width_limit'  => thum_width,
                      'height_limit' => thum_height,
                      'grayscale'    => false,
                ),
            );

            $fname    = $image->uniq_name($_FILES['photo']['name']);
            $img_info = @getimagesize($_FILES['photo']['tmp_name']);

            $data->photo = $image->store_image_iterate($_FILES['photo']['tmp_name'], $_FILES['photo']['name'], $img_data, $fname);
        }
        $data->short            = $_POST['short'];
        $data->keywords         = trim(strip_tags(str_replace(array("\r", "\n"), ' ', $_POST['keywords'])));
        $data->c_links          = trim(strip_tags($_POST['c_links']));
        $data->c_gal            = trim(strip_tags($_POST['c_gal']));
        $data->meta_description = isset($_POST['meta_description']) ? trim(strip_tags($_POST['meta_description'])) : '';
        $data->title            = trim(strip_tags(str_replace(array("\r", "\n"), ' ', $_POST['title'])));
        $data->price            = trim(strip_tags($_POST['item_price']));
        $data->quantity         = (int) $_POST['item_quantity'];
        $showcase               = 0;
        if (isset($_POST['showcase']) AND (int) $_POST['showcase'] === 1) {
            $showcase = 1;
        }

        $data->showcase = $showcase;

        if ($validation_error == '') {
            $db->update_obj($gallery->photo_table, $data, ' id=\'' . (int) $_POST['item_id'] . '\'');

            header('Location: items.php?cat_id=' . $cat_id . '&saving_process=success');
            exit();
        }
    }
}


// Removing

if(isset($_GET['remove'])) {

    $id   = (int) $_GET['remove'];
    $item = $db->select_obj($gallery->photo_table, 'photo,up_files', ' id=\'' . $id . '\'');
    if ($item) {

        $item = $item[0];
        if ($item->photo != '') {
            @unlink(PUBPATH . $gallery->photo_dir_pref . $cat_id . '/' . $item->photo);
            @unlink(PUBPATH . $gallery->photo_dir_pref . $cat_id . $gallery->thumb_dir . '/' . $item->photo);
            //@unlink(PUBPATH.$gallery->photo_dir_pref.$cat_id.$gallery->thumb_gray.'/'.$item->photo);
        }

        $upfiles = unserialize($item->up_files);
        if ( ! empty($upfiles)) {

            $r_folder = PUBPATH . $gallery->photo_dir_pref . $cat_id . $gallery->sound_dir;

            foreach ($upfiles as $val) {

                @unlink($r_folder . '/' . $val->fname);
            }
        }

        $db->remove_obj($gallery->photo_table, ' where id=\'' . $id . '\'');
        $d           = new stdClass();
        $d->is_empty = 'is_empty-1';
        $db->update_int_obj($gallery->tree_table, $d, ' id=\'' . $cat_id . '\'');

        header('Location: items.php?cat_id=' . $cat_id);
        exit();
    }
}


// PROCESS

$page_title = 'Single Item';

$add_styles = array('../css/style.css?v=1.0');

include('../../../admin/header.php');

echo $catalog_ex->build_sub_menu();

$category=$db->select_obj($gallery->tree_table,'id,pid,p_name',' id=\''.$cat_id.'\'');
if($category) $category=$category[0];
?>

    <script language="JavaScript">
        $(document).ready(function(){
            $("#plc").sortable();
        });
        var t_qstr = '';
    </script>
    <form enctype="multipart/form-data" action="<?=$form_action;?>" name="admin" method="post">
        <h1>Позиции для категории &laquo;<?=$category->p_name;?>&raquo;<span class="instr info" title="Подсказка" onclick="trig('ex');">&nbsp;</span></h1>
        <table cellpadding="0" cellspacing="0" class="container">
            <tr>
                <td style="width: <?echo (270+thum_width);?>px; border-right: 1px solid #6D8C7A;">
                    <div class="explan" style="display: none;" id="ex">
                        Убедитесь, что размер загружаемого файла не больше, чем <span><b style="color: #f00;"><?=$image->get_max_upload_size();?></b></span><br />
                        Для работы со спецификацией, кликнуть на наименовании.
                    </div>
                    <?
                    $photo_list = $db->select_obj($gallery->photo_table,'id,cat_id,sort_order,publish,item_name,photo,showcase',' cat_id=\''.$cat_id.'\' order by sort_order');

                    if($photo_list) {


                        echo '<div id="plc">';
                        foreach($photo_list as $val) {

                            $preview_link='/?set='.$gallery->gallery_page_id.'&gallery='.$cat_id.'&item='.$val->id.'&preview=1';
                            $page_preview='<span class="instr preview" onclick="window.open(\''.$preview_link.'\',\'_blank\');" title="Просмотреть страницу">&nbsp;</span>';

                            $edit_link='<span class="instr edit"><a href="'.$loc_script.'?cat_id='.$cat_id.'&edit='.$val->id.'" title="Редактировать">&nbsp;</a></span>';
                            $drop_link='<span class="instr drop"><a href="'.$loc_script.'?cat_id='.$cat_id.'&remove='.$val->id.'" title="Удалить" onclick="return confirm(\'Удалить позицию?\');">&nbsp;</a></span>';

                            isset($_GET['edit']) && $val->id == (int)$_GET['edit'] ? $style='style="border: 2px solid #f00;"' : $style='';
                            $val->publish==1 ? $imgclass='' : $imgclass='op';
                            /*        $preview='onclick="window.open(\''.$relative.$gallery->photo_dir_pref.$cat_id.'/'.$val->photo.'\',\'newWindow\',\'resizable,width='.(photo_width+20).',height='.(photo_height+20).'\');" title="Увеличить изображение"';*/
                            $preview='onclick="window.open(\''.$relative.$gallery->photo_dir_pref.$cat_id.$gallery->thumb_dir.'/'.$val->photo.'\',\'newWindow\',\'resizable,width='.(thum_width+20).',height='.(thum_height+20).'\');" title="Увеличить изображение"';
                            $item_name = '<a href="single_item.php?item_id='.$val->id.'&cat_id='.$cat_id.'" title="Работать со спецификацией" class="'.$imgclass.'">'.$val->item_name.'</a>';
                            if($val->photo != '')
                                $img_preview = '<span class="instr view" '.$preview.'>&nbsp;</span>';
                            else
                                $img_preview='<span class="instr blank">&nbsp;</span>';

                            $stored_file=cache_file_name('set='.$gallery->gallery_page_id.'&gallery='.$cat_id.'&item='.$val->id,PUBPATH);

                            if(file_exists($stored_file)) {

                                $clean_str='<span class="instr stored" title="Очистить кэш" onclick="clean_cache(\''.$loc_script.'?cat_id='.$cat_id.'\',\''.$stored_file.'\',this.className=\'instr blank\');">&nbsp;</span>';
                            }
                            else
                                $clean_str='<span class="instr blank">&nbsp;</span>';

                            echo '<div class="place" '.$style.' id="n_'.$val->sort_order.'~'.$val->id.'">';
                            echo '<p style="clear:both;"><span class="instr drag" onmouseup="sort_order(\''.$loc_script.'\',0,\'cat_id='.$cat_id.'&\'+$(\'#plc\').sortable(\'serialize\'),\'#plc\');">&nbsp;</span>'.$item_name.'</p>';

                            $show = '<span class="instr blank">&nbsp;</span>';
                            if($val->showcase==1)
                                $show = '<span class="instr info">&nbsp;</span>';

                            echo $img_preview.$edit_link.$drop_link.$page_preview.$show.$clean_str;
                            echo '</div>';
                        }
                        echo '</div>';
                    }

                    ?>
                    &nbsp;</td>
                <td>
                    <?php
                    $form                  = array();
                    $form['hidden_fields'] = array(
                        array('type' => 'hidden', 'name' => 'cat_id', 'value' => $cat_id),
                    );

                    if(isset($_GET['edit']) && (int)$_GET['edit']>0) {

                        $id            = (int) $_GET['edit'];
                        $form['title'] = $validation_error . '<h2>Редактирование</h2>';

                        echo $html->wysiwyg_init($relative.'admin/',$gallery->photo_dir_pref.$cat_id.$gallery->anysrc_dir, 'advanced', 'teditor');

                        if($item=$db->get_extreme_value($gallery->photo_table,'id,publish,photo,item_name,c_links,c_gal,short,price,quantity,showcase,keywords,title,meta_description',' id=\''.$id.'\''))
                        {

                            $form['hidden_fields'][] = array('type'  => 'hidden',
                                                             'name'  => 'item_id',
                                                             'value' => $item->id,
                            );

                            if ($item->photo != '') {
                                $form['hidden_fields'][] = array('type'  => 'hidden',
                                                                 'name'  => 'photo_file',
                                                                 'value' => $item->photo,
                                );
                            }

                            $extLabel = empty($item->c_gal) ? 'Ссылка на продукт' : $item->c_gal;

                            $extraHtml = '<span class="boxed link" id="clear-link" style="float:left;margin-right:8px;">убрать ссылку</span>';
                            $extraHtml .= '<span data-default-label="Ссылка на продукт" style="float: left;" id="external-link" class="boxed relative fake-link" data-label="'. htmlspecialchars($extLabel) .'"></span>';
                            $extraHtml .= '<span class="relative" style="float: left;width:110px;"><span class="boxed tost corner-all shadow" id="display-list"></span></span>';
                            $extraHtml .= '<input type="hidden" name="c_links" value="'. htmlspecialchars($item->c_links) .'" />';
                            $extraHtml .= '<input type="hidden" name="c_gal" value="'. htmlspecialchars($item->c_gal) .'" />';

                            $form['fields'] = array(
                                array('txt' => 'Картинка', 'type' => 'file', 'name' => 'photo'),
                                array('txt'   => 'Наименование',
                                      'type'  => 'text',
                                      'name'  => 'item_name',
                                      'value' => htmlspecialchars($item->item_name, ENT_COMPAT, 'utf8'),
                                      'class' => 'long',
                                ),
                                array('txt'   => '&nbsp;',
                                      'type'  => 'special',
                                      'fields'=> $extraHtml,
                                ),
                                array('txt'   => 'Заголовок браузера',
                                      'type'  => 'text',
                                      'name'  => 'title',
                                      'value' => htmlspecialchars($item->title, ENT_COMPAT, 'utf8'),
                                      'class' => 'long',
                                ),
                                array('txt'   => 'Ключевые слова',
                                      'attr'  => 'maxlength="600"',
                                      'type'  => 'textarea',
                                      'name'  => 'keywords',
                                      'value' => ($item->keywords),
                                      'class' => 'long',
                                ),
                                array('txt'   => 'Мета-описание',
                                      'attr'  => 'maxlength="600"',
                                      'type'  => 'textarea',
                                      'name'  => 'meta_description',
                                      'value' => ($item->meta_description),
                                      'class' => 'long',
                                ),
                                array('txt'   => 'Краткое описание',
                                      'type'  => 'textarea',
                                      'name'  => 'short',
                                      'value' => ($item->short),
                                      'class' => 'long teditor',
                                ),
                                array('txt'     => 'Публиковать?',
                                      'type'    => 'checkbox',
                                      'value'   => 1,
                                      'checked' => $item->publish,
                                      'name'    => 'publish',
                                ),
                                array('txt'     => 'На витрину',
                                      'type'    => 'checkbox',
                                      'value'   => 1,
                                      'checked' => $item->showcase,
                                      'name'    => 'showcase',
                                ),
                                array('txt'   => 'Стоимость',
                                      'type'  => 'text',
                                      'name'  => 'item_price',
                                      'value' => htmlspecialchars($item->price),
                                      'class' => 'long',
                                ),
                                array('txt'   => 'Количество',
                                      'type'  => 'text',
                                      'name'  => 'item_quantity',
                                      'value' => htmlspecialchars($item->quantity),
                                      'class' => 'long',
                                ),
                            );

                            $form['fields'][]=array(
                                'txt'=>'&nbsp;', 'type'=>'submit', 'name'=>'save_file', 'value'=>'Сохранить', 'cancel_value'=>'Отмена', 'back'=>'items.php?cat_id='.$cat_id
                            );
                        }
                    }
                    else {

                        $form['title']=$validation_error.'<h2>Добавление</h2>';

                        $form['fields']=array(
                            array('txt'=>'Картинка', 'type'=>'file', 'name'=>'photo'),
                            array('txt'=>'Наименование', 'type'=>'text', 'name'=>'item_name', 'class'=>'long', 'require'=>1, 'value'=>isset($_POST['item_name'])?htmlspecialchars($_POST['item_name']):''),
                            array('txt'=>'Публиковать?', 'type'=>'checkbox', 'value'=>1, 'checked'=>1, 'name'=>'publish')
                        );

                        $form['fields'][]=array(
                            'txt'=>'&nbsp;', 'type'=>'submit', 'name'=>'add_file', 'value'=>'Добавить', 'back'=>'index.php', 'cancel_value'=>'К списку категорий'
                        );
                    }

                    echo $html->_form($form);
                    ?>
                </td>
            </tr>
        </table>
    </form>
<script>
    jQuery(document).ready(function($) {

        $('#external-link').bind('click', function() {

            var holder = $('#display-list');
            $.ajax({
                url:'ajax.php',
                dataType:'json',
                method:'get',
                data:{action:'product_links'},
                success:function(response) {

                    if('error' != response.status) {
                        holder.html(response.result);
                    }
                    else {
                        holder.html('<p style="color:#f00;">'+response.result+'</p>');
                    }
                }
            });
        });

        $('#clear-link').bind('click', function() {

            var obj = $('#external-link');
            $('input[name="c_links"]').val('');
            $('input[name="c_gal"]').val('');
            obj.attr('data-label', obj.attr('data-default-label'));
        });
    });
</script>
<?php
include('../../../admin/footer.php');

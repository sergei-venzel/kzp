<?php
// Auth LAYER
error_reporting(E_ALL);
session_id();
session_start();

require_once('../config.php');

if ( ! defined('installed')) {

    session_destroy();
    header('Location: ../install/');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['user']);
}

if ( ! isset($_SESSION['user']) || $_SESSION['user'] != 'admin') {
    session_destroy();
    header('Location: auth.php');
    exit();
}
// Auth LAYER

// Check installed tables

// Check installed tables

$relative = RELATIVE_PATH;

$loc_script = 'site_pages.php';

/**
 * @var db $db
 */
$db = Registry::getInstance()->get('db');

require_once('class.pages.php');
$pages = new pages();
$layouts = $pages->page_types();

require_once('class.image.php');
$image = new image();

$db_table = $pages->table;

// PROCESSES
$validation_error = '';

if (isset($_POST['p_sub'])) {

    $data         = new stdClass();
    $data->p_name = trim(strip_tags($_POST['p_name']));

    if ($data->p_name != '') {

        if (isset($_POST['alone'])) {

            $data->pid = - 1;
        }
        else {
            if (isset($_POST['p_level'])) {
                $levels = explode('_', $_POST['p_level']);
            }
            else {
                $levels = explode('_', '0_0_0');
            }

            if (count($levels) == 3) {
                $data->pid = (int) $levels[0];
                if ($data->pid !== 0) {
                    $data->rid = (int) $levels[1];
                }
            }
        }

        $data->publish = isset($_POST['p_publ']) ? (int) $_POST['p_publ'] : 0;
        if ($data->pid < 1) {

            $data->top_menu = (int) $_POST['s_menu'];

            if ($data->top_menu == 1) {
                $tops = $db->get_extreme_value($db_table, 'count(top_menu) as tops', 'publish=1 and top_menu=1');
                if ($tops->tops >= 5 && $data->top_menu == 1) {
                    $data->top_menu = 0;
                }
            }

            /*            $data->bg = $image->draw_word_image($data->p_name,PUBPATH.design.'/');*/
            $isset_main = $db->get_extreme_value($db_table, '1', 'main_page=1');
            if ( ! $isset_main) {
                $data->main_page = 1;
            }
        }

        $data->p_title = trim(strip_tags($_POST['p_title']));
        $layout        = strip_tags($_POST['p_layout']);
        $layouts[$layout] ? $data->layout = $layout : $data->layout = 'common';
        //$data->bg = (int)$_POST['bgimg'];
        $order = $db->get_extreme_value($db_table, ' max(sort_order) as morder', 'pid=\'' . $data->pid . '\'', 'file: ' . __FILE__ . 'line:' . __LINE__);
        if ($order) {
            $data->sort_order = ($order->morder) + 1;
        }

        $data->external = isset($_POST['external']) ? (int) $_POST['external'] : 0;
        $data->ext_link = str_replace('http://', '', trim(strip_tags($_POST['ext_link'])));

        $ins_id = $db->insert_obj($db_table, $data);

        if ($data->pid === 0) {
            $data      = '';
            $data->rid = $ins_id;

            $db->update_obj($db_table, $data, ' id=\'' . $ins_id . '\'');

            @mkdir(PUBPATH . p_f_dir . $data->rid/*,0707,true*/);
            @chmod(PUBPATH . p_f_dir . $data->rid, d_perm);
        }
        elseif ($data->pid == - 1) {

            $data      = new stdClass();
            $data->rid = $ins_id;
            $db->update_obj($db_table, $data, ' id=\'' . $ins_id . '\'');
            @mkdir(PUBPATH . p_f_dir . $ins_id/*,0707,true*/);
            @chmod(PUBPATH . p_f_dir . $ins_id, d_perm);
        }

        header('Location: ' . $loc_script);
        exit();
    }
    else {
        $validation_error = '<p class="error">Поля, помеченные *, должны быть заполнены!</p>';
    }

}

if (isset($_POST['p_save'])) {

    $data         = new stdClass();
    $data->p_name = trim(strip_tags($_POST['p_name']));
    if ($data->p_name != '') {

        if ((int) $_POST['page_pid'] > 0) {

            $levels = explode('_', $_POST['p_level']);
            if (count($levels) == 3) {

                if ((int) $levels[0] !== 0) {

                    $data->pid = (int) $levels[0];
                    $data->rid = (int) $levels[1];
                }
            }

            if (isset($data->rid) && isset($data->pid) && $data->pid != (int) $_POST['page_pid']) {

                //error_log("HERE\r\n",3,PUBPATH.'sort_order.txt');
                $order = $db->get_extreme_value($db_table, ' max(sort_order) as morder', 'pid=\'' . $data->pid . '\'', 'file: ' . __FILE__ . 'line:' . __LINE__);
                if ($order) {
                    $data->sort_order = ($order->morder) + 1;
                    error_log($data->sort_order . "\r\n", 3, PUBPATH . 'sort_order.txt');
                }

                $pages->renew_branch($data->rid, $_POST['page_id']);
            }
        }

        $data->publish = (int) $_POST['p_publ'];

        $data->top_menu = (int) $_POST['s_menu'];
        if ($data->top_menu == 1) {
            $tops = $db->get_extreme_value($db_table, 'count(top_menu) as tops', 'publish=1 and top_menu=1');
            if ($tops->tops >= 5 && $data->top_menu == 1) {
                $data->top_menu = 0;
            }
        }

        $data->p_title       = trim(strip_tags($_POST['p_title']));
        $data->a_title       = trim(strip_tags($_POST['a_title']));
        $data->p_keywords    = str_replace(array("\r", "\n", "\t"), ' ', trim(strip_tags($_POST['p_keywords'])));
        $data->p_description = str_replace(array("\r", "\n", "\t"), ' ', trim(strip_tags($_POST['p_description'])));
        //$data->bg = (int)$_POST['bgimg'];
        $layout = strip_tags($_POST['p_layout']);
        $layouts[$layout] ? $data->layout = $layout : $data->layout = 'common';

        $data->external = (int) isset($_POST['external']);
        $data->ext_link = str_replace('http://', '', trim(strip_tags($_POST['ext_link'])));

        if (isset($_FILES['photo'])) {
            if ($_FILES['photo']['name'] != '' && ! $_POST['photo_kill']) {
                $new_name = md5(uniqid(rand(), true)) . strrchr($_FILES['photo']['name'], '.');
                $data->bg = $image->store_image($_FILES['photo']['tmp_name'], $_FILES['photo']['name'], PUBPATH . p_f_dir . (int) $_POST['page_id'] . '/', false, false, $new_name);
                if ($_POST['photo_old'] != '') {
                    @unlink(PUBPATH . p_f_dir . (int) $_POST['page_id'] . '/' . $_POST['photo_old']);
                }
            }
        }

        if (isset($_POST['photo_kill'])) {
            $data->bg = '';
            @unlink(PUBPATH . p_f_dir . (int) $_POST['page_id'] . '/' . $_POST['photo_old']);
        }

        $db->update_obj($db_table, $data, ' id=\'' . (int) $_POST['page_id'] . '\'');
    }

    header('Location: ' . $loc_script . '?saving_process=success');
    exit();
}


if (isset($_GET['delete_page'])) {

    $dir_id = false;//$pages->is_top($_GET['delete_page']);

    $res = $db->get_extreme_value($db_table, 'rid,bg', ' id=\'' . (int) $_GET['delete_page'] . '\' and (pid=0 or pid=-1)');
    if ($res) {

        $dir_id = $res->rid;
    }
    if ($dir_id) {

        //$dest_folder = PUBPATH.p_f_dir.$dir_id;
        exec('rm -r "' . PUBPATH . p_f_dir . $dir_id . '"');
    }

    $p_arr = array();
    $pages->get_root_pages($_GET['delete_page'], $p_arr);
    $db->remove_obj($db_table, ' where id in (' . join(',', $p_arr) . ')');

    header('Location: ' . $loc_script);
    exit();
}


require_once(PUBPATH . 'js/JsHttpRequest.php');

$JsHttpRequest = new JsHttpRequest("utf-8");

$updated = 0;

if (isset($_REQUEST['save_content'])) {

    if (isset($_GET['p']) && ! empty($_GET['p'])) {

        //error_log(var_export($_GET['p'],true),3,PUBPATH.'sort_order.txt');
        $updated = 1;

        $i = 1;
        foreach ($_GET['p'] as $page_id) {

            $data             = new stdClass();
            $data->sort_order = $i;
            if ( ! $db->update_obj($db_table, $data, 'id=\'' . $page_id . '\' and pid=\'' . (int) $_REQUEST['update_level'] . '\'')) {
                $updated = 0;
            }
            $i ++;
        }

    }
    //sleep(4);
    $GLOBALS['_RESULT'] = array('updated' => $updated);
    exit();
}

if (isset($_REQUEST['force'])) {

    //sleep(3);
    $done = 0;
    $data = 0;

    if (method_exists($pages, $_REQUEST['force'])) {

        if (isset($_GET['change_level']) && $_GET['change_level'] == 1) {
            $tree = array();
        }
        else {

            $first_option         = new stdClass();
            $first_option->id     = '0_0_0';
            $first_option->p_name = 'Верхний. Или...';

            $tree = array($first_option);
        }

        call_user_method($_REQUEST['force'], $pages, $tree);
        $pages->select_tree($tree);

        $data = $tree;
    }
    else {
        $data = 0;
    }


    $done = 1;

    $GLOBALS['_RESULT'] = array('done' => $done, 'data' => $data);
    exit();
}


if (isset($_GET['stat_main']) && (int) $_GET['stat_main'] > 0) {

    $ud            = new stdClass();
    $ud->main_page = 0;

    $db->update_obj($pages->table, $ud);

    $ud            = '';
    $ud->main_page = 1;

    $db->update_obj($pages->table, $ud, ' id=\'' . (int) $_GET['stat_main'] . '\'');

    header('Location: ' . $loc_script);
    exit();
}
// PROCESSES

$form_action = '';
include('header.php');
?>
    <form enctype="multipart/form-data" action="<?=$form_action;?>" name="admin" method="post">
        <?
        require_once('class.html.php');

        $html = new html();


        ?>
        <script language="JavaScript">
            <!--
            var t_qstr = '';

            //-->
        </script>

        <h1>Страницы сайта<span class="instr info" title="Подсказка" onclick="trig('ex');">&nbsp;</span></h1>
        <div class="explan" style="display: none;" id="ex">Кликните по названию страницы (ниже), чтобы работать с ее содержанием.</div>
        <span class="instr new" onclick="trig('add_page');" title="Создать новую страницу">&nbsp;</span>

        <?
        $show_panel = (isset($_GET['edit_page']) || $validation_error != '') ? 'style="display:block;"' : '';
        if(isset($_POST['p_sub']))
            echo '
<script language="JavaScript">
<!--
$(document).ready(function(){
    get_select(\''.$loc_script.'\',\'select_tree\',\'s_tree\',\'p_level\',\''.$_POST['p_level'].'\');
  });

//-->
</script>
';
        ?>
        <div class="zone" id="add_page" <?=$show_panel?>>
            <?php

            $data = array();

            $sub_menu = array(
                0   => 'по умолчанию',
                1   => 'в верхнем меню',
                - 1 => 'в нижнем меню',
            );

            if (isset($_GET['edit_page']) && (int) $_GET['edit_page'] > 0) {

                $edited_page = $db->get_extreme_value($db_table, 'id,rid,pid,publish,p_name,p_title,a_title,p_keywords,p_description,layout,bg,external,ext_link,top_menu', ' id=\'' . (int) $_GET['edit_page'] . '\'', 'file: ' . __FILE__ . 'line:' . __LINE__);

                if ($edited_page) {

                    $data['title']         = $validation_error . '<h2>Редактирование свойств страницы &laquo;' . $edited_page->p_name . '&raquo;&nbsp;<input type="button" class="more" value="" title="Дополнительно..." onclick="hider(this,[\'lab_1\',\'lab_2\']);" /></h2>';
                    $data['hidden_fields'] = array(
                        array('type' => 'hidden', 'name' => 'page_id', 'value' => $edited_page->id),
                        array('type' => 'hidden', 'name' => 'page_pid', 'value' => $edited_page->pid),
                        array('type' => 'hidden', 'name' => 'page_rid', 'value' => $edited_page->rid),
                    );
                    if ($edited_page->pid < 1) {
                        $data['hidden_fields'][] = array('txt'   => '',
                                                         'type'  => 'hidden',
                                                         'name'  => 'title_img',
                                                         'value' => $edited_page->bg,
                        );
                    }
                    $edited_page->external == 1 ? $s_display = 'block' : $s_display = 'none';
                    $data['fields'] = array(
                        array('txt'      => 'Название страницы',
                              'require'  => 1,
                              'type'     => 'text',
                              'name'     => 'p_name',
                              'value'    => htmlspecialchars($edited_page->p_name),
                              'class'    => 'long',
                              'td_class' => 'class="label"',
                        ),
                        array('txt'     => 'Заголовок (title)',
                              'require' => 0,
                              'type'    => 'text',
                              'name'    => 'p_title',
                              'value'   => htmlspecialchars($edited_page->p_title),
                              'class'   => 'long',
                        ),
                        array('txt'     => 'Title ссылки',
                              'require' => 0,
                              'type'    => 'text',
                              'name'    => 'a_title',
                              'value'   => htmlspecialchars($edited_page->a_title),
                              'class'   => 'long',
                        ),
                        array('txt'     => 'Внешняя ссылка',
                              'require' => 0,
                              'type'    => 'checkbox',
                              'name'    => 'external',
                              'value'   => 1,
                              'class'   => '',
                              'checked' => $edited_page->external,
                              'script'  => 'onclick="trig(\'ext_txt\'); trig(\'ext\');"',
                        ),

                        array('txt'     => '<div id="ext_txt" style="display: ' . $s_display . ';">Адрес</div>',
                              'require' => 0,
                              'type'    => 'text',
                              'id'      => 'ext',
                              'name'    => 'ext_link',
                              'value'   => htmlspecialchars($edited_page->ext_link),
                              'class'   => 'long',
                              'style'   => 'display: ' . $s_display . ';',
                        ),
                        array('txt'      => 'Ключевые слова<br /><small>(Для поисковых систем. Слова, разделенные пробелами. Не более, чем 255 символов, включая пробелы.)</small>',
                              'require'  => 0,
                              'type'     => 'textarea',
                              'attr'     => 'maxlength="600"',
                              'name'     => 'p_keywords',
                              'value'    => $edited_page->p_keywords,
                              'class'    => 'long',
                              'td_class' => 'class="label"',
                        ),
                        array('txt'      => 'Описание<br /><small>(Описание страницы (не обязательно). Не более, чем 255 символов, включая пробелы.)</small>',
                              'require'  => 0,
                              'attr'     => 'maxlength="600"',
                              'type'     => 'textarea',
                              'name'     => 'p_description',
                              'value'    => $edited_page->p_description,
                              'class'    => 'long',
                              'td_class' => 'class="label"',
                        ),
                        array('txt'     => 'Публиковать?',
                              'require' => 0,
                              'type'    => 'checkbox',
                              'name'    => 'p_publ',
                              'value'   => 1,
                              'class'   => '',
                              'checked' => $edited_page->publish,
                        ),

                    );

                    if (($edited_page->pid == 0) || ($edited_page->pid == - 1)) {
                        $data['fields'][] = array('txt'      => 'Внести в одно<br />из меню',
                                                  'require'  => 0,
                                                  'type'     => 'select',
                                                  'id'       => 'sub_menu',
                                                  'name'     => 's_menu',
                                                  'value'    => $sub_menu,
                                                  'class'    => '',
                                                  'selected' => $edited_page->top_menu,
                        );
                    }

                    if ($edited_page->pid > 0) {

                        $data['fields'][] = array('txt'   => 'Сменить уровень<p class="error">Внимание!<br />При смене "главного родителя"<br />необходимо восстановить ВСЕ ВНУТРЕННИЕ связи<br />в тексте ВСЕХ страниц ЭТОЙ ветки!</p><span class="instr refresh" title="Получить список" onclick="get_select(\'' . $loc_script . '?change_level=1\',\'select_tree\',\'s_tree\',\'p_level\',\'' . $edited_page->pid . '_' . $edited_page->rid . '_' . $edited_page->pid . '\');">&nbsp;</span>',
                                                  'tr_id' => 'level_txt',
                                                  'type'  => 'js',
                                                  'td_id' => 's_tree',
                        );
                    }

                    $data['fields'][] = array('txt'      => '<div id="lab_1" style="display: none;">Тип страницы</div>',
                                              'attr'     => 'style="display: none;"',
                                              'id'       => 'lab_2',
                                              'require'  => 0,
                                              'type'     => 'select',
                                              'name'     => 'p_layout',
                                              'value'    => $layouts,
                                              'selected' => $edited_page->layout,
                    );
                    $data['fields'][] = array('txt'          => '&nbsp;',
                                              'require'      => 0,
                                              'type'         => 'submit',
                                              'name'         => 'p_save',
                                              'value'        => 'Сохранить',
                                              'class'        => '',
                                              'back'         => $loc_script,
                                              'cancel_value' => 'Отменить',
                    );
                }
            }
            else {
                $data['title'] = $validation_error.'<h2>Создать новую страницу&nbsp;<input type="button" class="more" value="" title="Дополнительно..." onclick="hider(this,[\'lab_1\',\'lab_2\']);" /></h2>';
                (isset($_POST['p_title'])) ? $v_title=$_POST['p_title'] : $v_title=default_title;
                $data['fields'] = array(
                    array(
                        'txt' => 'Название страницы',
                        'require' => 1, 'type' => 'text', 'name' => 'p_name',
                        'value' => isset($_POST['p_name']) ? $_POST['p_name'] : '',
                        'class' => 'long',
                        'td_class' => 'class="label"'),
                    array('txt'=>'Заголовок (title)', 'require'=>0, 'type'=>'text', 'name'=>'p_title', 'value'=>$v_title, 'class'=>'long'),
                    array('txt'=>'Отдельная страница', 'require'=>0, 'type'=>'checkbox', 'name'=>'alone', 'value'=>1, 'class'=>'', 'checked'=>0, 'script'=>'onclick="react_on_check(document.admin.alone,\'level_txt\');"'),
                    array('txt'=>'Внешняя ссылка', 'require'=>0, 'type'=>'checkbox', 'name'=>'external', 'value'=>1, 'class'=>'', 'checked'=>0, 'script'=>'onclick="trig(\'ext_txt\'); trig(\'ext\');"'),
                    array('txt'=>'<div id="ext_txt" style="display: none;">Адрес</div>', 'require'=>0, 'type'=>'text', 'id'=>'ext', 'name'=>'ext_link', 'value'=>'', 'class'=>'long', 'style'=>'display: none;'),
                    array('txt'=>'Внести в иерархический<br />уровень<span class="instr refresh" title="Получить список" onclick="get_select(\''.$loc_script.'\',\'select_tree\',\'s_tree\',\'p_level\',false);">&nbsp;</span>', 'tr_id'=>'level_txt', 'type'=>'js', 'td_id'=>'s_tree'),
                    array('txt'=>'Внести в одно<br />из меню', 'require'=>0, 'type'=>'select', 'id'=>'sub_menu', 'name'=>'s_menu', 'value'=>$sub_menu, 'class'=>'', 'selected'=>0),
                    array('txt'=>'Публиковать?', 'require'=>0, 'type'=>'checkbox', 'name'=>'p_publ', 'value'=>1, 'class'=>'', 'checked'=>1),
                    array('txt'=>'<div id="lab_1" style="display: none;">Тип страницы</div>', 'attr'=>'style="display: none;"', 'id'=>'lab_2', 'require'=>0, 'type'=>'select', 'name'=>'p_layout', 'value'=>$layouts, 'selected'=>'common'),
                    array('txt'=>'&nbsp;', 'require'=>0, 'type'=>'submit', 'name'=>'p_sub', 'value'=>'Создать')
                );
            }

            echo $html->_form($data);


            ?>
        </div>

        <h1>Основные страницы</h1>
        <div>
            <script language="JavaScript">
                <!--

                //-->
            </script>
            <?

            /*echo '<pre>';
            print_r($all_tree_arr);
            echo '</pre>';*/

            $html_str = '<div class="menu_holder">';
            $js_str = '<script>

$(document).ready(function() {
';
            $pages->sortable_tree_html($html_str,$js_str,$loc_script);
            $js_str .= '});

</script>';
            $html_str .= '</div>';

            echo $js_str;
            echo $html_str;

            /*  Alone pages  */

            ?>
            <h1>Отдельные страницы</h1>
            <?

            $html_str = '<div class="menu_holder">';
            $js_str = '<script>

$(document).ready(function() {
';
            $pages->sortable_tree_html($html_str,$js_str,$loc_script,-1);
            $js_str .= '});

</script>';
            $html_str .= '</div>';

            echo $js_str;
            echo $html_str;


            ?>
        </div>
    </form>
<?php
//echo mysql_thread_id($db->db_conn);
include('footer.php');
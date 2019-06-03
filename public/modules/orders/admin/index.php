<?php

// Auth LAYER
error_reporting(E_ALL);
session_id();
session_start();

require_once('../../../config.php');
defined('SYSPATH') OR die('Forbidden');

$relative = RELATIVE_PATH;

if ( ! defined('installed')) {

    session_destroy();
    header('Location: ' . $relative . 'install/');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['user']);
}

if ( ! isset($_SESSION['user']) || $_SESSION['user'] != 'admin') {
    session_destroy();
    header('Location: /admin/auth.php');
    exit();
}
// Auth LAYER

if ( ! isset($_SESSION['orders_date'])) {
    $_SESSION['orders_date'] = array();
}

if (isset($_POST['month'])) {
    $_SESSION['orders_date']['month'] = $_POST['month'];
}
if (isset($_POST['year'])) {
    $_SESSION['orders_date']['year'] = $_POST['year'];
}

isset($_SESSION['orders_date']['month']) ? $mon = $_SESSION['orders_date']['month'] : $mon = date('n');
isset($_SESSION['orders_date']['year']) ? $year = $_SESSION['orders_date']['year'] : $year = date('Y');


/**
 * @var db $db
 */
$db = Registry::getInstance()->get('db');


require_once('class.html.php');
$html = new html();

require_once('orders/class.orders.php');
$orders = new orders();

$proc_tbl         = $orders->main_table;
$validation_error = '';


$loc_script = 'index.php';


// PROCESS

if (isset($_GET['accept'])) {

    $data         = '';
    $data->status = 1;
    $db->update_obj($proc_tbl, $data, ' id=\'' . (int) $_GET['accept'] . '\'');

    header('Location: ' . $loc_script);
    exit();
}

if (isset($_GET['del_order'])) {

    $db->remove_obj($proc_tbl, ' where id=\'' . (int) $_GET['del_order'] . '\'');

    header('Location: ' . $loc_script);
    exit();
}

if (is_ajax()) {
    $response      = '';
    $response->err = 1;

    if ($_POST['asave']) {
        //$response->html = iconv('UTF-8','windows-1251',_esc($_POST['answer']));
        try {
            //$orders->set_answer(iconv('UTF-8','windows-1251',_esc($_POST['answer'])));
            $orders->set_answer(_esc($_POST['answer']));
            $response->err = '';
        }
        catch (Exception $e) {
            $response->err = $e->getMessage();
        }
    }

    header('Content-Type:text/html;charset=utf-8');
    echo json_encode($response);
    $db->db_close($db->db_conn);
    exit;
}

// PROCESS
$page_title = 'Заказы';
include(PUBPATH . 'admin/header.php');
//echo str_replace(SYSPATH,'',w2u_path(dirname(__FILE__)));
/*echo w2u_path(dirname(__FILE__)).'<br />';
echo SYSPATH;*/
//echo $orders->answer_text_file;
?>
    <script type="text/javascript" src="ui.datepicker-min.js"></script>
    <link rel="stylesheet" href="orders.css" type="text/css"/>
    <link rel="stylesheet" href="style.css" type="text/css"/>
    <h1>Раздел обработки Заказов</h1>
    <div style="margin-bottom:10px;" class="clearfix">
        <h4>Текст для письма клиенту</h4>
        <div id="err"></div>
        <textarea name="answer" id="answer"><?php echo $orders->get_answer(); ?></textarea><br/>
        <input type="button" value="Сохранить" id="save_answer"/>
    </div>

    <div class="clearfix" style="margin-top:10px;padding:8px;border:1px solid #A8AFA8;">
        <h1>Промо коды</h1>
        <form name="add-discount" method="post" action="" onsubmit="return false;">
            <input type="text" name="token" placeholder="Промо код" />&nbsp;<span id="promo-gen" class="link" title="Сгенерировать промо-код">&hellip;</span>&nbsp;
            <input type="text" name="discount" placeholder="Процент скидки" />
            <input type="text" name="expired" placeholder="Действует до..." />
            <button data-action="createMethod" id="add-discount-button">Добавить</button>
        </form>

        <div id="discount-list" class="clearfix" style="margin:8px 0 0 0;"></div>

        <script type="text/javascript">
            jQuery(document).ready(function ($) {

                $('input[name="expired"]').datepicker(
                    {
                        buttonImageOnly:false,
                        dateFormat: "dd-mm-yy"
                    }
                );

                $.getScript('discount.js');

                $.ajax(
                    {
                        url:'discounts.php',
                        type:'get',
                        cache:false,
                        dataType:'json',
                        data:{action:'items'},
                        success:function(response) {

                            if(response.error) {
                                show_save_alert(response.error);
                            }
                            else {
                                $('#discount-list').html(response.result);
                            }
                        }
                    }
                );
            });
        </script>
    </div>

    <div class="clearfix" style="margin-top:10px;padding:8px;border:1px solid #A8AFA8;">
        <h1>Способы доставки</h1>
        <form name="add-shipping" method="post" action="" onsubmit="return false;">
        <input type="text" name="name" placeholder="Способ доставки" />
        <input type="text" name="cost" placeholder="Стоимость доставки" />
        <textarea name="description" style="height:40px;display:block;margin:6px 0;" placeholder="Комментарии к способу доставки"></textarea>
        </form>
        <button data-action="createMethod" id="add-ship-button">Добавить</button>
        <div id="shipp-list" class="clearfix" style="margin:8px 0 0 0;"></div>

        <script type="text/javascript">
            jQuery(document).ready(function ($) {

                $.ajax(
                    {
                        url:'shippings.php',
                        type:'get',
                        cache:false,
                        dataType:'json',
                        data:{action:'items'},
                        success:function(response) {

                            if(response.error) {
                                show_save_alert(response.error);
                            }
                            else {
                                $('#shipp-list').html(response.result);
                            }
                        }
                    }
                );
                $.getScript('shipping.js');
            });
        </script>
    </div>

    <div style="margin-bottom: 10px;"><b>Список заказов, созданных в промежуток времени (выбрать ниже).</b></div>
    <form method="post" name="dates" action="index.php">
        <?php

        $rus_montes       = array(
            1  => 'Январь',
            2  => 'Февраль',
            3  => 'Март',
            4  => 'Апрель',
            5  => 'Май',
            6  => 'Июнь',
            7  => 'Июль',
            8  => 'Август',
            9  => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь',
        );
        $monthes          = array();
        $monthes['name']  = 'month';
        $monthes['value'] = $rus_montes;

        echo $html->html_select($monthes, $mon);
        echo '&nbsp;';

        $years          = array();
        $years['name']  = 'year';
        $years['value'] = array($year => $year);

        $ext_year = $db->get_extreme_value($proc_tbl, 'min(DATE_FORMAT(created,"%Y")) as min_year, max(DATE_FORMAT(created,"%Y")) as max_year');

        if ( ! empty($ext_year->min_year)) {
            $years['value'] = array();
            for ($key = $ext_year->min_year; $key <= $ext_year->max_year; $key ++) {
                $years['value'][$key] = $key;
            }
        }

        echo $html->html_select($years, $year);
        echo '&nbsp;<input type="submit" name="set_date" value="Выбрать" />';
        ?>
    </form>
<?php

$orders_list = $orders->get_orders($mon, $year);
?>
    <h3 class="order">Новые заказы</h3>
    <div style="padding:6px;border:1px solid green;float:left;width:90%;">
        <?php

        if (isset($orders_list['new']) AND ! empty($orders_list['new'])) {

            foreach ($orders_list['new'] as $val) {
                ?>
                <div class="order">
                    <div class="title">
                        <span class="instr full" id="o_<?php echo $val->id; ?>" title="принять">&nbsp;</span>
                        <span class="instr drop" title="удалить" id="o_<?php echo $val->id; ?>">&nbsp;</span>
                        <span class="date" title="подробнее..."><?php echo $val->cdate; ?></span>
                    </div>
                    <div class="details"><?php echo $val->content; ?></div>
                </div>
                <?

            }
        }
        else {
            echo 'пусто';
        }
        ?>
    </div>
    <h3 class="order">Зафиксированные заказы</h3>
    <div style="padding:6px;border:1px solid gray;float:left;width:90%;">
        <?php

        if (isset($orders_list['ready']) AND ! empty($orders_list['ready'])) {
            foreach ($orders_list['ready'] as $val) {
                ?>
                <div class="order">
                    <div class="title">
                        <span class="instr drop" title="удалить" id="o_<?php echo $val->id; ?>">&nbsp;</span>
                        <span class="date" title="подробнее..."><?php echo $val->cdate; ?></span>
                    </div>
                    <div class="details"><?php echo $val->content; ?></div>
                </div>
                <?

            }
        }
        else {
            echo 'пусто';
        }
        ?>
    </div>
    <script type="text/javascript">
        var loc_scr = '<?php echo $loc_script;?>';

        jQuery(document).ready(function ($) {
            $.getScript('orders.js');
        });
    </script>
<?php

include(PUBPATH . 'admin/footer.php');
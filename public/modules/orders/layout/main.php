<?php defined('SYSPATH') or die('Out of');
error_reporting(E_ALL);

require_once('class.modules.php');
$module = new modules();

/*require_once('lib/class.email.php');
$mail = new email();*/

require_once('orders/class.orders.php');
$orders = new orders();

//write_log(orders_e_mail,'dbg.txt');
require_once('catalog/class.catalog.php');
$gallery = new gallery();


$page_html = '';
$tpl->assign('head_line', $page_content->p_name);
$content_res = $db->get_extreme_value($page->table, 'content', 'id=\'' . $act_page_id . '\'');
$tpl->assign('page_content', $content_res->content);

$tpl->assign('short_page', true);
//$tpl->assign('scripts',array('/js/jquery.form.js'));
include(PUBPATH . 'header.php');


$catalog_link = '?set=' . $gallery->gallery_page_id;
$tpl->assign('catalog_link', $catalog_link);


if (isset($_GET['remove_from_basket']) AND $basket_active !== false) {

    $response      = '';
    $response->err = '';

    unset($_SESSION['basket']['order'][(int) $_GET['remove_from_basket']]);

    $response->r_item = (int) $_GET['remove_from_basket'];
    $basket_sum       = 0;

    $result = $gallery->count_basket_items($_SESSION['basket']['order']);

    if ( ! empty($result)) {

        foreach ($result as $item) {

            if ($item->discount > 0) {

                $basket_sum += $item->{'sumdiscount_' . $item->id};
            }
            else {

                $basket_sum += $item->{'sum_' . $item->id};
            }
        }
    }

    $_SESSION['basket']['total'] = $basket_sum;

    $response->total = number_format(floatval($basket_sum), 1, '.', ' ');

    if ($gallery->cur_factor) {

        $response->ru_total = number_format(floatval($basket_sum * $gallery->cur_factor), 1, '.', ' ');

        $_SESSION['basket']['total'] *= $gallery->cur_factor;
    }

    $response->items = count($result);

    echo json_encode($response);
    exit;
}


/**
 * Recalculate basket
 */
if (isset($_POST['recalc'])) {

    $response      = '';
    $response->err = '';
    $response->act = 'recalc';

    try {
        $recalc_result = $orders->recalc_basket($gallery, $_POST);
    }
    catch (Exception $e) {
        $response->err = $e->getMessage();
    }

    $_SESSION['basket']['order'] = $recalc_result['basket_products'];

    if ($gallery->cur_factor) {
        $_SESSION['basket']['total'] = $recalc_result['basket_sum'] * $gallery->cur_factor;
    }
    else {
        $_SESSION['basket']['total'] = $recalc_result['basket_sum'];
    }
    $response->total = number_format(floatval($recalc_result['basket_sum']), 1, '.', ' ');
    if ($gallery->cur_factor) {
        $response->ru_total = number_format(floatval($recalc_result['basket_sum'] * $gallery->cur_factor), 1, '.', ' ');
    }
    $response->items        = $recalc_result['items'];
    $response->recalc_items = $recalc_result['recalc_items'];
    $response->kill         = $recalc_result['remove_products'];

    if (is_ajax()) {

        echo json_encode($response);
        exit;
    }
}

/**
 * Validate and Send order if validate
 *
 */

if (isset($_POST['order'])) {
    //sleep(4);
    require_once('swift/swift_required.php');
    $orders->load_settings();

    $response         = new stdClass();
    $response->err    = '';
    $response->act    = 'order';
    $response->mailed = '';
    $response->fields = '';
    $response->code   = '';

    if ($_POST['s_code'] !== $_SESSION['security_code']) {
        $response->code = 1;
    }

    $required = array(
        'fio'          => 'string',
        'umail'        => 'email',
        'phone'        => 'phone',
        'country'      => 'string',
        'city'         => 'string',
        'region'       => 'string',
        'address'      => 'string',
        'postcode'     => 'integer',
        'shiping_type' => 'integer',
        'billing_type' => 'integer',
    );

    $dictionary = array(
        'fio'          => 'Ф.И.О.',
        'umail'        => 'Email',
        'phone'        => 'Телефон',
        'country'      => 'Страна',
        'city'         => 'Город',
        'region'       => 'Область',
        'address'      => 'Адрес',
        'postcode'     => 'Индекс',
        'shiping_type' => 'Доставка',
        'billing_type' => 'Оплата',
        'comm'         => 'Примечания',
    );

    $shiping = array(
        1 => 'Почта',
        2 => 'ЕМС',
        3 => 'Транспортная компания',
    );

    $billing = array(
        1 => 'WebMoney',
        2 => 'Qiwi',
        3 => 'Яндекс деньги',
        4 => 'Перевод на карту',
    );


    $data = array();

    $validate = array();

    foreach ($_POST as $pkey => $pval) {

        if ( ! isset($dictionary[$pkey])) {
            continue;
        }

        $data[$pkey] = trim(strip_tags($pval));

        if (isset($required[$pkey])) {

            $validate[] = array(
                'value' => $pval,
                'type'  => $required[$pkey],
            );
        }
    }

    if ($orders->validate_post_order($validate) !== true) {
        $response->fields = 1;
    }

    $data['shiping_type'] = (int) $data['shiping_type'];
    $data['billing_type'] = (int) $data['billing_type'];

    if ( ! isset($shiping[$data['shiping_type']]) || ! isset($billing[$data['billing_type']])) {
        $response->fields = 1;
    }

    $data['shiping_type'] = $shiping[$data['shiping_type']];
    $data['billing_type'] = $billing[$data['billing_type']];

    $posted_name  = $data['fio'];
    $posted_umail = $data['umail'];

    try {
        Swift_Message::newInstance()->setTo(array($posted_umail => $posted_name));
    }
    catch (Exception $e) {
        $response->fields = 1;
    }

    $order_stored = false;
    $order_string = false;
    if ($response->fields == '' AND $response->code == '') {

        $order_elem = array();
        foreach ($data as $pkey => $pval) {

            $order_elem[] = array(
                'label' => isset($dictionary[$pkey]) ? $dictionary[$pkey] : $pkey,
                'value' => $pval,
            );
        }

        try {

            $posted_items = $orders->recalc_basket($gallery, $_POST);

            $order_elem[] = array('label' => 'Детали заказа', 'value' => '&nbsp;');

            $order_details = array();
            foreach ($posted_items['html_items'] as $val) {
                $order_details[] = $val;
            }

            $order_total = number_format(floatval($posted_items['basket_sum']), 1, '.', ' ');
            if ($gallery->cur_factor > 0) {
                $order_total = number_format(floatval($posted_items['basket_sum'] * $gallery->cur_factor), 1, '.', ' ');
            }

            $tpl->assign('list', array('customer' => $order_elem, 'order' => $order_details, 'total' => $order_total));
            $order_string = $tpl->fetch('order.tpl');

            $tpl->assign('staff4client', nl2br($orders->get_answer()));
            $client_order_string = $tpl->fetch('client_order.tpl');

            $order_stored = $orders->set_order(preg_replace('/<style[^>]*>(.*?)<\/style>/sim', '', $order_string));

            if ($order_stored !== false) {
                $_SESSION['basket']['order'] = array();
                $_SESSION['basket']['total'] = 0;
            }
        }
        catch (Exception $e) {
            $response->err = $e->getMessage();
        }
    }

    if (is_ajax()) {
        echo json_encode($response);
    }

    if ($order_stored !== false AND $order_string !== false) {
//		$transport = Swift_MailTransport::newInstance();
        require_once(SYSPATH . 's.php');

        $hostName = 'Kachay-Zhelezo';

        $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 587, 'tls')->setUsername(eaccount)->setPassword(ecred);
        $mailer    = Swift_Mailer::newInstance($transport);
        $message   = Swift_Message::newInstance()->setCharset('UTF-8')
                                  ->setSubject($posted_name . ' Новый заказ')
                                  ->setFrom(array($posted_umail => $posted_name))
                                  ->setTo(array(orders_e_mail => $hostName))
                                  ->setReplyTo(array($posted_umail => $posted_name))//->setBody('Детали заказа')
        ;

        $message->addPart(stripslashes($order_string), 'text/html', 'UTF-8');
        $mailer->send($message);

        // To Client
        /**
         * @var Swift_Mime_Message $message
         */
        $message = Swift_Message::newInstance()->setCharset('UTF-8')
                                ->setSubject('Ваша заявка на ' . $hostName)
                                ->setFrom(array(orders_from_mail => 'Orders ' . $hostName))
                                ->setTo(array($posted_umail => $posted_name))
                                ->setReplyTo(array(orders_e_mail => $hostName))//->setBody('Детали заказа')
        ;

        $message->addPart(stripslashes($client_order_string), 'text/html', 'UTF-8');
        $mailer->send($message);
    }

    if (is_ajax()) {
        exit;
    }
}


$items  = array();
$result = $gallery->count_basket_items(isset($_SESSION['basket']['order']) ? $_SESSION['basket']['order'] : array());

if ($result) {

    foreach ($result as $val) {

        $val->quantity = $_SESSION['basket']['order'][$val->id];

        if ($val->discount > 0) {
            $val->price_sum = number_format(floatval($val->{'sumdiscount_' . $val->id}), 1, '.', ' ');
        }
        else {
            $val->price_sum = number_format(floatval($val->{'sum_' . $val->id}), 1, '.', ' ');
        }

        if ($gallery->cur_factor > 0) {

            $val->ruprice    = number_format(floatval($val->price * $gallery->cur_factor), 1, '.', ' ');
            $val->rudiscount = number_format(floatval($val->discount * $gallery->cur_factor), 1, '.', ' ');

            if ($val->discount > 0) {
                $val->ruprice_sum = number_format(floatval($val->{'sumdiscount_' . $val->id} * $gallery->cur_factor), 1, '.', ' ');
            }
            else {
                $val->ruprice_sum = number_format(floatval($val->{'sum_' . $val->id} * $gallery->cur_factor), 1, '.', ' ');
            }
        }

        $val->price    = number_format(floatval($val->price), 1, '.', ' ');
        $val->discount = number_format(floatval($val->discount), 1, '.', ' ');

        $items[$val->id] = $val;
    }
}
$tpl->assign('items', $items);
$tpl->assign('thumbs', '/' . $gallery->photo_dir_pref . '%d' . $gallery->thumb_dir . '/');
$tpl->assign('img', md5(uniqid(rand(), true)));
$cat_nav = array();
$gallery->get_site_navigation($cat_nav, array(), 0, 0, $page_info);

$tpl->assign('catalog_menu', $cat_nav);
$tpl->assign('guidemess', nl2br($orders->get_answer()));

if ($gallery->cur_factor > 0 && isset($_SESSION['basket']['total'])) {
    $tpl->assign('ru_sum', number_format(floatval($_SESSION['basket']['total'] * $gallery->cur_factor), 1, '.', ' '));
}

$page_html .= $tpl->fetch('basket.tpl');

include(PUBPATH . 'footer.php');
echo $page_html;
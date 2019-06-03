<?php
/**
 * kz.loc
 * shippings.php
 * Date: 1/21/19
 */
error_reporting(E_ALL);
session_id();
session_start();

require_once('../../../config.php');

if ( ! defined('installed')) {

    session_destroy();
    header('Location: ' . $relative . 'install/');
    exit();
}

if ( ! is_ajax()) {

    exit();
}

$action = isset($_REQUEST['action']) ? trim(strip_tags($_REQUEST['action'])) : false;

require_once('orders/class.orders.php');

try {

    $shipps = new Discounts();

    switch ($action) {

        case 'createMethod':

            $shipps->addItem($_REQUEST);
            makeResponse($shipps->itemsHtml());
            break;

        case 'items':

            makeResponse($shipps->itemsHtml());

            break;

        case 'removeSection':

            if(!isset($_REQUEST['id'])) {

                makeResponse('', 'Bad Request');
            }

            $shipps->removeItem($_REQUEST['id']);
            makeResponse($shipps->itemsHtml());

            break;

        case 'modifySection':

            $data = array();
            if(!isset($_REQUEST['id'])) {

                makeResponse('', 'Bad Request');
            }

            $shipps->modify($_REQUEST);

            makeResponse();
            break;


    }
}
catch(Exception $e) {

    makeResponse('', $e->getMessage());
}

makeResponse();
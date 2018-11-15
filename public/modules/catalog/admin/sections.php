<?php
/**
 * kz.loc
 * sections.php
 * Date: 11/2/18
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

require_once('catalog/class.catalog.php');

try {

    $sections = new Sections();

    switch ($action) {

        case 'createSection':

            $name = isset($_REQUEST['section_name']) ? trim(strip_tags($_REQUEST['section_name'])) : '';
            if(empty($name)) {

                makeResponse('', 'Укажите название нового Раздела');
            }

            $sections->addSection($name, isset($_REQUEST['sort_order']) ? $_REQUEST['sort_order'] : 0);

            makeResponse($sections->sectionsHtml());

            break;

        case 'items':

            makeResponse($sections->sectionsHtml());

            break;

        case 'removeSection':

            if(!isset($_REQUEST['id'])) {

                makeResponse('', 'Bad Request');
            }

            $sections->removeSection($_REQUEST['id']);
            makeResponse($sections->sectionsHtml());

            break;

        case 'modifySection':

            if(!isset($_REQUEST['id'])) {

                makeResponse('', 'Bad Request');
            }

            $name = isset($_REQUEST['section_name']) ? trim(strip_tags($_REQUEST['section_name'])) : '';
            if(empty($name)) {

                makeResponse('', 'Укажите название Раздела');
            }

            $data = array(
                'name' => $name,
            );

            if(isset($_REQUEST['sort_order'])) {
                $data['sort_order'] = $_REQUEST['sort_order'];
            }

            $section_cats = array();

            if(isset($_REQUEST['section_cats'])) {

                $section_cats = preg_split('/, {0,}/', trim(strip_tags($_REQUEST['section_cats'])), -1, PREG_SPLIT_NO_EMPTY);
                array_walk($section_cats, function(&$val) {
                    $val = (int)$val;
                });
                $section_cats = array_filter($section_cats);
            }

            $data['catIds'] = $section_cats;

            $sections->modifySection($_REQUEST['id'], $data);

            break;

        case 'catList':

            makeResponse($sections->categories());

            break;
    }
}
catch(Exception $e) {

    makeResponse('', $e->getMessage());
}

makeResponse();
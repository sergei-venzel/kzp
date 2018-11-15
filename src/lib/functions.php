<?php

function make_path($path = '')
{

    $real_path = str_replace(base, '', $path);
    $real_path = substr($real_path, 0, - (strlen(strrchr($real_path, '/')) - 1));
    $rel_path  = '';
    for ($i = 0; $i < substr_count($real_path, '/'); $i ++) {
        $rel_path .= '../';
    }

    return $rel_path;
}


function store_cache($page_html, $str = '', $preview = false)
{

    $stored_file = cache_file_name($str);
    if (defined('caching') && caching == 1 && ! $preview) {

        if ( ! file_exists($stored_file) || time() - filemtime($stored_file) >= cache_time) {

            $fp = @fopen($stored_file, 'w');

            if (@flock($fp, LOCK_EX)) {
                fwrite($fp, $page_html);
                flock($fp, LOCK_UN);
            }
            @fclose($fp);
        }
    }
}

function cache_file_name($str = '', $root_path = '')
{

    $str = $root_path . cache_store . md5($str) . '.html';

    return $str;
}

function logger($var, $destination = false)
{
    if ($destination === false) {
        echo '<pre>';
        var_export($var);
        echo '</pre>';
    }
    else {
        error_log(var_export($var, true) . "\r\n", 3, base . $destination);
    }
}

function is_ajax()
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        return true;
    }

    return false;
}

function w2u_path($str)
{
    return str_replace('\\', '/', $str);
}

function json_1251($object)
{

    if ( ! is_json_object($object)) {
        return $object;
    }

    if (is_object($object)) {
        $ovars = get_object_vars($object);
        //logger($object,'dbg.txt');
        $str = '{';
        if ( ! empty($ovars)) {
            $tmp = array();
            foreach ($ovars as $key => $val) {
                if ( ! is_json_object($val)) {
                    $tmp[] = '\'' . $key . '\':\'' . str_replace(array("\r", "\n", "\r\n", "\t", '\''), array(
                            '',
                            '',
                            '',
                            '',
                            '&apos;',
                        ), $val) . '\'';
                }
                else {
                    $tmp[] = '\'' . $key . '\':' . json_1251($val) . '';
                }
            }

            $str .= join(',', $tmp);
        }

        $str .= '}';
    }

    if (is_array($object)) {
        $ovars = $object;
        $str   = '[';
        if ( ! empty($ovars)) {
            $tmp = array();
            foreach ($ovars as $key => $val) {
                if ( ! is_json_object($val)) {
                    $tmp[] = '\'' . str_replace(array("\r", "\n", "\r\n", "\t", '\''), array(
                            '',
                            '',
                            '',
                            '',
                            '&apos;',
                        ), $val) . '\'';
                }
                else {
                    $tmp[] = json_1251($val);
                }
            }

            $str .= join(',', $tmp);
        }

        $str .= ']';
        //return $str;
    }

    return $str;
}

function is_json_object($var)
{
    if (is_object($var) OR is_array($var)) {
        return true;
    }

    return false;
}

function force_int($str, $signed = false)
{
    if ( ! ctype_digit($str)) {
        $str = (int) $str;
    }

    if ( ! $signed) {
        $str = abs($str);
    }

    return $str;
}

function _esc($str)
{
    if (get_magic_quotes_gpc()) {
        return stripslashes($str);
    }

    return $str;
}

function sanitizeDirPath($path)
{
    $path = trim($path);

    if ('/' === $path || '' === $path) {
        return $path;
    }

    if (0 === strpos($path, '/')) {
        $path = substr($path, 1);
    }

    if ((strlen($path) - 1) === strrpos($path, '/')) {
        $path = substr($path, 0, - 1);
    }

    return $path;
}

function readItems($path)
{

    $dir    = PUBPATH . sanitizeDirPath($path);
    $result = array();
    $list   = scandir($dir);
    foreach ($list as $src) {
        if ('.' == $src || '..' == $src) {
            continue;
        }
        $result[] = $path . $src;
    }

    return $result;
}

function dropFile($path)
{

    $file = PUBPATH . sanitizeDirPath($path);
    if (is_file($file)) {
        return unlink($file);
    }

    return false;
}

function resume($data)
{
    echo json_encode($data);
    exit();
}

function resumeAjax($result, $status = 'ok')
{

    $answer = array(
        'status' => $status,
        'result' => $result,
    );
    echo json_encode($answer, JSON_NUMERIC_CHECK);
    exit;
}

function makeResponse($result = 'ok', $error = false)
{
    $answer = array(
        'error'  => $error,
        'result' => $result,
    );

    header('Content-Type: application/json');
    echo json_encode($answer, JSON_NUMERIC_CHECK);
    exit;
}

function template($tpl_path, $vars = array())
{
    $tpl_path = $tpl_path . '.php';
    extract($vars, EXTR_REFS);
    ob_start();
    if(!include($tpl_path)) {
        throw new Exception('Can not find template ' . $tpl_path);
    }
    $contents = ob_get_contents();
    ob_end_clean();

    return $contents;
}
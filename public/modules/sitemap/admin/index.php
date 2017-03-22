<?php
// Auth LAYER
error_reporting(E_ALL ^ E_NOTICE);
session_id();
session_start();

require_once('../../../config.php');

$relative = RELATIVE_PATH;

if ( ! defined('installed')) {

    session_destroy();
    header('Location: ' . $relative . 'install/');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['user']);
}

if ($_SESSION['user'] != 'admin') {
    session_destroy();
    header('Location: ' . $relative . 'admin/auth.php');
    exit();
}
// Auth LAYER
require_once(base . 'lib/debug.php');
require_once(base . 'lib/class.db.php');
$db = new db();

require_once('sitemap/class.sitemap.php');
$sitemap_module = new sitemap();

if (is_ajax()) {
    $response       = new stdClass();
    $response->err  = '';
    $response->html = '';

    if (isset($_GET['new_gen'])) {
        try {
            $sitemap_module->create_file();
            $response->html = $sitemap_module->map_exist();
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

include(PUBPATH . 'admin/header.php');

?>
    <script type="text/javascript" charset="UTF-8">
        $.getScript('sitemap.js');
    </script>

    <h2>Генерация файла sitemap.xml</h2>

    <div>
        <div class="err_mess" style="position:relative;"></div>
        <div style="margin: 0px 0 10px 0;"><input type="button" value="генерация" id="gen"/></div>
        <div id="stm_file"><? echo $sitemap_module->map_exist(); ?></div>
    </div>

<?

include(PUBPATH . 'admin/footer.php');

$db->db_close($db->db_conn);
?>
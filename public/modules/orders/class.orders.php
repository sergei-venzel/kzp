<?php

class orders_data
{
    /*
    * As many as You need variables But don`t forget Fill in the tables_data() assosiated data (table structure)
    */
    var $main_table = 'orders';


    /*
    * if you need any directories to work, use constructor to define them 
    */

    function tables_data()
    {

        $t_data = array(
            'orders' => array(
                'fields' => array(
                    'id'      => 'int(11) NOT NULL auto_increment',
                    'content' => 'text NOT NULL',
                    'created' => 'timestamp NOT NULL default \'0000-00-00 00:00:00\'',
                    'status'  => 'tinyint(1) NOT NULL default \'0\'',
                ),
                'keys'   => array(
                    'PRIMARY KEY  (`id`)',
                ),
                'extra'  => 'ENGINE=MyISAM DEFAULT CHARSET=utf8',
            ),
        );

        return $t_data;
    }
}

class orders
{
    var       $name               = 'Механизм заказов';

    var       $nav_name           = 'Заказы';

    var       $module_description = 'Модуль для работы с заказами';

    var       $settings_script    = 'settings.php';

    var       $modules_page_id    = false;

    private   $answer_text_file   = null;

    var       $class_constants    = array(
        'e_mail'    => array(
            'type'  => 'email',
            'value' => 'sergei.venzel@gmail.com',
            'label' => 'E-mail "ПОЛУЧАТЕЛЯ" заказа',
        ),
        'from_mail' => array(
            'type'  => 'email',
            'value' => 'sergei.venzel@gmail.com',
            'label' => 'E-mail "ОТПРАВИТЕЛЯ"',
        ),
    );

    protected $allowed_tags       = '<div>,<p>,<span>,<b>,<i>,<u>,<br/>,<table>,<tr>,<td>';


    function __construct()
    {
        $tables = get_class_vars(__CLASS__ . '_data');
        if ( ! empty($tables)) {

            foreach ($tables as $key => $val) {
                $this->{$key} = $val;
            }
        }

        $tmp = array();
        foreach ($this->class_constants as $key => $val) {
            $tmp[__CLASS__ . '_' . $key] = $val;
        }

        $this->class_constants = $tmp;

        $this->modules_page_id = $this->module_page_id();

        $answer_file = PUBPATH . 'files/' . __CLASS__ . '/answer_text.inc';
        if (file_exists($answer_file)) {
            $this->answer_text_file = $answer_file;
        }
        else {
            if ($fp = @fopen($answer_file, 'w+')) {
                $this->answer_text_file = $answer_file;
                @fclose($fp);
            }
        }
    }


    function can_use()
    {

    }


    function module_page_id()
    {
        global $db;

        $page_id = $db->get_extreme_value(ptbl, 'id', 'publish=1 and layout=\'' . __CLASS__ . '\'');
        if ($page_id) {
            return $page_id->id;
        }
        else {
            return false;
        }
    }


    function load_settings()
    {

        if (file_exists(MODPATH . __CLASS__ . '/' . $this->settings_script)) {
            require_once($this->settings_script);
        }

        foreach ($this->class_constants as $key => $val) {
            if ( ! defined(__CLASS__ . '_' . $key)) {
                define(__CLASS__ . '_' . $key, $val['value']);
            }
        }
    }


    public function set_answer($str)
    {
        if ( ! $fp = @fopen($this->answer_text_file, 'w')) {
            throw new Exception('Ресурс недоступен для записи');
        }

        if ( ! $this->answer_text_file) {
            throw new Exception('Технический сбой - не найден ресурс для записи.');
        }


        if (@flock($fp, LOCK_EX)) {
            $str = trim(strip_tags($str));
            if ( ! fwrite($fp, $str)) {
                throw new Exception('Запись невозможна');
            }
            flock($fp, LOCK_UN);
        }
        @fclose($fp);

        return true;
    }


    public function get_answer()
    {
        $str = '';

        if ($this->answer_text_file) {
            $str = file_get_contents($this->answer_text_file);
        }

        return $str;
    }


    function get_orders($month = false, $year = false)
    {

        global $db;
        $orders = array();
        if ($month === false OR $year === false) {
            $month = date('n');
            $year  = date('Y');
        }

        $from_date = date('YmdHis', mktime(0, 0, 0, ($month), 1, $year));
        $to_date   = date('YmdHis', mktime(0, 0, 0, ($month + 1), 1, $year));

        $where = ' created >= \'' . $from_date . '\' and created <= \'' . $to_date . '\' order by created DESC';
        //dbg::logger($where,'dbg.txt');
        if ($list_orders = $db->select_obj($this->main_table, 'id,content,DATE_FORMAT(created,"%d.%m.%Y %H:%i") as cdate,status', $where)) {
            $orders['ready'] = array();
            $orders['new']   = array();

            foreach ($list_orders as $val) {
                if ($val->status == 1) {
                    $orders['ready'][] = $val;
                }
                else {
                    $orders['new'][] = $val;
                }
            }
        }

        return $orders;
    }


    function set_order($order_details = '')
    {
        global $db;

        $result = false;

        $order_details = trim(strip_tags($order_details, $this->allowed_tags));

        if (is_string($order_details) AND $order_details != '') {
            $ins_data          = '';
            $ins_data->content = $order_details;
            $ins_data->created = 'NOW()';

            if ($db->insert_obj($this->main_table, $ins_data)) {
                $result = $ins_data;
            }
        }

        return $result;
    }


    function validate_post_order($data = array())
    {
        $valid = true;

        if ( ! empty($data)) {

            foreach ($data as $val) {

                $str = trim(strip_tags($val['value']));

                if (empty($str)) {
                    return false;
                }

                if ( ! isset($val['type'])) {
                    $val['type'] = 'string';
                }

                switch ($val['type']) {
                    case 'string':
                        if (preg_match('/[<>?`~\\t\\r\\n$=+&@#*]/sim', $str)) {
                            $valid = false;
                        }
                        break;

                    case 'email':

                        if ( ! $str = filter_var($str, FILTER_VALIDATE_EMAIL)) {

                            $valid = false;
                        }
                        break;

                    case 'phone':

                        $valid = ! ! preg_match('/^\+{0,1}[(]{0,1}[0-9]{1,4}[)]{0,1}[- 0-9]+$/sim', $str);
                        break;

                    case 'integer':
                        if (is_bool($str)) {
                            $str = 0;
                        }
                        $str = (int) $str;
                        if (0 >= $str) {
                            $valid = false;
                        }
                        break;

                    default:
                        if (preg_match('/[<>?`~\\t\\r\\n$=+&@#*]/sim', $str)) {
                            $valid = false;
                        }
                }
            }
        }

        return $valid;
    }


    /**
     * @param gallery $catalog_obj
     * @param array   $data
     *
     * @return array
     * @throws Exception
     */
    function recalc_basket($catalog_obj, $data = array())
    {
        $recalc_basket_products = array();
        $remove_products        = array();

        if (isset($data['prod_id']) AND ! empty($data['prod_id'])) {
            foreach ($data['prod_id'] as $key => $val) {
                if (isset($data['q'][$key]) AND (int) $data['q'][$key] > 0) {
                    $recalc_basket_products[$val] = (int) $data['q'][$key];
                }
                else {
                    $remove_products[] = $val;
                }
            }
        }

        $basket_sum   = 0;
        $recalc_items = array();
        $html_items   = array();
        $result       = $catalog_obj->count_basket_items($recalc_basket_products);

        if ($result === false) {
            throw new Exception('Technical warn in ' . __METHOD__);
        }

        if ($catalog_obj->cur_factor <= 0) {
            $catalog_obj->cur_factor = 1;
        }

        if ( ! empty($result)) {

            foreach ($result as $item) {

                $tmp     = new stdClass();
                $tmp->id = $item->id;

                if ($item->discount > 0) {

                    $basket_sum += $item->{'sumdiscount_' . $item->id};
                    $tmp->value = number_format(floatval($item->{'sumdiscount_' . $item->id}) * $catalog_obj->cur_factor, 1, '.', ' ');
                }
                else {

                    $basket_sum += $item->{'sum_' . $item->id};
                    $tmp->value = number_format(floatval($item->{'sum_' . $item->id}) * $catalog_obj->cur_factor, 1, '.', ' ');
                }

                $recalc_items[] = $tmp;

                $item->quantity = $recalc_basket_products[$item->id];

                if ($item->discount > 0) {

                    $item->price_sum   = number_format(floatval($item->{'sumdiscount_' . $item->id}), 1, '.', ' ');
                    $item->ruprice_sum = number_format(floatval($item->{'sumdiscount_' . $item->id}) * $catalog_obj->cur_factor, 1, '.', ' ');
                    $item->ruprice     = number_format(floatval($item->discount) * $catalog_obj->cur_factor, 1, '.', ' ');
                }
                else {

                    $item->price_sum   = number_format(floatval($item->{'sum_' . $item->id}), 1, '.', ' ');
                    $item->ruprice_sum = number_format(floatval($item->{'sum_' . $item->id}) * $catalog_obj->cur_factor, 1, '.', ' ');
                    $item->ruprice     = number_format(floatval($item->price) * $catalog_obj->cur_factor, 1, '.', ' ');
                }

                $item->price    = number_format(floatval($item->price), 1, '.', ' ');
                $item->discount = number_format(floatval($item->discount), 1, '.', ' ');

                $html_items[$item->id] = $item;
            }
        }

        $result_array = array(
            'basket_products' => $recalc_basket_products,
            'remove_products' => $remove_products,
            'basket_sum'      => $basket_sum,
            'items'           => count($result),
            'recalc_items'    => $recalc_items,
            'html_items'      => $html_items,
        );

        return $result_array;
    }
}

class Shippings
{
    /**
     * @var \db|null $DB
     */
    protected      $db             = null;

    protected      $tpl_dir        = 'orders/admin/views/';

    private static $shipp_db_table = 'shipping';


    public function __construct()
    {
        $this->db = Registry::getInstance()->get('db');
    }


    public function addShippingMethod(array $data)
    {
        $name = isset($data['name']) ? trim(strip_tags($data['name'])) : '';

        if (empty($name)) {
            throw new Exception('Укажите Способ доставки');
        }

        $cost = isset($data['cost']) ? (float) str_replace(',', '.', $data['cost']) : 0;

        $description = isset($data['description']) ? trim(strip_tags($data['description'])) : '';

        $query = 'INSERT INTO `' . self::$shipp_db_table . '` 
        (`name`, `cost`, `description`) 
        VALUES (\'' . $this->db->esc($name) . '\', \'' . $cost . '\', \'' . $this->db->esc($description) . '\' )';

        $this->db->dbQuery($query);
    }


    public function shippingsList($multiplier = 1)
    {
        $query  = 'SELECT `id`, `name`, (`cost` * '. (int) $multiplier .') AS `cost`, `description` FROM `' . self::$shipp_db_table . '`';
        $result = array();
        $res    = $this->db->dbQuery($query);
        while ($row = $res->fetch_assoc()) {
            $result[] = $row;
        }
        $res->free_result();

        return $result;
    }


    public function shippingItemsHtml()
    {
        $params = array(
            'items' => $this->shippingsList(),
        );

        return template($this->tpl_dir . 'list', $params);
    }


    public function removeShipp($id)
    {
        $this->db->dbQuery('DELETE FROM `' . self::$shipp_db_table . '` WHERE `id`=\'' . (int) $id . '\'');
    }


    public function modifyShipp($data)
    {
        $id = (int) $data['id'];

        $name = isset($data['name']) ? trim(strip_tags($data['name'])) : '';

        if (empty($name)) {
            throw new Exception('Укажите Способ доставки');
        }

        $cost = isset($data['cost']) ? (float) str_replace(',', '.', $data['cost']) : 0;

        $description = isset($data['description']) ? trim(strip_tags($data['description'])) : '';

        $query = 'UPDATE `' . self::$shipp_db_table . '` SET 
        `name` = \'' . $this->db->esc($name) . '\', 
        `cost` = \'' . $cost . '\', 
        `description`= \'' . $this->db->esc($description) . '\' 
        WHERE `id`=\'' . $id . '\'';

        $this->db->dbQuery($query);
    }
}

?>
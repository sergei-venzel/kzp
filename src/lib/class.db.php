<?php

class db extends mysqli
{
    const RESOURCE_TYPE = 'mysql link';

    /**
     * @var db $instance
     */
    protected static $instance    = null;

    public           $db_conn     = null;

    protected        $db_login    = db_login;

    protected        $db_pswd     = db_pswd;

    protected        $db_host     = db_host;

    protected        $db_name     = db_name;

    private          $query_cache = array();

    private          $query_arr   = array();


    public function __construct()
    {
        //$this->createConnection();
        parent::__construct(db_host, db_login, db_pswd);
        $this->select_db($this->db_name);
    }

    public static function getInstance()
    {
        if ( ! self::$instance instanceof db) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    private function createConnection()
    {
        $this->db_conn = @mysql_connect(db_host, db_login, db_pswd);
        if ( ! is_resource($this->db_conn) && mysql_errno() == '1040') {
            sleep(1);
            $this->db_conn = @mysql_connect(db_host, db_login, db_pswd);
        }

        $this->selet_db();

        return $this->db_conn;
    }


    public function __destruct()
    {
        //is_resource($this->db_conn) and mysql_close($this->db_conn);
        //$this->db_close($this->db_conn);
        //$this->query_cache = array();
        /*logger(array_keys($this->query_cache),'dbg.inc');
        logger($this->query_arr,'dbg.inc');*/
//        $this->db_close();
    }


    private function _connect()
    {

        $resource = null;
        if ( ! is_resource($this->db_conn)) {
            $resource = @mysql_connect(db_host, db_login, db_pswd);
            if ( ! is_resource($resource) AND mysql_errno() == '1040') {
                sleep(1);
                $resource = @mysql_connect(db_host, db_login, db_pswd);
            }
        }

        return $resource;
    }


    private function selet_db()
    {
        if (is_resource($this->db_conn)) {
            if (@mysql_select_db($this->db_name, $this->db_conn)) {
                $this->m_query('SET NAMES utf8'/* . DBCOLL*/);
            }
            else {
                $this->err_report(mysql_error($this->db_conn), "\r\n" . date('r') . ' - Internal problem with THIS DB: ');
            }
        }
        else {
            $this->err_report(mysql_errno() . ': ' . mysql_error(), "\r\n" . date('r') . ' - Connection to DB: ');
        }
    }


    public function up_connection()
    {
//        if ( ! is_resource($this->db_conn) || get_resource_type($this->db_conn) != self::RESOURCE_TYPE) {
//            return $this->createConnection();
//        }
//
//        return $this->db_conn;
        return true;
    }


    function common_tables()
    {

        $com_tbls = array(
            ptbl => array(
                'query' => '
                    CREATE TABLE `pages` (
					`id` int(11) NOT NULL auto_increment,
					`rid` int(11) NOT NULL default \'0\',
					`pid` int(11) NOT NULL default \'0\',
					`sort_order` int(11) NOT NULL default \'1\',
					`publish` tinyint(1) NOT NULL default \'0\',
					`p_name` varchar(255) NOT NULL default \'\',
					`p_title` varchar(255) NOT NULL default \'\',
					`a_title` varchar(255) NOT NULL default \'\',
					`p_keywords` varchar(255) NOT NULL default \'\',
					`p_description` varchar(255) NOT NULL default \'\',
					`content` mediumtext NOT NULL,
					`layout` varchar(40) NOT NULL default \'\',
					`main_page` tinyint(1) NOT NULL default \'0\',
					`bg` varchar(37) NOT NULL default \'\',
					`top_menu` tinyint(1) NOT NULL default \'0\',
					`external` tinyint(1) NOT NULL default \'0\',
					`ext_link` varchar(255) NOT NULL default \'\',
					PRIMARY KEY  (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=' . DBCOLL,
            ),

            'users'   => array(
                'query' => '
                    CREATE TABLE `users` (
					`id` int(11) NOT NULL auto_increment,
					`a_name` varchar(32) NOT NULL default \'\',
					`a_ind` varchar(32) NOT NULL default \'\',
					PRIMARY KEY  (`id`),
					UNIQUE (`a_name`)
					) ENGINE=MyISAM DEFAULT CHARSET=' . DBCOLL,
            ),
            'modules' => array(
                'query' => '
					CREATE TABLE `modules` (
					`id` smallint(6) NOT NULL auto_increment,
					`module_name` varchar(255) NOT NULL default \'\',
					`nav_name` varchar(100) NOT NULL default \'\',
					`menu_item` tinyint(1) NOT NULL default \'1\',
					PRIMARY KEY  (`id`),
					UNIQUE KEY `module_name` (`module_name`)
					) ENGINE=MyISAM DEFAULT CHARSET=' . DBCOLL,
            ),
        );

        return $com_tbls;
    }


    private function free_result($result)
    {
        if (is_resource($result)) {
            @mysql_free_result($result);
        }
    }


    public function quote_smart($value)
    {
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }

        if ( ! is_numeric($value)) {
            $value = "'" . mysql_real_escape_string($value) . "'";
        }

        return $value;
    }


    public function esc($val)
    {
        return mysql_real_escape_string($val);
    }


    function err_report($err, $descr = '')
    {

        $headers     = 'From: ' . support . "\r\n" .
                       'Reply-To: ' . support . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();
        $from_server = 'Message from: ' . $_SERVER['HTTP_HOST'] . "\r\n";
        if (PRODUCTION === false) {
            throw new Exception($descr . $err);
        }
        else {
            error_log($descr . $err);
        }
    }


    public function db_close()
    {
        if (is_resource($this->db_conn)) {
            @mysql_close($this->db_conn);
            $this->db_conn = null;
        }
    }


    public function select_obj($table, $fields = '*', $where = '', $place = '', $extra = '')
    {

        $return_result = false;
        $where != '' ? $clause = ' WHERE ' . $where : $clause = '';

        $query = 'SELECT ' . $fields . ' FROM ' . $table . ' ' . $clause . ' ' . $extra;
        //$t = FirePHP::getInstance(TRUE)->fb($query);
        if ($res = $this->m_query($query, __METHOD__)) {

            $res_arr = array();
            while ($result = mysql_fetch_object($res)) {

                $res_arr[] = $result;
            }
            $this->free_result($res);

            if ( ! empty($res_arr)) {
                $return_result = $res_arr;
            }
        }

        return $return_result;
    }


    public function selectAssoc($query)
    {
        $result = array();
        if ($res = $this->m_query($query)) {

            while ($row = mysql_fetch_assoc($res)) {
                $result[] = $row;
            }

            $this->free_result($res);
        }

        if ( ! empty($result)) {
            return $result;
        }

        return false;
    }


    public function _query($query = '', $place = '')
    {

        $res_arr = array();
        if ($res = $this->m_query($query)) {
            while ($result = mysql_fetch_object($res)) {
                $res_arr[] = $result;
            }

            $this->free_result($res);

            if ( ! empty($res_arr)) {
                return $res_arr;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }


    public function udf_query($query)
    {
        $r_result = false;

        if ($res = $this->m_query($query, __METHOD__)) {
            if (is_resource($res)) {
                $tmp = array();
                while ($result = mysql_fetch_object($res)) {

                    $tmp[] = $result;
                }
                $this->free_result($res);
                if ( ! empty($tmp)) {
                    $r_result = $tmp;
                }
            }
            else {
                //$a = FirePHP::getInstance(TRUE)->fb(mysql_affected_rows());
                //$r = FirePHP::getInstance(TRUE)->fb(gettype($res));
                $aff_rows = mysql_affected_rows();
                if ($aff_rows > 0) {
                    $r_result = $aff_rows;
                }
                else {
                    $r_result = true;
                }
            }
        }

        return $r_result;
    }


    /**
     * @param   string      $table
     * @param bool|stdClass $data
     * @param null          $ignore
     *
     * @return bool|int
     * @throws Exception
     */
    public function insert_obj($table, $data = false, $ignore = null)
    {

        if (is_object($data)) {
            $data_arr = get_object_vars($data);
            if (count($data_arr)) {

                $fields = array();
                $values = array();
                foreach ($data_arr as $key => $val) {
                    $fields[] = '`' . $key . '`';
                    if ($val == 'NOW()') {
                        $values[] = $val;
                    }
                    else {
                        $values[] = $this->quote_smart($val);
                    }
                }

                $ignore_statement = '';
                if ($ignore) {
                    $ignore_statement = 'IGNORE';
                }

                $query = 'INSERT ' . $ignore_statement . ' INTO ' . $table . ' (' . join(',', $fields) . ') VALUES (' . join(',', $values) . ')';
                if ($res = @mysql_query($query)) {
                    $ins_id = @mysql_insert_id();
                    if ($ins_id === 0) {
                        $ins_id = true;
                    }

                    $this->free_result($res);

                    return $ins_id;
                }
                else {
                    if (PRODUCTION === false) {
                        throw new Exception(mysql_error() . "\r\n\r\n" . $query);
                    }
                    else {
                        $this->err_report(mysql_error() . "\r\n\r\n" . $query, 'INSERT problem in ' . __CLASS__ . '->' . __FUNCTION__ . ': ' . "\r\n");

                        return false;
                    }
                }
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }


    function update_obj($table, $data = false, $where = '', $ignore = null)
    {

        if (is_object($data)) {

            $where != '' ? $clause = ' WHERE ' . $where : $clause = '';
            $data_arr = get_object_vars($data);
            if (count($data_arr)) {

                foreach ($data_arr as $key => $val) {

                    $update[] = '`' . $key . '`=' . $this->quote_smart($val) . '';
                }

                $ignore_statement = '';
                if ($ignore) {
                    $ignore_statement = 'IGNORE';
                }

                $query = 'UPDATE ' . $ignore_statement . ' ' . $table . ' SET ' . join(', ', $update) . ' ' . $clause;
                if ($res = @mysql_query($query)) {
                    $aff_rows = @mysql_affected_rows();
                    $this->free_result($res);
                    if ($aff_rows) {
                        return $aff_rows;
                    }
                    else {
                        return true;
                    }
                }
                else {
                    $this->err_report(mysql_error() . "\r\n\r\n" . $query, 'UPDATE problem in ' . __METHOD__ . ': ' . "\r\n");

                    return false;
                }
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }


    function update_int_obj($table, $data = false, $where = '')
    {

        if (is_object($data)) {

            $where != '' ? $clause = ' WHERE ' . $where : $clause = '';
            $data_arr = get_object_vars($data);
            if (count($data_arr)) {

                foreach ($data_arr as $key => $val) {

                    $update[] = '`' . $key . '`=' . $val;
                }
                $query = 'UPDATE ' . $table . ' SET ' . join(', ', $update) . ' ' . $clause;
                if ($res = @mysql_query($query)) {
                    $aff_rows = @mysql_affected_rows();
                    $this->free_result($res);
                    if ($aff_rows) {
                        return $aff_rows;
                    }
                    else {
                        return true;
                    }
                }
                else {
                    $this->err_report(mysql_error() . "\r\n\r\n" . $query, 'UPDATE problem in ' . __METHOD__ . ': ' . "\r\n");

                    return false;
                }
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }


    function remove_obj($table, $clause = '')
    {

        $query = 'delete from ' . $table . ' ' . $clause;
        if (@mysql_query($query)) {
            return true;
        }
        else {
            $this->err_report(mysql_error() . "\r\n\r\n" . $query, 'DELETE problem in ' . __CLASS__ . '->' . __FUNCTION__ . ': ' . "\r\n");

            return false;
        }
    }


    function create_table($data = array())
    {

        //CREATE TABLE `spec_1` (`item_id` INT( 11 ) NOT NULL);
        if (count($data)) {

            if ($data['tbl']) {

                $query = 'CREATE TABLE `' . $data['tbl'] . '` ';

                if (count($data['fields'])) {

                    $query .= '(' . join(', ', $data['fields']) . ')';

                    if ($res = @mysql_query($query)) {
                        $this->free_result($res);

                        return true;
                    }
                    else {
                        $this->err_report(mysql_error() . "\r\n\r\n" . $query, 'CREATE TABLE problem in ' . __METHOD__ . ': ' . "\r\n");

                        return false;
                    }
                }
                else {
                    return false;
                }
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }


    private function create_table_by_format($table_name, $format)
    {
        if (is_array($format['fields']) && ! empty($format['fields'])) {

            $query = 'CREATE TABLE `' . $table_name . '` (';

            $tmp = array();
            foreach ($format['fields'] as $f => $p) {
                $tmp[] = '`' . $f . '` ' . $p;
            }

            $query .= join(',', $tmp);

            if ( ! empty($format['keys'])) {
                $query .= ',' . join(',', $format['keys']);
            }

            $query .= ')';

            if ( ! empty($format['extra'])) {
                $query .= ' ' . $format['extra'];
            }

            // NOW - query
            if ($res = $this->m_query($query, __METHOD__)) {
                $this->free_result($res);
            }
        }

        return true;
    }


    function alter_table($tbl_name = '', $data = array(), $type = '')
    {

        if (count($data)) {

            $query = '';

            switch ($type) {

                case 'ADD':
                    $query = 'ALTER TABLE `' . $tbl_name . '` ADD ' . join(', ADD ', $data);
                    break;

                case 'DROP':
                    $query = 'ALTER TABLE `' . $tbl_name . '` DROP ' . join(', DROP ', $data);
                    break;

                case 'ADD_INDEX':
                    $query = 'ALTER TABLE `' . $tbl_name . '` ADD INDEX `i_' . $tbl_name . '` (' . join(', ', $data) . ')';
                    break;

                case 'CHANGE':
                    $query = 'ALTER TABLE `' . $tbl_name . '` ' . join(' , ', $data);
                    break;
            }

            if ($query == '') {
                return false;
            }

            if ($res = $this->m_query($query, __METHOD__)) {
                $this->free_result($res);

                return true;
            }
            else {
                $this->err_report(mysql_error() . "\r\n\r\n" . $query, 'ALTER TABLE problem in ' . __METHOD__ . ': ' . "\r\n");

                return false;
            }
        }
        else {
            return false;
        }
    }


    function drop_table($tbl_name = '')
    {

        $query = 'DROP TABLE IF EXISTS `' . $tbl_name . '`';
        if (@mysql_query($query)) {
            return true;
        }
        else {
            $this->err_report(mysql_error() . "\r\n\r\n" . $query, 'DROP TABLE problem in ' . __CLASS__ . '->' . __FUNCTION__ . ': ' . "\r\n");

            return false;
        }
    }


    private function list_tables()
    {
        $tables = array();

        $query = 'SHOW TABLES FROM `' . $this->db_name . '`';
        $res   = $this->m_query($query, __METHOD__);
        while ($result = mysql_fetch_object($res)) {
            $tables[] = $result->{Tables_in_ . $this->db_name};
        }
        $this->free_result($res);

        return $tables;
    }


    private function get_existing_fields($table_name)
    {
        $fields = array();
        $query  = 'SHOW FIELDS FROM `' . $table_name . '`';
        $res    = $this->m_query($query, __METHOD__);
        while ($result = mysql_fetch_object($res)) {
            $fields[] = $result->Field;
        }
        $this->free_result($res);
        if ( ! empty($fields)) {
            return $fields;
        }

        return false;
    }


    public function check_tables($table_name, $format)
    {

    }


    public function check_fields($table_name, $format = array())
    {
        //$t = FirePHP::getInstance(TRUE)->fb($tables);
        $existing_tables = $this->list_tables();
        if ( ! in_array($table_name, $existing_tables)) {
            $this->create_table_by_format($table_name, $format);
        }
        else {
            $to_drop         = array();
            $to_add          = array();
            $declared_fields = array();
            if (isset($format['fields'])) {
                $declared_fields = $format['fields'];
            }
            if ($existing_fields = $this->get_existing_fields($table_name)) {
                if ( ! empty($declared_fields)) {
                    foreach ($declared_fields as $key => $val) {
                        if ( ! in_array($key, $existing_fields)) {
                            $to_add[] = '`' . $key . '` ' . $val;
                        }
                    }

                    foreach ($existing_fields as $val) {
                        if ( ! isset($declared_fields[$val])) {
                            $to_drop[] = '`' . $val . '`';
                        }
                    }
                }
            }

            if ( ! empty($to_drop)) {
                if ( ! $this->alter_table($table_name, $to_drop, 'DROP')) {
                    throw new Exception(__METHOD__ . ': can`t drop fields');
                }
            }

            if ( ! empty($to_add)) {
                if ( ! $this->alter_table($table_name, $to_add, 'ADD')) {
                    throw new Exception(__METHOD__ . ': can`t add fields');
                }
            }
        }

        return true;
    }


    function get_extreme_value($tbl = '', $value_name = '', $where = false, $place = '')
    {

        if ($where) {
            $where .= ' LIMIT 0,1';
        }
        else {
            $where = '1 LIMIT 0,1';
        }

        $result = $this->select_obj($tbl, $value_name, $where, $place);

        if ($result) {
            return $result[0];
        }
        else {
            return false;
        }
    }


    function empty_table($tbl_name = '')
    {

        $result = false;
        $query  = 'TRUNCATE TABLE `' . $tbl_name . '`';
        if ($this->m_query($query, __METHOD__)) {
            $result = true;
        }

        return $result;
    }


    public function dbQuery($query)
    {
        return $this->query_result($query);
    }


    private function m_query($query, $method = '')
    {
        /*logger('-'.mysql_thread_id($this->db_conn),'dbg.inc');
        */
        //$this->up_connection();
        /*if(!is_resource($this->db_conn) || !mysql_ping($this->db_conn)) {
            $this->createConnection();
        }*/

        return $this->query_result($query, $method);
    }


    private function query_result($query, $method = '')
    {
        $res = false;
        if ( ! $res = @mysql_query($query)) {
            $errMess = mysql_error();
            $this->err_report($errMess . "\r\n\r\n" . $query, '--QUERY problem in ' . $method . ': ' . "\r\n");
            throw new Exception($method . ': ' . $errMess);
            /*if(mysql_errno() == 1045) {

                $this->createConnection();
                if(!$res = mysql_query($query)) {

                    $this->err_report($errMess."\r\n\r\n".$query,'--QUERY problem in '.$method.': '."\r\n");
                    throw new Exception($method.': ' . $errMess);
                }
            }*/
        }

        return $res;
    }


    private function query_hash($sql)
    {
        return sha1($sql);
    }
}

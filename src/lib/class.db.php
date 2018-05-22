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


    public function __construct($host, $user, $pswd, $dbname = '', $port = null)
    {
        //$this->createConnection();
        parent::__construct($host, $user, $pswd);
        $this->select_db($this->db_name);
    }


    public static function getInstance($host, $userName, $passwd, $dbName = null, $port = null)
    {
//        if ( ! self::$instance instanceof db) {
//            self::$instance = new self();
//        }
//
//        return self::$instance;
        $connection = new Db($host, $userName, $passwd, $dbName, $port);
        if (mysqli_connect_error()) {
//			new Error (mysqli_connect_error(), mysqli_connect_errno());
            throw new MySqliException(mysqli_connect_error(), mysqli_connect_errno(), '');
        }
        if ( ! $connection->set_charset("utf8")) {
            //new Error (mysqli_connect_error(), mysqli_connect_errno());
            throw new MySqliException(mysqli_connect_error(), mysqli_connect_errno(), 'Set charset');
        }

//        $offset = sprintf('%+d:%02d', HOURS_OFFSET, MINUTES_OFFSET);
//
//        $query = 'SET time_zone=\''. $offset .'\'';
//        if (!$connection->query($query)) {
//            throw new MySqliException(mysqli_connect_error(), mysqli_connect_errno(), $query);
//        }

        $connection->select_db($dbName);

        return $connection;
    }


    private function createConnection()
    {
        $this->db_conn = @mysql_connect(db_host, db_login, db_pswd);
        if ( ! is_resource($this->db_conn) && mysql_errno() == '1040') {
            sleep(1);
            $this->db_conn = @mysql_connect(db_host, db_login, db_pswd);
        }

//        $this->selet_db();

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
            $this->err_report(mysql_errno() . ': ' . $this->error, "\r\n" . date('r') . ' - Connection to DB: ');
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


    /**
     * @param MySQLi_Result $result
     */
    private function _free_result($result)
    {
        $result->free_result();
    }


    public function quote_smart($value)
    {
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }

        if ( ! is_numeric($value)) {
            $value = "'" . $this->esc($value) . "'";
        }

        return $value;
    }


    public function esc($val)
    {
        return $this->real_escape_string($val);
    }


    private function err_report($err, $descr = '')
    {
        if (PRODUCTION === false) {
            throw new Exception($descr . $err);
        }
        else {
            error_log($descr . $err);
        }
    }


    public function db_close($fake = true)
    {
//        if (is_resource($this->db_conn)) {
//            @mysql_close($this->db_conn);
//            $this->db_conn = null;
//        }
    }


    public function select_obj($table, $fields = '*', $where = '', $place = '', $extra = '')
    {
        $return_result = false;
        $where != '' ? $clause = ' WHERE ' . $where : $clause = '';

        $query = 'SELECT ' . $fields . ' FROM ' . $table . ' ' . $clause . ' ' . $extra;

        if ($res = $this->m_query($query, __METHOD__)) {

            $res_arr = array();
            while ($result = $res->fetch_object()) {

                $res_arr[] = $result;
            }
            $this->_free_result($res);

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

            while ($row = $res->fetch_assoc()) {
                $result[] = $row;
            }

            $this->_free_result($res);
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
            while ($result = $res->fetch_object()) {
                $res_arr[] = $result;
            }

            $this->_free_result($res);

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

        $res = $this->query_result($query, __METHOD__);

        if ( ! $res instanceof MySQLi_Result) {
            $r_result = $this->affected_rows;
        }
        else {
            $tmp = array();
            while ($result = $res->fetch_object()) {

                $tmp[] = $result;
            }
            $this->_free_result($res);
            if ( ! empty($tmp)) {
                $r_result = $tmp;
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
                if ($res = $this->query($query)) {

                    $ins_id = $this->insert_id;
                    if ($ins_id === 0) {
                        $ins_id = true;
                    }

                    return $ins_id;
                }
                else {
                    if (PRODUCTION === false) {
                        throw new Exception($this->error . "\r\n\r\n" . $query);
                    }
                    else {
                        $this->err_report($this->error . "\r\n\r\n" . $query, 'INSERT problem in ' . __CLASS__ . '->' . __FUNCTION__ . ': ' . "\r\n");

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


    public function update_obj($table, $data = false, $where = '', $ignore = null)
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

                if ( ! $res = $this->query($query)) {

                    $this->err_report($this->error . "\r\n\r\n" . $query, 'UPDATE problem in ' . __METHOD__ . ': ' . "\r\n");

                    return false;
                }

                if ( ! $rows = $this->affected_rows) {
                    $rows = true;
                }

                return $rows;
            }
        }

        return false;
    }


    public function update_int_obj($table, $data = false, $where = '')
    {
        if (is_object($data)) {

            $where != '' ? $clause = ' WHERE ' . $where : $clause = '';
            $data_arr = get_object_vars($data);

            if (count($data_arr)) {

                foreach ($data_arr as $key => $val) {

                    $update[] = '`' . $key . '`=' . $val;
                }

                $query = 'UPDATE ' . $table . ' SET ' . join(', ', $update) . ' ' . $clause;

                if ( ! $res = $this->query($query)) {

                    $this->err_report($this->error . "\r\n\r\n" . $query, 'UPDATE problem in ' . __METHOD__ . ': ' . "\r\n");

                    return false;
                }

                if ( ! $rows = $this->affected_rows) {
                    $rows = true;
                }

                return $rows;
            }
        }

        return false;
    }


    public function remove_obj($table, $clause = '')
    {
        $query = 'delete from ' . $table . ' ' . $clause;
        if ( ! $this->query($query)) {

            $this->err_report($this->error . "\r\n\r\n" . $query, 'DELETE problem in ' . __CLASS__ . '->' . __FUNCTION__ . ': ' . "\r\n");

            return false;
        }

        return true;
    }


    public function create_table($data = array())
    {
        if (count($data)) {

            if ($data['tbl']) {

                $query = 'CREATE TABLE `' . $data['tbl'] . '` ';

                if (count($data['fields'])) {

                    $query .= '(' . join(', ', $data['fields']) . ')';

                    if ($res = $this->query($query)) {

                        return true;
                    }
                }
            }
        }

        $this->err_report($this->error . "\r\n\r\n" . $query, 'CREATE TABLE problem in ' . __METHOD__ . ': ' . "\r\n");

        return false;
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

            $this->m_query($query, __METHOD__);
        }

        return true;
    }


    private function alter_table($tbl_name = '', $data = array(), $type = '')
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

                return true;
            }
            else {
                $this->err_report($this->error . "\r\n\r\n" . $query, 'ALTER TABLE problem in ' . __METHOD__ . ': ' . "\r\n");

                return false;
            }
        }

        return false;
    }


    public function drop_table($tbl_name = '')
    {
        $query = 'DROP TABLE IF EXISTS `' . $tbl_name . '`';

        if ($this->query($query)) {
            return true;
        }
        else {
            $this->err_report($this->error . "\r\n\r\n" . $query, 'DROP TABLE problem in ' . __CLASS__ . '->' . __FUNCTION__ . ': ' . "\r\n");

            return false;
        }
    }


    private function list_tables()
    {
        $tables = array();

        $query = 'SHOW TABLES FROM `' . $this->db_name . '`';
        $res   = $this->m_query($query, __METHOD__);
        while ($result = $res->fetch_object()) {
            $tables[] = $result->{Tables_in_ . $this->db_name};
        }
        $this->_free_result($res);

        return $tables;
    }


    private function get_existing_fields($table_name)
    {
        $fields = array();
        $query  = 'SHOW FIELDS FROM `' . $table_name . '`';
        $res    = $this->m_query($query, __METHOD__);
        while ($result = $res->fetch_object()) {
            $fields[] = $result->Field;
        }
        $this->_free_result($res);
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


    public function get_extreme_value($tbl = '', $value_name = '', $where = false, $place = '')
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
        return $this->query_result($query, $method);
    }


    /**
     * @param        $query
     * @param string $method
     *
     * @return bool|\mysqli_result
     * @throws \Exception
     */
    private function query_result($query, $method = '')
    {
        if ( ! $res = $this->query($query)) {

            $this->err_report($this->error . "\r\n\r\n" . $query, '--QUERY problem in ' . $method . ': ' . "\r\n");
            throw new Exception($method . ': ' . $this->error);
        }

        return $res;
    }
}

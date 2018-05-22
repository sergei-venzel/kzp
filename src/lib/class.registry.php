<?php


class Registry
{
    protected static $instance = null;

    private          $stack    = array();

    private          $called   = array(
        'db' => 'class.db.php',
    );

    private          $params   = array(
        'db' => array(db_host, db_login, db_pswd, db_name),
    );


    public static function getInstance()
    {

        if ( ! self::$instance instanceof Registry) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function get($key)
    {
        if (isset($this->stack[$key])) {

            return $this->stack[$key];
        }

        if (isset($this->called[$key])) {

            $this->stack[$key] = $this->loader($key);

            return $this->stack[$key];
        }

        return null;
    }


    private function loader($key)
    {
        if (@include_once($this->called[$key])) {

            if (is_callable(array($key, 'getInstance'))) {

                return call_user_func_array(array(
                    $key,
                    'getInstance',
                ), isset($this->params[$key]) ? $this->params[$key] : array());
            }
        }

        return null;
    }
}

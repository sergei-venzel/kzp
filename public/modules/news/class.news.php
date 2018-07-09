<? defined('SYSPATH') OR die('No direct access allowed');

class news_data
{
	public static $tables = array('main_table'=>'news');
	public $directories = false;

    public function tables_data()
	{
        return array(
            'news'         => array(
                'fields' => array(
                    'id'          => 'int(11) UNSIGNED NOT NULL auto_increment',
                    'sectionId'   => 'int(10) unsigned NOT NULL DEFAULT \'0\'',
                    `meta_title`  => 'varchar(255) NOT NULL DEFAULT \'\'',
                    'title'       => 'varchar(600) NOT NULL default \'\'',
                    'announce'    => 'text NOT NULL default \'\'',
                    'content'     => 'text NOT NULL default \'\'',
                    'keywords'    => 'varchar(600) NOT NULL default \'\'',
                    'description' => 'varchar(600) NOT NULL default \'\'',
                    'publish'     => 'tinyint(1) NOT NULL default \'1\'',
                    'created'     => 'timestamp NOT NULL default CURRENT_TIMESTAMP',
                ),
                'keys'   => array(
                    'PRIMARY KEY (`id`)',
                ),
                'extra'  => 'ENGINE=MyISAM DEFAULT CHARSET=utf8',
            ),
            'newsSections' => array(
                'fields' => array(
                    'id'        => 'int(10) unsigned NOT NULL auto_increment',
                    'title'     => 'varchar(600) NOT NULL default \'\'',
                    'sortOrder' => 'smallint(5) unsigned NOT NULL DEFAULT \'0\'',
                ),
                'keys'   => array(
                    'PRIMARY KEY `id` (`id`)',
                ),
                'extra'  => 'ENGINE=MyISAM DEFAULT CHARSET=utf8',
            ),
        );
	}
}

class news
{
    const SECTIONS_TABLE = news_admin::SECTIONS_TABLE;

    public $name = 'Статьи';
	public $nav_name = 'Статьи';
	public $module_description = 'Create and manage news';
	public $tables;
	
	public $self_config = TRUE;
	public $settings_script = 'settings.php';
	
	public $main_table;
	
	public $class_name = 'news';
	
	public $get_portion_prefix = 'npr';
	
	public $class_constants = array(
						'portion'=>array('type'=>'int','value'=>1, 'label'=>'Количество новостей на странице'),
						'announcenum'=>array('type'=>'int','value'=>1, 'label'=>'Количество выводимых анонсов'),
						);
	public $portion;
	public $announcenum = null;

	public $dbo;
	
	public function __construct()
	{
        $this->dbo = Registry::getInstance()->get('db');//new db();
        $dirs      = new news_data();
        if (is_array( $dirs->directories ) AND ! empty($dirs->directories)) {
            foreach ($dirs->directories as $key => $val) {
                $this->$key = 'files/' . $this->class_name . $val;
            }
        }
        $this->tables = news_data::$tables;
        foreach ($this->tables as $key => $val) {
            $this->$key = $val;
        }

        $this->_config();
        $this->load_settings();
	}
	
	public function __destruct()
	{
		//$this->dbo->db_close();
	}
	
	private function _config()
	{
		if(is_array($this->class_constants))
		{
            $config_file = MODPATH . $this->class_name . '/' . $this->settings_script;
            if ( ! file_exists( $config_file )) {
                $str = '<?php' . PHP_EOL;
                $str .= '$class_vars = array(' . PHP_EOL;
                foreach ($this->class_constants as $key => $val) {
                    switch ($val['type']):

                        case 'int':
                            $value = (int) $val['value'];
                            break;

                        case 'float':
                            $value = floatval( $val['value'] );
                            break;

                        default:
                            $value = '\'' . str_replace( '\'', '\\\'', $val['value'] ) . '\'';
                    endswitch;

                    $str .= '\'' . $key . '\' => ' . $value . ',' . PHP_EOL;
                }
                $str .= ');' . PHP_EOL;
                $str .= '?>';
                if ( ! file_put_contents( $config_file, $str )) {
                    throw new Exception( 'Unable write in ' . $this->class_name . '/' . $this->settings_script );
                }
            }
		}
	}
	
	public function load_settings()
	{
        $success     = false;
        $config_file = MODPATH . $this->class_name . '/' . $this->settings_script;
        if (is_file( $config_file )) {

            include_once($config_file);


            if (is_array( $this->class_constants )) {

                foreach ($this->class_constants as $key => $val) {

                    if(defined($key)) {
                        $this->{$key} = constant($key);
                    }
                }
                $success = true;
            }

//            if (isset($class_vars)) {
//                extract( $class_vars, EXTR_PREFIX_ALL, $this->class_name );
//
//                if (is_array( $this->class_constants )) {
//                    foreach ($this->class_constants as $key => $val) {
//                        $var_name = $this->class_name . '_' . $key;
//                        if (isset($$var_name)) {
//                            $this->{$key} = $$var_name;
//                        }
//                    }
//                    unset($class_vars);
//                    $success = true;
//                }
//            }
        }

        if ( ! $success) {
            $this->preload_settings();
        }
		//logger($this->announce_portion,'dbg.inc');
	}
	
	private function preload_settings()
	{
		if(is_array($this->class_constants))
		{
			foreach($this->class_constants as $key=>$val)
			{
				$this->{$key} = $val['value'];
			}
		}
	}
	
	public function can_use() { }

	public function news_list($nopubl = false, $from = false, $sectionId = false)
	{
		$clause = array();

        if(false === $nopubl) {
            $clause[] = 'publish=1';
        }

        if(false !== $sectionId) {
            $clause[] = 'sectionId=\''. (int)$sectionId .'\'';
        }

//        $where = ' publish=1';
//		if ($nopubl) {
//			$where = '';
//		}

        $where = '';
        if(!empty($clause)) {
            $where = ' ' . implode(' AND ', $clause);
        }

		$limit = '';

		if ($from !== false AND (int)$this->portion > 0) {
			if ((int)$from < 0) {
				$from = 0;
			}
			$limit = ' LIMIT ' . ((int) $from * $this->portion) . ',' . $this->portion;
		}

		return $this->dbo->select_obj( $this->main_table, 'id,title,announce,publish,DATE_FORMAT(created,\'%d/%m/%Y\') AS ndate,sectionId', $where, '', ' ORDER BY created DESC' . $limit );
	}

    public function get_item($id, $nopubl = false)
    {
        $id    = abs( (int) $id );
        $where = ' id=\'' . $id . '\' AND publish=1';
        if ($nopubl) {
            $where = ' id=\'' . $id . '\'';
        }

        return $this->dbo->get_extreme_value( $this->main_table, 'id,sectionId,meta_title,title,announce,keywords,description,DATE_FORMAT(created,\'%m/%d/%Y\') AS ndate,content', $where );
    }
	
	public function paginate($base_uri, $sectionId = false)
	{
        $result = false;
        if ($cnt = $this->count_publish($sectionId)) {
            if ($cnt > $this->portion) {
                $result = array();
                $cp     = ceil( $cnt / $this->portion );
                for ($i = 0; $i < $cp; $i ++) {
                    $name = $i + 1;
                    $link = $base_uri . '&' . $this->get_portion_prefix . '=' . $i;
                    if($sectionId) {
                        $link .= '&section=' . $sectionId;
                    }
                    $result[] = array(
                        'link' => $link,
                        'name' => $name,
                    );
                }
            }
        }

        return $result;
	}
	
	public function module_map($page_id,$form_name,$accepter)
	{
		try
		{
			$html = new View($this->class_name.'/views/module_map');
			$html->page_id = $page_id;
			$html->form_name = $form_name;
			$html->accepter = $accepter;
			$html->list = $this->news_list(TRUE);
			
			return $html->render();
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
	}

    public function module_site_map()
    {
        $result = false;

	    if($page_id = $this->dbo->get_extreme_value(ptbl,'id',' layout=\'news\' AND publish=1')) {

            $page_id = $page_id->id;
            if ($res = $this->news_list( true )) {
                $result = array('title' => $this->nav_name, 'pages' => array());
                foreach ($res as $val) {
                    $tmp               = new stdClass();
                    $tmp->p_name       = $val->title;
                    $tmp->link         = '/?set=' . $page_id . '&news_item=' . $val->id;
                    $result['pages'][] = $tmp;
                }
            }
        }

        return $result;
    }
	
	private function count_publish($sectionId = false)
	{
        $result = false;
        $where = ' publish=1';
        if($sectionId) {
            $where .= ' AND sectionId=\''. $sectionId .'\'';
        }
        if ($res = $this->dbo->get_extreme_value($this->main_table, 'count(id) AS cnt', $where)) {
            $result = $res->cnt;
        }

        return $result;
	}

    public function getSections()
    {
        $query = 'SELECT `id`,`title` FROM `'. self::SECTIONS_TABLE .'` ORDER BY `sortOrder`';

        $result = $this->dbo->selectAssoc($query);

//        $res = $this->dbo->m_query($query);
//
//        while($row = $res->fetch_assoc()) {
//
//            $result[] = $row;
//        }

        return $result;
    }
}

class news_admin extends news
{
	const SECTIONS_TABLE = 'newsSections';

    private $view_path;
	private $actual_date;
	
	public function __construct()
	{
		parent::__construct();
		$this->view_path = $this->class_name.'/views/';
		$this->actual_date = date('YmdHis');
		//$t = FirePHP::getInstance(TRUE)->fb($this->actual_date);
	}
	
	public function __destruct()
	{
		parent::__destruct();
	}
	
	public function assembler()
	{
        try {
            $html           = new View($this->view_path . 'assembler');
            $html->action   = '/modules/' . $this->class_name . '/admin/';
            $html->def_date = date('m/d/Y');
            $html->list     = $this->ex_list();

            return $html->render();
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
	}
	
	public function add_item($pdata)
	{
        if ( ! isset($pdata['date']) OR ! isset($pdata['title'])) {

            throw new Exception('Wrong data definition.');
        }

        $title = trim(strip_tags($pdata['title']));
        if ($title == '') {
            throw new Exception('Title field is empty!');
        }

        $data            = new stdClass();
        $data->title     = $title;
        $data->created   = $this->valid_date($pdata['date']);
        $data->announce  = trim(strip_tags($pdata['announce']));
        $data->sectionId = (int) $pdata['sectionId'];

        $this->dbo->insert_obj($this->main_table, $data);
	}
	
	public function ex_list()
	{
		try
		{
            $html         = new View($this->view_path . 'list');
            $html->action = '/modules/' . $this->class_name . '/admin/';
            $html->list   = $this->news_list(true);
            $sections     = array();
            $tmp          = $this->getSections();
            foreach ($tmp as $row) {
                $sections[(int) $row['id']] = $row['title'];
            }
            $html->sections = $sections;

            return $html->render();
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
	}

    public function sectionsMain()
    {
        $html         = new View($this->view_path . 'sections_main');
        $html->action = '/modules/' . $this->class_name . '/admin/';
        $html->list   = $this->sectionsList();

        return $html->render();
    }

    public function sectionsList()
    {
        $html         = new View($this->view_path . 'sections_list');
        $html->action = '/modules/' . $this->class_name . '/admin/';
        $html->list   = $this->getSections();

        return $html->render();
    }

    public function addSection($sectionName)
    {
        $sectionName = trim(strip_tags($sectionName));
        if(empty($sectionName)) {
            throw new Exception('Required Section name');
        }

        $query = 'INSERT INTO `'. self::SECTIONS_TABLE .'` (`title`) VALUES (\''. $this->dbo->esc($sectionName) .'\')';
        $this->dbo->dbQuery($query);
    }

    public function updateSection($id, $title)
    {
        $id = (int)$id;
        $query = 'UPDATE `'. self::SECTIONS_TABLE .'` SET `title` = \''. $this->dbo->esc($title) .'\' WHERE `id`=\''. $id .'\'';
        $this->dbo->dbQuery($query);
    }

    public function removeSection($id)
    {
        $id = (int)$id;
        $query = 'UPDATE `'. $this->main_table .'` SET `sectionId` = 0 WHERE `sectionId`=\''. $id .'\'';
        $query = 'DELETE FROM `'. self::SECTIONS_TABLE .'` WHERE `id`=\''. $id .'\'';
        $this->dbo->dbQuery($query);
    }

    public function changeSectionOrder($data)
    {
        if(!isset($data['sit']) || !is_array($data['sit'])) {
            return false;
        }

        $i = 1;
        foreach($data['sit'] as $id) {

            $id = (int)$id;
            $query = 'UPDATE `'. self::SECTIONS_TABLE .'` SET `sortOrder`='. $i .' WHERE `id`=\''. $id .'\'';
            $this->dbo->dbQuery($query);
            $i++;
        }

        return true;
    }
	
	public function change_stat($stat,$id)
	{
        $data          = new stdClass();
        $data->publish = (int) $stat;

        $this->update_item($data, $id);
	}
	
	public function drop_item($id)
	{
		$id = abs((int)$id);
		$this->dbo->remove_obj($this->main_table,' WHERE id=\''.$id.'\'');
	}
	
	public function show_item($id)
	{
        try {
            $html               = new View($this->view_path . 'item');
            $html->action       = '/modules/' . $this->class_name . '/admin/';
            $html->item         = $this->get_item($id, true);
            $html->sections     = $this->getSections();
            $html->media_folder = '/files/' . $this->class_name . '/';

            return $html->render();
        } catch (Exception $e) {
            return $e->getMessage();
        }
	}
	
//	public function editor_item($id)
//	{
//		try
//		{
//			$html = new View($this->view_path.'editor');
//			$html->action = '/modules/'.$this->class_name.'/admin/';
//			$html->item = $this->get_item($id,TRUE);
//			$html->wysiwig = html::wysiwyg_init('/admin/','files/news');
//			return $html->render();
//		}
//		catch(Exception $e)
//		{
//			return $e->getMessage();
//		}
//	}

    public function save_details($pdata)
    {
        if ( ! isset($pdata['item_id']) OR ! isset($pdata['title'])) {
            throw new Exception( 'Wrong data definition.' );
        }

        $title = trim( strip_tags( $pdata['title'] ) );
        if ($title == '') {
            throw new Exception( 'Title field is empty!' );
        }

        $data              = new stdClass();
        $data->title       = $title;
        $data->sectionId   = (int) $pdata['sectionId'];
        $data->meta_title  = !empty($pdata['meta_title']) ? trim(strip_tags($pdata['meta_title'])) : '';
        $data->announce    = trim(strip_tags($pdata['announce']));
        $data->keywords    = trim(strip_tags($pdata['keywords']));
        $data->description = trim(strip_tags($pdata['description']));
        $this->update_item($data, $pdata['item_id']);
    }
	
	public function set_content($content,$id)
	{
		$data='';
		$data->content = $content;
		$this->update_item($data,$id);
	}

    private function update_item($data, $id)
    {
        $id = abs( (int) $id );
        $this->dbo->update_obj( $this->main_table, $data, ' id=\'' . $id . '\'' );
    }
	
	private function valid_date($date_str)
	{
		$result = $this->actual_date;
		$date_str = trim(strip_tags($date_str));
		if(!empty($date_str))
		{
			$tmp = explode('/',$date_str);
			if(count($tmp)==3)
			{
				$year = $tmp[2];
				$month = $tmp[0];
				$day = $tmp[1];
				
				$result = $year.$month.$day.date('His');
			}
		}
		return $result;
	}
}

class news_site extends news
{
	public function __construct()
	{
		parent::__construct();
		//$t = FirePHP::getInstance(TRUE)->fb($this->actual_date);
	}
	
	public function __destruct()
	{
		parent::__destruct();
	}

	public function news_portion($from, $sectionId = false)
	{
		return $this->news_list( false, $from, $sectionId );
	}
	
	public function announces($aliases_hash=array())
    {
        $newsList = false;

        $newsPageId = $this->dbo->get_extreme_value(ptbl,'id',' layout=\'news\' AND publish=1');

        if(false !== $newsPageId) {

            $fields = 'id,title,announce,DATE_FORMAT(created,\'%d/%m/%Y\') AS ndate';
            $where = ' publish=1';
            $extra = ' ORDER BY created DESC LIMIT 0,'. ($this->announcenum !== null?$this->announcenum:2);

            $list = $this->dbo->select_obj($this->main_table,$fields,$where,__METHOD__,$extra);

            if(false !== $list) {

                $alias = false;
                if(is_array($aliases_hash)) {
                    $alias = array_search($newsPageId->id,$aliases_hash);
                }

                foreach($list as $item) {

                    if(false === $alias) {
                        $newsLink = '/?set='.$newsPageId->id.'&news_item='.$item->id;
                    }
                    else {
                        $newsLink = $alias . '/?news_item='.$item->id;
                    }

                    $newsList[] = array(
                        'link' => $newsLink,
                        'title' => htmlspecialchars($item->title),
                        'date' => $item->ndate,
                        'name' => $item->title,
                    );
                }
            }
        }

        return $newsList;
    }
}

?>
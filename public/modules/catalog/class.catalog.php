<?php

class catalog_data {
	
/*	
* As many as You need variables But don`t forget Fill in the tables_data() assosiated data (table structure)
*/
	var $main_table = 'gallery';
	var $item_table = 'items';
	
	/*
	* if you need any directories to work, use constructor to define them 
	*/
	
	function catalog_data() {
	
		/*$this->directories = array(
			'logo_dir'=>'/logo'
			);*/
	}
	
	public function tables_data()
	{
		$t_data = array(
			'gallery' => array(
				'fields' => array(
					'id'            => 'int(11) NOT NULL auto_increment',
					'rid'           => 'int(11) unsigned NOT NULL default \'0\'',
					'pid'           => 'int(11) NOT NULL default \'0\'',
					'sort_order'    => 'int(11) NOT NULL default \'1\'',
					'publish'       => 'tinyint(1) NOT NULL default \'0\'',
					'p_name'        => 'varchar(255) NOT NULL default \'\'',
					'p_title'       => 'varchar(255) NOT NULL default \'\'',
					'p_keywords'    => 'varchar(255) NOT NULL default \'\'',
					'p_description' => 'varchar(255) NOT NULL default \'\'',
					'is_empty'      => 'smallint(6) unsigned NOT NULL default \'0\'',
					'no_photos'     => 'varchar(37) NOT NULL default \'\'',
					'p_bg'          => 'varchar(37) NOT NULL default \'\'',
					'cat_text'      => 'text NOT NULL',
					'thumb'         => 'varchar(60) NOT NULL default \'\'',
				),
				'keys'   => array(
					'PRIMARY KEY  (`id`)',
				),
				'extra'  => 'ENGINE=MyISAM DEFAULT CHARSET=utf8',
			),
			'items'   => array(
				'fields' => array(
					'id'               => 'int(11) NOT NULL auto_increment',
					'cat_id'           => 'int(11) NOT NULL default \'0\'',
					'sort_order'       => 'int(11) NOT NULL default \'1\'',
					'publish'          => 'tinyint(1) NOT NULL default \'0\'',
					'item_name'        => 'varchar(255) NOT NULL default \'\'',
					'photo'            => 'varchar(37) NOT NULL default \'\'',
					'up_files'         => 'text NOT NULL',
					'spec'             => 'text NOT NULL',
					'c_links'          => 'varchar(255) NOT NULL default \'\'',
					'short'            => 'text NOT NULL',
					'new_item'         => 'tinyint(1) NOT NULL default \'0\'',
					'c_gal'            => 'text NOT NULL',
					'price'            => 'double(12,3) NOT NULL default \'0\'',
					'quantity'         => 'int(11) NOT NULL default \'1\'',
					'showcase'         => 'bool NOT NULL default \'0\'',
					'keywords'         => 'varchar(1200) NOT NULL DEFAULT \'\'',
					'title'            => 'varchar(255) NOT NULL DEFAULT \'\'',
					'meta_description' => 'varchar(600) NOT NULL DEFAULT \'\'',

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

class catalog {
	
	var $name = 'Каталог';
	var $nav_name = 'Каталог';
	var $module_description = 'Модуль для работы с каталогом';
	var $settings_script = 'settings.php';
	var $class_constants = array(
						'photo_height'=>array('type'=>'int','value'=>210, 'label'=>'Высота большой картинки (px)'),
						'photo_width'=>array('type'=>'int','value'=>240, 'label'=>'<span style="color:red;">Ширина</span> большой картинки (px)'),
						'thum_height'=>array('type'=>'int','value'=>120, 'label'=>'Высота иконки (px)'),
						'thum_width'=>array('type'=>'int','value'=>150, 'label'=>'<span style="color:red;">Ширина</span> иконки (px)'),
						'catalog_portion'=>array('type'=>'int','value'=>3, 'label'=>'Кол-во позиций на странице'),
                        'num_news_prod_main'=>array('type'=>'int','value'=>4, 'label'=>'Количество новинок<br />на главной странице'),
                        'num_news_prod'=>array('type'=>'int','value'=>3, 'label'=>'Количество новинок<br />в анонсе')
						);
                        
        var $sub_menu = array (
                        //'price_list'=>array('name'=>'Загрузка прайс-листа', 'script_file'=>'price_list.php'),
                        ); 
	
	public $cur_factor = 0;
	private $cur_file = 'factors.php';
	
	public function __construct()
	{
		
		$tables = get_class_vars(__CLASS__.'_data');
		if(!empty($tables)) {
			
			foreach($tables as $key=>$val)
				$this->{$key} = $val;
		}
		
		$obj_name = __CLASS__.'_data';
		$tmp = new $obj_name;
		
		$dirs = array();
        if(property_exists($obj_name, 'directories')) {
            $dirs = $tmp->directories;
        }
		if(!empty($dirs)) {
		
			foreach($dirs as $key=>$val)
				$this->{$key} = $val;
		}
		
		$this->cur_file = PUBPATH . 'modules/'.__CLASS__.'/'.$this->cur_file;
		$this->cur_factor = $this->set_cur_factor();
		
	}
	
	function can_use() {
		
	}
	
	function load_settings()
	{
		
		global $relative;
		$settings_file = PUBPATH.'modules/'.__CLASS__.'/'.$this->settings_script;
		if(file_exists($settings_file)) {

			require_once($settings_file);
        }

		//logger(SYSPATH.'modules/'.__CLASS__.'/'.$this->settings_script,'dbg.inc');
		
		if(!defined('photo_height'))
            define('photo_height',$this->class_constants['photo_height']['value']);
        if(!defined('photo_width'))
            define('photo_width',$this->class_constants['photo_width']['value']);
		if(!defined('thum_height'))
			define('thum_height',$this->class_constants['thum_height']['value']);
		if(!defined('thum_width'))
			define('thum_width',$this->class_constants['thum_width']['value']);
		if(!defined('catalog_portion'))
            define('catalog_portion',$this->class_constants['catalog_portion']['value']);
        if(!defined('num_news_prod_main'))
			define('num_news_prod_main',$this->class_constants['num_news_prod_main']['value']);
        if(!defined('num_news_prod'))
            define('num_news_prod',$this->class_constants['num_news_prod']['value']);
	}
	
	private function set_cur_factor()
	{
		$result = $this->cur_factor;
		if(!is_file($this->cur_file))
		{
			if($fp = @fopen($this->cur_file,'w'))
			{
				@fwrite($fp,$this->cur_record($this->cur_factor));
				fclose($fp);
			}
		}
		else
		{
			require($this->cur_file);
			//logger($catalog_cur_factor,'dbg.inc');
			if(isset($catalog_cur_factor))
			{
				$result = $catalog_cur_factor;
				unset($catalog_cur_factor);
				
			}
		}
		
		return $result;
	}
	
	public function set_factor($factor)
	{
		if(is_file($this->cur_file))
		{
			if($fp = @fopen($this->cur_file,'w'))
			{
				@fwrite($fp,$this->cur_record($factor));
				fclose($fp);
			}
		}
	}
	
	public function cur_record($str='')
	{
		$record = '<? defined(\'SYSPATH\') OR die(\'No direct access allowed\');
';
		$record .= '$catalog_cur_factor = '.floatval($str).';';
		$record .= '
?>';
		//logger($record,'dbg.inc');
		return $record;
	}
	
	function module_map($if_catalog_page=0,$form_name='admin',$accepter='')
    {
		
		$html = '';
		
		if($if_catalog_page > 0) {
			
			$listing = array();
		    $gallery = new gallery();
		    $gallery->get_catalog_list($listing);
		    
		    if(!empty($listing)) {
		        
		        $html .= '<div style="border-top: 1px solid #ccc; height: 20px;">&nbsp;</div>';
		        $html .= '<h3>Карта модуля "'.$this->name.'"<br /></h3>';
		        
		        foreach($listing as $val) {
		            
		            $gal_folder = $gallery->photo_dir_pref.$val->id.$gallery->sound_dir.'/';
		            $html .= '
		            <div style="padding-left: '.($val->factor * 15).'px; padding-bottom: 4px;">';
		            
		            $link='/?set='.$if_catalog_page.'&gallery='.$val->id;
		            
		            $html .= '<a href="#" onclick="opener.document.'.$accepter.'.value=\''.$link.'\'; opener.document.'.$form_name.'.title.value=\''.addslashes(htmlspecialchars($val->p_name)).'\'; self.close();">'.$val->p_name.'</a>';
		            
		            if(!empty($val->items)) {
		                
		                $html .= '<fieldset style="padding: 10px; margin: 10px; border: 1px solid #008000;">
		                <legend>Товары</legend>
		                ';
		                
		                foreach($val->items as $it) {
		                    $up_files='';
		                    $item_link = $link.'&item='.$it->id;
		                    $html .= '<a href="#" onclick="opener.document.'.$accepter.'.value=\''.$item_link.'\'; opener.document.'.$form_name.'.title.value=\''.addslashes(htmlspecialchars($it->item_name)).'\'; self.close();">'.$it->item_name.'</a><br />';
		                    if(property_exists($it, 'up_files') && $it->up_files != '') {
		                        
		                        $up_files=unserialize($it->up_files);
		                        if(!empty($up_files)) {
		                            
		                            $html .= '<fieldset style="padding: 10px; margin: 10px; border: 1px solid #fc0;">
		                            <legend>Ссылки на файлы</legend>
		                            ';
		                            foreach($up_files as $up) {
		                                
		                                $click='opener.document.'.$form_name.'.title.value=\''.addslashes(htmlspecialchars($up->txtname)).'\'; opener.document.'.$form_name.'.targetlist.value=\'_blank\'; opener.document.'.$accepter.'.value=\'/'.$gal_folder.$up->fname.'\'; self.close();';
		                                $html .= '<a href="#" onclick="'.$click.'">'.$up->txtname.'</a><br />';
		                                $click='';
		                            }
		                            $html .= '</fieldset>
		                            ';
		                        }
		                    }
		                }
		                
		                $html .= '</fieldset>
		                ';
		            }
		            
		            $html .= '</div>
		            ';
		        }
		    }
		}
		return $html;
	}
    
    function build_sub_menu() {
        
        //return;
        $html ='';
        
        if (!empty($this->sub_menu)) {
            $html.= '<div class="sub_menu">';
            
            foreach ($this->sub_menu as $val) {
                    
                    $html .= '<div onclick="window.open(\''.$val['script_file'].'\',\'setWind\',\'resizable,scrollbars,width=600,height=600\');" class="button">'.$val[name].'</div>';
                
            }
            
            $html.= '<p class="clear">&nbsp;</p></div>';
        }
    
        return $html;

    }

	public function module_site_map()
	{
		$listing = array();
		$gallery = new gallery();
		$gallery->recurse_for_site_map( $listing );

		return array('title' => 'Каталог', 'pages' => $listing);
	}

	/**
	 * @param \search $obj
	 *
	 * @return array|bool
	 */
    public function module_search(search $obj)
    {
    
        /*
                        * Structure of search results Array:
                        * title => Block title - May be linked. For example to Catalog page
                        * src => block src
                        * results => array(
                        *                     array(
                        *                             found_item => Title or found item,
                        *                             item_link => Link to the founded item. For example - /?top=3&set=8,
                        *                             [details => array(array([detail_title]=>string,text=>string))] - Option. Not required. For example while seach in different catalog features
                        *                         )
                        *                     ) 
                        */
        
        global $db;
        
        $page = $db->select_obj(ptbl,'id','publish=1 and layout="catalog"');
        
        if ($page) {
            
            $id_page=$page[0]->id;
            $results = array();
            
            $data_pages = $db->select_obj($this->main_table,'id,p_name','publish=1');
            if($data_pages) {      
        
                $results['title'] = 'В каталоге';
                $results['src'] = '/?set='.$id_page;
                if (empty($results['results'])) 
                    $results['results'] = array();
                
                foreach($data_pages as $key=>$val) {

                    /*$data_pages[$key]->{'p_name'} = $obj->get_lower(trim(strip_tags($val->{'p_name'})));
                    $positions = $obj->find_in_str($obj->etalon,$data_pages[$key]->{'p_name'});*/
                    $find_in_stroka = $obj->get_lower(trim(strip_tags($val->{'p_name'})));
                    $positions = $obj->find_in_str($obj->etalon,$find_in_stroka);
                    
                    $tmp = array();
                    
                    if($positions) {      
                        
                        $tmp['found_item'] = $val->p_name;
                        $tmp['item_link'] = '/?set='.$id_page.'&gallery='.$val->id;                        
                        $results['results'][]=$tmp;

                    }
                }
            }
            
            $gallery = new gallery();
            $gallery_results = array();
            $gallery_results = $gallery->module_search($obj,$id_page);
            
            if (!empty($gallery_results['results'])) {
                $results['results'] = array_merge($results['results'],$gallery_results['results']);
            }
            
            if(!empty($results['results']))
                    return $results;
                else
                    return false;

        } else 
            return false;
            
    }
	
}

class gallery {
    
    var $photo_dir_pref = 'files/catalog/gallery_';
    var $thumb_dir = '/thumbs';
    var $sound_dir = '/sound';
    var $anysrc_dir = '/anysrc';
    var $gallery_page_id = false;
/*    var $cat_thumb_store = '/thumb_set/';*/
    var $photo_table;
    
/*    var $menu_area = array (array (title => 'Спецификация', name =>'spec'));*/
    var $menu_area = array ();
    
    public $price_types = array('zip','rar','excel');
    public $main_dir = 'files/catalog/';
    
    public $cur_factor;
    
    private $catalog_object = NULL;
    
    public function __construct() {

        $this->catalog_object = new catalog();

        $this->catalog_object->load_settings();

        global $db;

        $this->tree_table  = $this->catalog_object->main_table;
        $this->photo_table = $this->catalog_object->item_table;


        $page_id = $db->get_extreme_value( ptbl, 'id', 'publish=1 and layout=\'catalog\'' );
        if ($page_id) {
            $this->gallery_page_id = $page_id->id;
        }

        $this->cur_factor = $this->catalog_object->cur_factor;
    }
    
    public function check_price_type($type='')
	{
		$result = FALSE;
		
		foreach($this->price_types as $val)
		{
			if(stripos($type,$val) !== FALSE)
				$result = TRUE;
		}
		
		return $result;
	}
	
	public function set_factor($factor=0)
	{
		$factor = str_replace(',','.',$factor);
		//logger($factor,'dbg.inc');
		$this->catalog_object->set_factor($factor);
	}
	
	public function showcase_items()
	{
		global $db;
		$result = FALSE;
		if($res = $db->select_obj($this->photo_table,'id,cat_id,item_name,photo,short,price,quantity','publish=1 AND showcase=1 ORDER BY cat_id,sort_order'))
		{
			foreach($res as $val)
			{
				$val->price = number_format(floatval($val->price),1,'.',' ');
				if($this->cur_factor)
					$val->ruprice = number_format(floatval($val->price*$this->cur_factor),1,'.',' ');
				$result[] = $val;
			}
		}
		return $result;
	}
    
    function get_max_upload_size() {
    	
    	if((int)ini_get('post_max_size')<=(int)ini_get('upload_max_filesize'))
        	return ini_get('post_max_size');
        else
        	return ini_get('upload_max_filesize');
    }
    
    function prior_file($avail_files=array())
    {
    	$result = FALSE;
    	if(!empty($avail_files))
		{
			$tmp = array();
			foreach($avail_files as $ctime=>$val)
			{
				$tmp[strtolower(strrchr($val->f_src,'.'))] = $ctime;
			}
			
			if(isset($tmp['.zip']))
				$result = $avail_files[$tmp['.zip']]->im_src.'?v='.$tmp['.zip'];
			elseif(isset($tmp['.rar']))
				$result = $avail_files[$tmp['.rar']]->im_src.'?v='.$tmp['.rar'];
			elseif(isset($tmp['.xls']))
				$result = $avail_files[$tmp['.xls']]->im_src.'?v='.$tmp['.xls'];
		}
		
		return $result;
	}
    
    function bred_crumb(&$crumb_arr,$id=0) {
        
        global $db;
        $id = (int)$id;
        
        $res = $db->get_extreme_value($this->tree_table,'id,rid,pid,p_name',' id=\''.$id.'\'','file: '.__FILE__.'line:'.__LINE__);
        //if($id == 0)
        if($res) {
            
            $tmp='';
            $tmp->href = '/?set='.$this->gallery_page_id.'&gallery='.$res->id;
            $tmp->p_name = $res->p_name;
            $crumb_arr[] = $tmp;
            $this->bred_crumb($crumb_arr,$res->pid);
        }
    }


    public function recurse_for_site_map(&$arr, $pid = 0, $step = - 1)
    {
        global $db;

        $step ++;
        $res = $db->select_obj($this->tree_table, 'id,rid,pid,p_name', 'publish=1 and pid=\'' . $pid . '\' order by sort_order', 'file: ' . __FILE__ . 'line:' . __LINE__);
        if ($res) {

            foreach ($res as $val) {

                $val->link   = '/?set=' . $this->gallery_page_id . '&gallery=' . $val->id;
                $val->factor = 10 * $step + 4;
                if ($items = $this->get_category_commodity($val->id)) {

                    foreach ($items as $k => $i) {

                        $items[$k]->link = $val->link . '&item=' . $i->id;
                    }
                    $val->items = $items;
                }
                $arr[] = $val;
                $this->recurse_for_site_map($arr, $val->id, $step);
            }
        }
    }
    
    
    function get_site_navigation(&$arr,$branch_id,$id=0,$pid=0,$page_info,$step=0) {
        
        global $db;
        
        $res = $db->select_obj($this->tree_table,'id,rid,pid,p_name','publish=1 and pid=\''.$pid.'\' order by sort_order','file: '.__FILE__.'line:'.__LINE__);
        
        if($res) {
            
            $arr['id'] = $pid==0 ? 'd_'.$id : 'c_'.$id;
            
            $arr['class'] = 'sub';
            $step>1 ? $arr['class'] .= ' second' : $arr['class'];
            
            $flag=false;
            foreach ($res as $val) {
                if (in_array($val->id,$branch_id))
                    $flag=true;
            }
            if ($flag)
                $arr['public']=1;
            
        	$step++;
            
            
            foreach($res as $val) {

                $tmp=array();
            
                $a_class='';
                if ($val->id == $page_info->proc_cat) $a_class= 'current';
                
                
                $level_link = (int)$pid>0 ? '&level='.$val->id : '';
                $link='set='.$this->gallery_page_id.'&gallery='.$val->id/*.$level_link*/;
                $tmp['class'] = $a_class;
                $tmp['value'] = '<a href="/?'.$link.'">'.$val->p_name.'</a>';
                /*if($step == 1)
                	$tmp['value'] = '<h3>'.$tmp['value'].'</h3>';*/
        		$tmp['events'] = '';//'onmouseover="showItem(\'c_'.$val->id.'\');" onmouseout="hideItem(\'c_'.$val->id.'\');"';
        		$tmp['childs']=array();
                $this->get_site_navigation($tmp['childs'],$branch_id,$val->id,$val->id,$page_info,$step);
                $arr[]=$tmp;
            }
        }
    }
    
    function get_parent_id_branch(&$arr,$id=0) {
        
        global $db;
        $id=(int)$id;
        
        $res = $db->select_obj($this->tree_table,'id,rid,pid','publish=1 and id=\''.$id.'\'');
        
        if ($res) {
            foreach($res as $val) {
                $arr[]=$val->id;
                if ($val->pid != 0) {
                    $this->get_parent_id_branch($arr,$val->pid);
                }
            }
        }
        
    }
    
    function get_child_id_branch(&$arr,$id=0) {
        
        global $db;
        $id=(int)$id;
        
        $res = $db->select_obj($this->tree_table,'id','publish=1 and pid=\''.$id.'\'');
        
        if ($res) {        
            foreach($res as $val) {
                $arr[] = $val->id;
            }

        }
        
    }
    
    function get_neighbour_id_branch(&$arr,$id=0) {
        
        global $db;
        $id=(int)$id;

        $res = $db->select_obj($this->tree_table,'pid','publish=1 and id=\''.$id.'\'');
         
        if ($res) {
            $p_id = (int)$res[0]->pid;
            
            $res = $db->select_obj($this->tree_table,'id','publish=1 and pid=\''.$p_id.'\' and id!=\''.$id.'\'');
            if ($res) {
                foreach($res as $val) {
                    $arr[]=$val->id;
                }
            }
        }
        
    }
    
    
    function get_top_id_branch(&$arr) {
        
        global $db;
                
        $res = $db->select_obj($this->tree_table,'id','publish=1 and pid=\'0\'');
        if ($res) {
            foreach($res as $val) {
                $arr[]=$val->id;
            }
        }
        
    }
    
    
    
    function get_root_id($cat_id=0) {
        
        global $db;
        $cat_id = (int)$cat_id;
        $res = $db->get_extreme_value($this->tree_table,'rid','id=\''.$cat_id.'\'','file: '.__FILE__.'line:'.__LINE__);
        return $res->rid;
    }


    function get_catalog_list(&$arr, $pid = 0, $factor = - 1)
    {

        global $db;

        $res_tbls     = $db->_query('SHOW TABLES FROM `' . db_name . '`');
        $table_exists = false;
        if ($res_tbls) {

            foreach ($res_tbls as $t) {

                if ($t->{'Tables_in_' . db_name} == $this->tree_table) {

                    $table_exists = true;
                }
            }
        }
        $res = false;
        if ($table_exists) {
            $res = $db->select_obj($this->tree_table, 'id,p_name', ' pid=\'' . $pid . '\' order by sort_order', 'file: ' . __FILE__ . 'line:' . __LINE__);
        }

        if ($res) {

            $factor ++;
            foreach ($res as $val) {

                $val->factor = $factor;
                //echo $factor.'<br />';
                $val->items = $this->get_category_commodity($val->id);
                $arr[]      = $val;
                $this->get_gal_tree($arr, $val->id, $factor);
            }
        }
    }

    public function commonProductsData()
    {
        global $db;

        $query = 'SELECT `it`.`id`, `cat_id`, `item_name`, `p_name`
        FROM `items` `it` LEFT JOIN `gallery` `gl` ON `it`.`cat_id`=`gl`.`id`
        ORDER BY `gl`.`sort_order`, `it`.`sort_order`';

        $list = array();

        if ($items = $db->selectAssoc($query)) {

            foreach ($items as $row) {

                $row['cat_id'] = (int) $row['cat_id'];
                $row['id']     = (int) $row['id'];

                if ( ! isset($list[$row['cat_id']])) {

                    $list[$row['cat_id']] = array(
                        'name'     => $row['p_name'],
                        'products' => array(),
                    );
                }

                $list[$row['cat_id']]['products'][] = array(
                    'id'   => $row['id'],
                    'name' => $row['item_name'],
                );
            }
        }

        try
        {
            $html = new View('catalog/views/list');
            $html->items = $list;


            return $html->render();
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }
    
    function get_category_commodity($cat_id=0) {
        
        global $db;
        
        $list = $db->select_obj($this->photo_table,'id,cat_id,item_name,photo,new_item','cat_id=\''.(int)$cat_id.'\' and publish=1 order by sort_order','file: '.__FILE__.'line:'.__LINE__);
        
        if($list)
            return $list;
        else
            return false;
    }
    
    
    function get_parent_list(&$arr,$id=0,$pid=0) {
        
        global $db;
        $id=(int)$id;
        if($id<0) $id=$id*-1;
        
        if($id==0) {
            
            $top_res = $db->select_obj($this->tree_table,'id',' pid=0 order by sort_order','file: '.__FILE__.'line:'.__LINE__);
            if($top_res) {
                foreach($top_res as $t) {
                    $arr[]=$t->id;
                }
            }
        }
        
        $res = $db->select_obj($this->tree_table,'id,pid',' id=\''.$id.'\'','file: '.__FILE__.'line:'.__LINE__);
        
        if($res) {
            
            $result = $db->select_obj($this->tree_table,'id,pid,p_name',' pid=\''.$res[0]->pid.'\'','file: '.__FILE__.'line:'.__LINE__);
            if($result) {
                
                foreach($result as $val) {
                    
                    $arr[]=$val->id;
                }
                $this->get_parent_list($arr,$result[0]->pid,$result[0]->pid);
            }
        }
        
    }
    
    function get_single_branch(&$arr,$pid=0,$factor) {
        
        global $db;
        $pid=(int)$pid;
        // and (is_empty>0 or no_photos=1)
        $res=$db->select_obj($this->tree_table,'id,p_name,is_empty',' pid=\''.$pid.'\' and publish=1 order by sort_order','file: '.__FILE__.'line:'.__LINE__);
        if($res) {
            $factor++;
            foreach($res as $val) {
                $val->factor=$factor;
                $arr[]=$val;
            }
        }
        
    }
    
        
    
    function moving($tbl='',$direct='',$id='',$field='pid') {
        
        global $db;
        $id=(int)$id;
        
        $item=$db->select_obj($tbl, $field.',sort_order',' id=\''.$id.'\'','file: '.__FILE__.'line:'.__LINE__);
        
        if($item) {
            
            $item=$item[0];
            
            switch($direct) {
                
                case 'up':
                    $result=$db->select_obj($tbl,'id,sort_order',' '.$field.'=\''.$item->$field.'\' and sort_order<\''.$item->sort_order.'\' order by sort_order desc','file: '.__FILE__.'line:'.__LINE__);
                break;
                
                case 'down':
                    $result=$db->select_obj($tbl,'id,sort_order',' '.$field.'=\''.$item->$field.'\' and sort_order>\''.$item->sort_order.'\' order by sort_order asc','file: '.__FILE__.'line:'.__LINE__);
                break;
            }
            
            if($result) {
                
                $res=$result[0];
                
                $data='';
                $data->sort_order=$res->sort_order;
                $db->update_obj($tbl,$data,' id=\''.$id.'\'');
                
                $data='';
                $data->sort_order=$item->sort_order;
                $db->update_obj($tbl,$data,' id=\''.$res->id.'\'');
            }
        }
    }
    
    
    function get_smaller($arr,$etalon) {
    
	    $flag = false;
	    foreach($arr as $key=>$val) {
	        
	        if($key>=$etalon)
	            return $flag;
	        $flag=$key;
	    }
	    
	    return $flag;
	}

	function get_bigger($arr,$etalon) {
	    
	    krsort($arr);

	    $flag = false;
	    foreach($arr as $key=>$val) {

	        if($key<=$etalon)
	            return $flag;
	        $flag=$key;
	    }
	    
	    return $flag;
	}
	
	
	function get_gal_tree(&$arr, $pid=0, $factor=-1) {
        
        global $db;
        
        $res = $db->select_obj($this->tree_table,'id,pid,publish,p_name,sort_order,is_empty',' pid=\''.$pid.'\' order by sort_order','file: '.__FILE__.'line:'.__LINE__);
        
        if($res) {
            
            $factor++;
            foreach($res as $val) {
                
                $val->factor=$factor;
                $val->items=$this->get_category_commodity($val->id);
                $arr[]=$val;
                $this->get_gal_tree($arr,$val->id,$factor);
            }
        }
    }
    
    function catalog_tree(&$arr,$pid=0,$factor=0) {
    
    	global $db;
        
        $res = $db->select_obj($this->tree_table,'id,p_name',' pid=\''.$pid.'\' order by sort_order','file: '.__FILE__.'line:'.__LINE__);
        
        if($res) {
            
            $factor++;
            foreach($res as $val) {
                
                $pre = '';
                for($i=0;$i<$factor;$i++) $pre .= '&gt; ';
                //$val->factor=$factor;
                $val->p_name = htmlspecialchars_decode($pre).$val->p_name;
                $arr[]=$val;
                $this->catalog_tree($arr,$val->id,$factor);
            }
        }
	}
	
	
	
	function sortable_tree_html(&$html_str,&$js_str,$loc_script='/',$pid=0,$step=-1) {
    
    	global $db;
    	$step++;
    	$pid = (int)$pid;
    	$pages = $db->select_obj($this->tree_table,'id,pid,publish,p_name,is_empty,no_photos', ' pid=\''.$pid.'\' order by sort_order');
    	
    	if($pages) {
    	
    		$level_pref = $pid;
    		$html_str .= '<ul id="level_'.$level_pref.'">';
    		$js_str .= '$("#level_'.$level_pref.'").sortable();'."\r\n";
    		
    		$cnt = count($pages);
    		$i=0;
    		foreach($pages as $val) {
    		
    			$val->is_empty>0 ? $full='<span class="instr full" title="В категории есть товары">&nbsp;</span>' : $full='<span class="instr blank">&nbsp;</span>';
                $is_img = ($val->no_photos != '') ? '<span class="instr view"><a href="#" title="Подгружена картинка" onclick="window.open(\'/'.$this->photo_dir_pref.$val->id.'/'.$val->no_photos.'\',\'preview\',\'width=300,height=300,resizable,scrollbars=yes\');return false;">&nbsp;</a></span>' : '<span class="instr blank">&nbsp;</span>';
                
    			$edit_text_link = '<span class="instr write"><a href="#" onclick="window.open(\'category_text.php?cat_id='.$val->id.'\',\'editWind\',\'resizable,scrollbars,width=800,height=650\'); return false;" title="Работать с текстом">&nbsp;</a></span>';
    			
    			$r = $b = 238-($step*7);
    			$g = 255-($step*7);
    			$li_style = $pid==0 ? '' : 'style="background-color: rgb('.$r.','.$g.','.$b.');"';
    			
    			$html_str .= '<li id="p_'.$val->id.'" '.$li_style.'>';
    			// 
    			$html_str .= '<span class="title">';
    			$act_script = 'onmouseup="sort_order(\''.$loc_script.'\','.$pid.',$(\'#level_'.$level_pref.'\').sortable(\'serialize\'),\'#level_'.$level_pref.'\');"';
    			$html_str .= '<span class="instr drag" '.$act_script.'>&nbsp;</span>';
    			$html_str .= '<a href="items.php?cat_id='.$val->id.'">'.$val->p_name.'</a></span>';
    			
    			$html_str .= '<div class="desk">';
    			$html_str .= '<span class="instr drop"><a href="'.$loc_script.'?remove_gallery='.$val->id.'" title="Удалить" onclick="return confirm(\'Удалить категорию &laquo;'.addslashes(htmlspecialchars($val->p_name)).'&raquo; и все вложенные в нее?\');">&nbsp;</a></span>';
    			$html_str .= '<span class="instr edit"><a href="'.$loc_script.'?edit='.$val->id.'">&nbsp;</a></span>';
    			$html_str .= $edit_text_link;
                $html_str .= $is_img;
    			$html_str .= $full;
    			
    			//$html_str .= $clean_str;
    			//$html_str .= $ext_link;
    			
			    
    			$html_str .= '</div>';
    			
    			$this->sortable_tree_html($html_str,$js_str,$loc_script,$val->id,$step);
    			
    			$html_str .= '</li>';
    			$i++;
			}
    		
    		$html_str .= '</ul>';
		}
    	
	}
    
    
    function tree_down_catalog ($id=0, &$mass_img) {
        
        global $db;
        
        $res_id = $db->select_obj($this->tree_table, 'id', 'publish=1 and pid='.$id);

        if ($res_id) {
            foreach ($res_id as $res_id_one){
                $mass_img[]=$res_id_one->id;
                $this->tree_down_catalog($res_id_one->id, $mass_img);
            }
        }
    }
    
    function get_block_gallery(&$arr,$pid) {
        
        global $db;
        
        $tmp =  array();
        $res = $db->select_obj($this->tree_table,'id,rid,pid,p_name,p_description','publish=1 and pid=\''.$pid.'\' order by sort_order','file: '.__FILE__.'line:'.__LINE__);
        
        if ($res) {
            
            foreach($res as $val) {
                
                $i++;
                $mass_img = array();
                $this->tree_down_catalog($val->id, $mass_img);
                                              
               if (count($mass_img)>0) {
                    $zapr ='publish=1 and cat_id in ('.$val->id.','.implode(',',$mass_img).') and photo!=\'\'';
                } else {
                    $zapr ='publish=1 and cat_id in ('.$val->id.') and photo!=\'\'';
                }
                
                $res_img = $db->select_obj($this->photo_table, 'cat_id,photo' ,$zapr);
                
                $tmp='';
                $level_link = (int)$pid>0 ? '&level='.$val->id : '';
                $link='set='.$this->gallery_page_id.'&gallery='.$val->id.$level_link;
                $tmp->_link = '/?'.$link;
                $tmp->name = $val->p_name;
                $tmp->description = $val->p_description;
                        
                if ((!empty($res_img)) && (count($res_img)>0)) { 
                    
                    $n=rand(0,(count($res_img)-1));
                    $img=$res_img[$n]->photo;
                    $img_id=$res_img[$n]->cat_id;
                    $tmp->img = $this->photo_dir_pref.$img_id.$this->thumb_dir.'/'.$img; 
                }
                
                $arr[]=$tmp;
            }
            
            if (count($arr)%2 == 0) {
                if (isset($arr[count($arr)-1]))
                    $arr[count($arr)-1]->end=true;
                if (isset($arr[count($arr)-2]))
                    $arr[count($arr)-2]->end=true;    
            } else {
                if (isset($arr[count($arr)-1]))
                    $arr[count($arr)-1]->end=true; 
            }
        }
        
    }
    
    function get_root_category($cat_id) {
    
    	global $db;
    	$cat_id = (int)$cat_id;
    	$root_category = FALSE;
    	$result = $db->get_extreme_value($this->tree_table,'rid','id=\''.$cat_id.'\' AND rid != \''.$cat_id.'\'');
    	if($result) {
    	
    		$root_category = $db->get_extreme_value($this->tree_table,'p_name','id=\''.$result->rid.'\'');
		}
    	
    	return $root_category;
	}

    function list_cat_items($cat_id, $if_basket, $from = 0)
    {

        global $db;

        $from = (int) $from;
        if ($from < 0) {
            $from = 0;
        }

        $r_txt    = ' limit ' . $from . ',' . catalog_portion;
        $res_item = $db->select_obj($this->photo_table, 'id,cat_id,item_name,photo,c_links,short,price,quantity', 'publish=1 and cat_id=\'' . $cat_id . '\' order by sort_order' . $r_txt, 'file: ' . __FILE__ . 'line:' . __LINE__);

        $res_count = $db->get_extreme_value($this->photo_table, 'count(id) as cnt', 'publish=1 and cat_id=\'' . $cat_id . '\'');
        $from > 0 ? $i = $from : $i = 0;

        $result = array('products' => array(), 'cnt' => $res_count->cnt);

        if ($res_item) {
            foreach ($res_item as $at) {

                $link     = 'set=' . $this->gallery_page_id . '&gallery=' . $cat_id;
                $externalProductLink = trim($at->c_links);
                if(!empty($externalProductLink)) {
                    $at->link = '/?set=' . $this->gallery_page_id . '&' . $externalProductLink;
                }
                else {
                    $at->link = '/?' . $link . '&item=' . $at->id;
                }

                //logger(floatval($at->price));
                $at->price = number_format(floatval($at->price), 1, '.', ' ');
                if ($this->cur_factor > 0) {
                    $at->ruprice = number_format(floatval($at->price * $this->cur_factor), 1, '.', ' ');
                }
                if ($if_basket) {
                    if (($at->quantity > 0) && ($at->price > 0)) {
                        $at->basket_link = '/?' . $link . '&add_basket=' . $at->id;
                    }
                }

                $result['products'][] = $at;
                $i ++;
            }
        }

        return $result;
    }
    
    
    
    public function module_search(search $obj, $id_page)
    {
    
        /*
                        * Structure of search results Array:
                        * title => Block title - May be linked. For example to Catalog page
                        * results => array(
                        *                     array(
                        *                             found_item => Title or found item,
                        *                             item_link => Link to the founded item. For example - /?top=3&set=8,
                        *                             [details => array(array([detail_title]=>string,text=>string))] - Option. Not required. For example while seach in different catalog features
                        *                         )
                        *                     ) 
                        */
        global $db;
        
        $results = array();
        $data_pages = $db->select_obj($this->photo_table,'id,cat_id,item_name,spec,c_links,short','publish=1');
                
        if($data_pages) {      
    
            $tmp = array();
            
            foreach($data_pages as $key=>$val) {
                
                $menu_area = $this->menu_area;
                
                $mas = array();
                
                                
                $data_pages[$key]->{'item_name'} = $obj->get_lower(trim(strip_tags($val->{'item_name'})));
                $positions = $obj->find_in_str($obj->etalon,$data_pages[$key]->{'item_name'});
                
                $data_pages[$key]->{'short'} = $obj->get_lower(trim(strip_tags($val->{'short'})));
                $positions_short = $obj->find_in_str($obj->etalon,$data_pages[$key]->{'short'});
                
                $data_pages[$key]->{'spec'} = $obj->get_lower(trim(strip_tags($val->{'spec'})));
                $positions_spec = $obj->find_in_str($obj->etalon,$data_pages[$key]->{'spec'});
                
                if ($positions) {
                    $mas['detail_title']='<b>'.$val->item_name.'</b>';
                    $mas['detail_link'] = '/?set='.$id_page.'&gallery='.$val->cat_id.'&item='.$val->id;
                    $tmp['details'][]=$mas;
                }
              
                if ($positions_short) {
                    $mas['detail_title']='В кратком описании <b>&laquo;'.$val->item_name.'&raquo;</b>:';
                    $mas['detail_link'] = '/?set='.$id_page.'&gallery='.$val->cat_id.'&item='.$val->id;
                    $str = array();
                    foreach($positions_short as $v) {
                        $v>50 ? $start=($v-50) : $start=0;
                        $str[] = '&hellip;'.str_replace($obj->etalon,$obj->etalon_b, substr(html_entity_decode($data_pages[$key]->{'short'}),$start,200)).'&hellip;';
                    }
                    
                    $mas['text']=join(' ',array_unique($str));
                    $tmp['details'][]=$mas;
                }
                
                if ($positions_spec) {
                    $mas['detail_title']='В описании <b>&laquo;'.$val->item_name.'&raquo;</b>:';
                    $mas['detail_link'] = '/?set='.$id_page.'&gallery='.$val->cat_id.'&item='.$val->id;
                    $str = array();
                    foreach($positions_spec as $v) {
                        $v>50 ? $start=($v-50) : $start=0;
                        $str[] = '&hellip;'.str_replace($obj->etalon, $obj->etalon_b, substr(html_entity_decode($data_pages[$key]->{'spec'}), $start, 200)) . '&hellip;';
                    }
                    
                    $mas['text']=join(' ',array_unique($str));
                    $tmp['details'][]=$mas;
                }

                foreach($menu_area as $key => $at){
                    $data_pages[$key]->{'c_'.$at['name']} = $obj->get_lower(trim(strip_tags($val->{'c_'.$at['name']})));
                    $positions_one = $obj->find_in_str($obj->etalon,$data_pages[$key]->{'c_'.$at['name']});
                    
                    if ($positions_one) {
                        $mas['detail_title']='В разделе '.$at['title'].' <b>&laquo;'.$val->item_name.'&raquo;</b>:';
                        $mas['detail_link'] = '/?set='.$id_page.'&gallery='.$val->cat_id.'&item='.$val->id.'&area='.$key;
                        $str = array();
                        foreach($positions_one as $v) {
                            $v>50 ? $start=($v-50) : $start=0;
                            $str[] = '&hellip;'.str_replace($obj->etalon,$obj->etalon_b, substr(html_entity_decode($data_pages[$key]->{'c_'.$at['name']}),$start,200)).'&hellip;';
                        }
                                          
                        $mas['text']=join(' ',array_unique($str));
                        $tmp['details'][]=$mas;
                    }
                }
                
            }
    
                if (!empty($tmp)) {
                    $results['title'] = 'В каталоге';
                    $results['results'] = array();
                    
                    $tmp['item_link'] = '/?set='.$id_page;
                    $results['results'][]=$tmp;
                    
                    
                
                }

            if(!empty($results['results']))
                return $results;
            else
                return false;
        }
        else
            return false;
    }
    
    
    
    function get_new_items($count_items=false) {
        
    
        global $db;
        
        $page = $db->select_obj(ptbl,'id','publish=1 and layout="catalog"');
        if ($page) {
            
            $res_item = $db->select_obj($this->photo_table,'id,cat_id,item_name,photo,short','new_item=\'1\' order by sort_order','file: '.__FILE__.'line:'.__LINE__);
            
            if ($res_item) {
                
                
                
                $arr=array();
                
                if ($count_items) {
                    
                    $number=-1;
                    if ($count_items<count($res_item)) {

                        while (count($res_item)>$count_items) {
                            $number=rand(0,count($res_item));
                            array_splice($res_item,$number,1);
                        }    
                    }
                
                }
                
                foreach ($res_item as $at) {
                    $temp='';
                    $link='set='.$this->gallery_page_id.'&gallery='.$at->cat_id.'&item='.$at->id;
                    $temp->_link = '/?'.$link;
                    $temp->name = $at->item_name;
                    $temp->short = $at->short;
                    if (!empty($at->photo)) {
                        $temp->photo = '/'.$this->photo_dir_pref.$at->cat_id.$this->thumb_dir.'/'.$at->photo;                    
                        $img_info = @getimagesize(base.$temp->photo);
                    }
                    
                    $arr[]=$temp;
                }
                
                return $arr;
                
            } else return false;
            
        } else return false;
        
    }
    
    
    function count_basket_items($data=array())
    {
    	global $db;
    	$result = array();
    	//write_log($data,'cat_menu.txt');
    	/*SELECT id,item_name, price*2 as sum_11, price*3 as sum_13 FROM `items` WHERE id in (11,13)*/
    	if(!empty($data))
    	{
    		$fields = 'id,cat_id,item_name,photo,short,price,quantity';
    		$id_arr = array();
    		$prices = array();
//    		$ruprices = array();
    		foreach($data as $key=>$val)
    		{
    			$id_arr[] = (int)$key;
    			$prices[] = 'price*'.(int)$val.' as sum_'.(int)$key;
//				if($this->cur_factor>0) {
//					$ruprices[] = 'price*'.(int)$val.'*'.$this->cur_factor.' as rusum_'.(int)$key;
//				}
			}
			
			if(!empty($prices))
				$fields .= ','.join(',',$prices);
			
			if(!empty($id_arr))
			{
				$result = $db->select_obj($this->photo_table,$fields,'id in ('.join(',',$id_arr).') AND price > 0 AND publish=1 order by item_name');
			}
			
			//write_log($result,'cat_menu.txt');
		}
    	return $result;
	}
    
}

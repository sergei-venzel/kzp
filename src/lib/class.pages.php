<?php

class pages
{

    var $page_tree = array('0_0_0' => 'Верхний. Или...');
    var $root_tree = array();
    //var $db;
    var $table           = ptbl;
    var $pages_table     = 'blocks_content';
    var $layout_folder   = 'layout/';
    var $layout_page_suf = '_page.php';
    var $pagesettings;
    var $page_bgs        = array(
        'default.jpg',
        'support.jpg'/*,
    'portfolio.jpg',
    'price.jpg',
    'service.jpg',
    'theory.jpg',
    'vakancy.jpg'*/
    );
    
    function layout_script($layout='') {
    	
    	return 'modules/'.$layout.'/layout/main.php';
    }
    
    function select_tree(&$arr,$pid=0,$factor=0) {
        
        global $db;
        $pid = (int)$pid;
        
        $pages = $db->select_obj($this->table,'id,rid,p_name', ' pid=\''.$pid.'\' order by sort_order');
        if($pages) {
            
            $factor++;
            foreach($pages as $val) {
                
                $pre = '';
                for($i=0;$i<$factor;$i++) $pre .= '&gt; ';
                //$this->page_tree[$val->id.'_'.$val->rid.'_'.$val->id]=$space.$val->p_name;
                $tmp = '';
                $tmp->id = $val->id.'_'.$val->rid.'_'.$val->id;
                $tmp->p_name = htmlspecialchars_decode($pre).$val->p_name;
                $arr[]=$tmp;
                $this->select_tree($arr,$val->id,$factor);
                
            }
        }        
    }

    
    function select_root_tree($rid=0,$pid=0,$step=-1) {
        global $db;
        $step++;
        $pid = (int)$pid;
        $rid = (int)$rid;
        $pages = $db->select_obj($this->table,'id,rid,p_name', ' pid=\''.$pid.'\' and rid=\''.$rid.'\' order by sort_order');
        if($pages) {
            
            foreach($pages as $key=>$val) {
                
                $space = ''; for($i=0;$i<$step;$i++) {$space .= '&raquo;&nbsp;';}
                $this->page_tree[$val->id.'_'.$val->rid.'_'.$val->id]=$space.$val->p_name;
                $this->select_root_tree($val->rid,$val->id,$step);
                
            }
        }        
    }

    function move_page($direction, $id = 0, $pid = 0, $sid = 0)
    {
        global $db;
        $id  = (int) $id;
        $pid = (int) $pid;
        $sid = (int) $sid;
        switch ($direction) {

            case 'up':
                $neibor = $db->select_obj($this->table, 'id,sort_order', ' pid=\'' . $pid . '\' and sort_order < \'' . $sid . '\' order by sort_order desc');
                if ($neibor) {

                    $data_prev             = new stdClass();
                    $data                  = new stdClass();
                    $data_prev->sort_order = $sid;
                    $data->sort_order      = $neibor[0]->sort_order;
                    $db->update_obj($this->table, $data_prev, ' id=\'' . $neibor[0]->id . '\'');
                    $db->update_obj($this->table, $data, ' id=\'' . $id . '\'');
                }
                break;

            case 'down':
                $neibor = $db->select_obj($this->table, 'id,sort_order', ' pid=\'' . $pid . '\' and sort_order > \'' . $sid . '\' order by sort_order asc');
                if ($neibor) {

                    $data_prev             = new stdClass();
                    $data                  = new stdClass();
                    $data_prev->sort_order = $sid;
                    $data->sort_order      = $neibor[0]->sort_order;
                    $db->update_obj($this->table, $data_prev, ' id=\'' . $neibor[0]->id . '\'');
                    $db->update_obj($this->table, $data, ' id=\'' . $id . '\'');
                }
                break;
        }
    }

    function move_block($direction, $id = 0, $pid = 0, $sid = 0)
    {
        global $db;
        $id  = (int) $id;
        $pid = (int) $pid;
        $sid = (int) $sid;
        switch ($direction) {

            case 'up':
                $neibor = $db->select_obj($this->pages_table, 'id,sort_order', ' page_id=\'' . $pid . '\' and sort_order < \'' . $sid . '\' order by sort_order desc');
                if ($neibor) {

                    $data_prev             = new stdClass();
                    $data                  = new stdClass();
                    $data_prev->sort_order = $sid;
                    $data->sort_order      = $neibor[0]->sort_order;
                    $db->update_obj($this->pages_table, $data_prev, ' id=\'' . $neibor[0]->id . '\'');
                    $db->update_obj($this->pages_table, $data, ' id=\'' . $id . '\'');
                }
                break;

            case 'down':
                $neibor = $db->select_obj($this->pages_table, 'id,sort_order', ' page_id=\'' . $pid . '\' and sort_order > \'' . $sid . '\' order by sort_order asc');
                if ($neibor) {

                    $data_prev             = new stdClass();
                    $data                  = new stdClass();
                    $data_prev->sort_order = $sid;
                    $data->sort_order      = $neibor[0]->sort_order;
                    $db->update_obj($this->pages_table, $data_prev, ' id=\'' . $neibor[0]->id . '\'');
                    $db->update_obj($this->pages_table, $data, ' id=\'' . $id . '\'');
                }
                break;
        }
    }
    
    
    function get_top_navigation(&$arr,$pid=0,$i=0) {
        
        global $db;
        global $relative;
        
        $res = $db->select_obj($this->table,'id,rid,pid,p_name,layout,external,ext_link','publish=1 and pid=\''.$pid.'\' and main_page != 1 order by sort_order');
        if($res) {
            
            $arr['id'] = 'd_'.$pid;
            $arr['class'] = $pid==0 ? 'top' : 'sub';
            
            $i++;
            $i>2 ? $arr['class'].=' second' : $arr['class'];
            
            foreach($res as $val) {
            
                $tmp=array();
                $pid===0 ? $link='set='.$val->id : $link='top='.$val->rid.'&set='.$val->id;
                if($val->external == 1) {
            
                    $page_link = '"http://'.($val->ext_link != '' ? htmlspecialchars($val->ext_link) : $_SERVER['HTTP_HOST']).'" target="_blank" title="Страница откроется в новом окне"';
                }
                else {
                    $page_link = '"/?'.$link.'"';
                }
                $tmp['value'] = '<a href='.$page_link.'>'.$val->p_name.'</a>';
                $tmp['events'] = 'onmouseover="showItem(\'d_'.$val->id.'\');" onmouseout="hideItem(\'d_'.$val->id.'\');"';
                $tmp['childs']=array();
                if($pid==0 && $val->layout == 'catalog') {
                    
                    @require_once('catalog/class.catalog.php');
                    $catalog = new gallery();
                    $catalog->get_site_navigation($tmp['childs'],$val->id,0,$i);
                }
                else
                    $this->recurse_navigation($tmp['childs'],$val->id,$i);
                $arr[]=$tmp;
            }
        }
    }
    
    
	function get_family($id=0) {
        
        $parent_list = array();
        $this->get_parent_list($parent_list,$id);
        
        return $parent_list;
    }
    
    
    function recurse_navigation(&$arr,$branch_id,$page_info,$pid=0,$step=0) {
    
    	global $db;
        global $relative;
        
        $res = $db->select_obj($this->table,'id,rid,pid,p_name,layout,external,ext_link','publish=1 and pid=\''.$pid.'\' and top_menu=0 order by sort_order');
        
        if($res) {
            
        	$arr['id'] = 'd_'.$pid;
        	$arr['class'] = $step==0 ? 'topLevel' : 'sub';
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
        		$pid===0 ? $link='set='.$val->id : $link='top='.$val->rid.'&set='.$val->id;
                if($val->external == 1) {
            
                    $page_link = '"http://'.($val->ext_link != '' ? htmlspecialchars($val->ext_link) : $_SERVER['HTTP_HOST']).'" target="_blank" title="Страница откроется в новом окне"';
                }
                else {
                    $page_link = '"/?'.$link.'"';
                }
                
                if ($val->id == $page_info->top_id) $tmp['class'] = 'current';
                $a_class='';
                if ($val->id == $page_info->act_page_id) $a_class= ' class="current"';
                
        		$tmp['value'] = '<a href='.$page_link.$a_class.'>'.$val->p_name.'</a>';
        		$tmp['events'] = 'onmouseover="showItem(\'d_'.$val->id.'\');" onmouseout="hideItem(\'d_'.$val->id.'\');"';                    
                
        		$tmp['childs']=array();
        		if($pid==0 && $val->layout == 'catalog') {
                    
                    $tmp['layout'] = 'catalog';
                	
                    require_once($relative.'lib/class.modules.php');
                    $module = new modules();

                    if ($module->is_active_module('catalog')) {
                    
                	    @require_once($relative.'modules/catalog/class.catalog.php');
                	    $catalog = new gallery();
                        
                        $branch_id = array();
                        $catalog->get_parent_id_branch($branch_id,$page_info->proc_cat);
                        $catalog->get_child_id_branch($branch_id,$page_info->proc_cat);
                        $catalog->get_neighbour_id_branch($branch_id,$page_info->proc_cat);
                        $catalog->get_top_id_branch($branch_id);

/*                    echo '<pre>';
                    print_r($branch_id);
                    echo '</pre>';*/
                    
                	    $catalog->get_site_navigation($tmp['childs'],$branch_id,$val->id,0,$page_info,$step);
                    }
                }
                else {
        			$this->recurse_navigation($tmp['childs'],$branch_id,$page_info,$val->id,$step);
                }
        		$arr[$val->id]=$tmp;
			}
		}
	}
	
	function one_step_into($page_id=0) {
	
		global $db;
		global $relative;
		
		$page_id = intval($page_id);
		$menu = array();
		$res = $db->select_obj($this->table,'id,rid,pid,p_name,external,ext_link','publish=1 and pid=\''.$page_id.'\' order by sort_order');
		
		if($res) {
		
			foreach($res as $val) {
			
				$pid===0 ? $link='set='.$val->id : $link='top='.$val->rid.'&set='.$val->id;
                if($val->external == 1) {
            
                    $page_link = '"http://'.($val->ext_link != '' ? htmlspecialchars($val->ext_link) : $_SERVER['HTTP_HOST']).'" target="_blank" title="Страница откроется в новом окне"';
                }
                else {
                    $page_link = '"/?'.$link.'" title="'.htmlspecialchars($val->p_name).'"';
                }
                
                $menu[] = '<a href='.$page_link.'>'.$val->p_name.'</a>';
			}
		}
		
		return $menu;
	}
	
	
	function expand_nav(&$arr,$parents = array(),$pid=0,$step=0) { // Need to be inspected and completeed (not finished yet!)
    
    	global $db;
        global $relative;
        
        $res = $db->select_obj($this->table,'id,rid,pid,p_name,layout,external,ext_link','publish=1 and pid=\''.$pid.'\' order by sort_order');
        if($res) {
        
        	
        	$arr['id'] = 'd_'.$pid;
        	$arr['class'] = $step==0 ? 'topLevel' : '';
        	$step++;
        	foreach($res as $val) {
        	
        		if(in_array($val->id,$parents)) {
        		
        			$tmp=array();
        			$pid===0 ? $link='set='.$val->id : $link='top='.$val->rid.'&set='.$val->id;
	                if($val->external == 1) {
	            
	                    $page_link = '"http://'.($val->ext_link != '' ? htmlspecialchars($val->ext_link) : $_SERVER['HTTP_HOST']).'" target="_blank" title="Страница откроется в новом окне"';
	                }
	                else {
	                    $page_link = '"/?'.$link.'"';
	                }
        			$tmp['value'] = '<a href='.$page_link.'>'.$val->p_name.'</a>';
        			$tmp['childs']=array();
        			$this->expand_nav($tmp['childs'],$parents,$val->id,$step);
        			$arr[]=$tmp;
				}
			}
		}
	}
    
    function sortable_tree_html(&$html_str,&$js_str,$loc_script='/',$pid=0,$step=-1) {
    
        global $db;
        $step++;
        $pid = (int)$pid;
        $pages = $db->select_obj($this->table,'id,rid,pid,publish,sort_order,p_name,layout,main_page,bg,external,ext_link,top_menu', ' pid=\''.$pid.'\' order by sort_order');
        
        if($pages) {
        
            $level_pref = $pid>-1 ? $pid : 'alones';
            $html_str .= '<ul id="level_'.$level_pref.'">';
            $js_str .= '$("#level_'.$level_pref.'").sortable();'."\r\n";
            
            $cnt = count($pages);
            $i=0;
            foreach($pages as $val) {
            
                $val->pid==0 ? $stored_file=cache_file_name('set='.$val->id, PUBPATH) : $stored_file=cache_file_name('top='.$val->rid.'&set='.$val->id,PUBPATH);
                
                ((int)$val->publish === 0) ? $a_class = ' class="nopubl"' : $a_class = '';
                  
                
                if(file_exists($stored_file)) {
                    
                    $clean_str = '<span class="instr stored" title="Очистить кэш" onclick="clean_cache(\''.RELATIVE_PATH.'admin/cleaner.php\',\''.$stored_file.'\',this.className=\'instr blank\');">&nbsp;</span>';
                }
                else
                    $clean_str='<span class="instr blank">&nbsp;</span>';
                
                if($val->pid==0) {
        
                    $val->main_page==1 ? $main_status='<span class="instr home" title="Стартовая страница">&nbsp;</span>' : $main_status='<span class="instr dohome"><a href="'.$loc_script.'?stat_main='.$val->id.'" title="Сделать Стартовой">&nbsp;</a></span>';
                    
                }
                else
                    $main_status='<span class="instr blank">&nbsp;</span>';
                    
                if (($val->pid<1) && ((int)$val->top_menu == 1)) {
                    $is_top = '<span class="instr toppage">&nbsp;</span>';
                } elseif (($val->pid<1) && ((int)$val->top_menu == -1)) {
                    $is_top = '<span class="instr footerpage">&nbsp;</span>';
                } else $is_top='<span class="instr blank">&nbsp;</span>';
                
                $is_img = ($val->pid==0 && $val->bg != '') ? '<span class="instr view"><a href="#" title="Подгружена картинка" onclick="window.open(\'/'.design.'/'.$val->bg.'\',\'preview\',\'width=300,height=300,resizable,scrollbars=yes\');return false;">&nbsp;</a></span>' : '<span class="instr blank">&nbsp;</span>';
                
                if($i == 0)
                    $li_class = 'class="first"';
                elseif($i == $cnt-1)
                    $li_class = 'class="last"';
                else
                    $li_class = '';
                
                $ext_link = $val->external == 1 ? '<span class="instr external"><a href="'.($val->ext_link != '' ? 'http://'.htmlspecialchars($val->ext_link) : 'http://'.$_SERVER['HTTP_HOST']).'" target="_blank" title="Посмотреть на ресурс">&nbsp;</a></span>' : '<span class="instr blank">&nbsp;</span>';
                
                $r = $b = 238-($step*7);
                $g = 255-($step*7);
                $li_style = $pid==0 ? '' : 'style="background-color: rgb('.$r.','.$g.','.$b.');"';
                
                $act_script = 'onmouseup="sort_order(\''.$loc_script.'\','.$pid.',$(\'#level_'.$level_pref.'\').sortable(\'serialize\'),\'#level_'.$level_pref.'\');"';
                $html_str .= '<li id="p_'.$val->id.'" '.$li_class.' '.$li_style.'>';
                // 
                $html_str .= '<span class="title">';
                
                $html_str .= '<span class="instr drag" '.$act_script.'>&nbsp;</span>';
                $html_str .= '<a href="content.php?content_page='.$val->id.'"'.$a_class.'>'.$val->p_name.'</a></span>';
                
                $html_str .= '<div class="desk">';
                
                $html_str .= $is_top;
                $html_str .= '<span class="instr drop"><a href="'.$loc_script.'?delete_page='.$val->id.'" title="Удалить" onclick="return confirm(\'Удалить &laquo;'.addslashes(htmlspecialchars($val->p_name)).'&raquo;\r\n(и все под-страницы)?\');">&nbsp;</a></span>';
                $html_str .= '<span class="instr edit"><a href="'.$loc_script.'?edit_page='.$val->id.'">&nbsp;</a></span>';
                $html_str .= $main_status;
                $html_str .= $is_img;
                $html_str .= $clean_str;
                $html_str .= $ext_link;

                
                
                $html_str .= '</div>';
                
                if((int)$pid > -1)
                    $this->sortable_tree_html($html_str,$js_str,$loc_script,$val->id,$step);
                
                $html_str .= '</li>';
                $i++;
            }
            
            $html_str .= '</ul>';
        }
        
    }
    
    function select_menu_tree(&$root_tree,$pid=0,$step=-1) {
        
        global $db;
        $step++;
        $pid = (int)$pid;
        $pages = $db->select_obj($this->table,'id,rid,pid,publish,sort_order,p_name,layout,main_page,bg,external,ext_link', ' pid=\''.$pid.'\' order by sort_order');
        $min_order = $db->select_obj($this->table,'min(sort_order) as minorder', ' pid=\''.$pid.'\'');
        $max_order = $db->select_obj($this->table,'max(sort_order) as maxorder', ' pid=\''.$pid.'\'');
        if($pages) {
            
            foreach($pages as $val) {
                
                $data->id=$val->id;
                $data->pid=$val->pid;
                $data->rid=$val->rid;
                $data->publish=$val->publish;
                $data->sort_order=$val->sort_order;
                $data->p_name=$val->p_name;
                $data->layout = $val->layout;
                $data->main_page=$val->main_page;
                $data->minorder=$min_order[0]->minorder;
                $data->maxorder=$max_order[0]->maxorder;
                $data->factor=$step;
                $data->bg = $val->bg;
                $data->external = $val->external;
                $data->ext_link = $val->ext_link;
                $root_tree[]=$data;
                $data='';
                $this->select_menu_tree($root_tree,$val->id,$step);
            }
        }        
    }
    
    function alone_pages() {
        
        global $db;
        
        $pages = $db->select_obj($this->table,'id,rid,pid,publish,sort_order,p_name,layout,main_page,top_menu,external,ext_link', ' pid=\'-1\' order by sort_order');
        $min_order = $db->select_obj($this->table,'min(sort_order) as minorder', ' pid=\'-1\'');
        $max_order = $db->select_obj($this->table,'max(sort_order) as maxorder', ' pid=\'-1\'');
        $alone_pages=array();
        if($pages) {
            
            foreach($pages as $val) {
                $data='';
                $data->id=$val->id;
                $data->pid=$val->pid;
                $data->rid=$val->rid;
                $data->publish=$val->publish;
                $data->sort_order=$val->sort_order;
                $data->p_name=$val->p_name;
                $data->layout = $val->layout;
                $data->main_page=$val->main_page;
                $data->minorder=$min_order[0]->minorder;
                $data->maxorder=$max_order[0]->maxorder;
                $data->factor=4;
                $data->top_menu = $val->top_menu;
                $data->external = $val->external;
                $data->ext_link = $val->ext_link;
                $data->link = $val->external==1 ? '"http://'.($val->ext_link!=''?htmlspecialchars($val->ext_link):$_SERVER['HTTP_HOST']).'" target="_blank" title="В новом окне"' : '"/?set='.$val->id.'"';
                $alone_pages[]=$data;
            }
        }
        
        if(!empty($alone_pages))
            return $alone_pages;
        else
            return false;
    }
    
    
    function site_map(&$root_tree,$pid=0,$step=-1) {
        
        global $db;
        $step++;
        $pid = (int)$pid;
        $pages = $db->select_obj($this->table,'id,rid,pid,p_name', ' pid=\''.$pid.'\' and publish=1 and external=0 order by sort_order');
        if($pages) {
            
            foreach($pages as $val) {
                
                $data='';
                $data->id=$val->id;
                $data->pid=$val->pid;
                $data->rid=$val->rid;
                $data->p_name=$val->p_name;
                $data->factor=10*$step+4;
                $data->link = $val->pid==0 ? '/?set='.$val->id : '/?top='.$val->rid.'&set='.$val->id;
                $root_tree[]=$data;
                
                $this->site_map($root_tree,$val->id,$step);
            }
        }        
    }
    
    
    
    function get_all_page_data($id=0,$where='') {
        
        global $db;
        $id=(int)$id;
        $stat = $db->select_obj($this->table,'id,rid,p_name,p_keywords,p_description',' id=\''.$id.'\'');
        $order = $db->select_obj($this->pages_table,' max(sort_order) as maxorder, min(sort_order) as minorder', ' page_id=\''.$id.'\'');
        if($stat) {
            
            $data->rid = $stat[0]->rid;
            $data->p_name = $stat[0]->p_name;
            $data->id = $stat[0]->id;
            $data->p_keywords = $stat[0]->p_keywords;
            $data->p_description = $stat[0]->p_description;
            $data->maxorder = $order[0]->maxorder;
            $data->minorder = $order[0]->minorder;
            
            $content = $db->select_obj($this->pages_table,'id,sort_order,publish,block_title,short_cont,DATE_FORMAT(pub_date,"%d-%m-%Y, %H:%i") as pbd',' page_id=\''.$data->id.'\' '.$where.' order by sort_order');
            if($content) {
                $arr=array();
                foreach($content as $val) {
                    
                    $cont_data->id=$val->id;
                    $cont_data->sort_order=$val->sort_order;
                    $cont_data->publish=$val->publish;
                    $cont_data->block_title=$val->block_title;
                    $cont_data->short_cont=$val->short_cont;
                    $cont_data->pbd=$val->pbd;
                    $arr[] = $cont_data;
                }
                $data->blocks = $arr;
            }
            
            return $data;
        }
        else return false;
    }
    
    
    function get_root_pages($page_id=0,&$p_arr) {
        
        global $db;
        $page_id = (int)$page_id;
        $p_arr[] = $page_id;
        
        $childs = $db->select_obj($this->table,'id,rid,pid',' pid=\''.$page_id.'\'');
        if($childs) foreach($childs as $val) $this->get_root_pages($val->id,$p_arr);
    }
    
    function is_top($page_id=0) {
        
        global $db;
        $page_id = (int)$page_id;
        $result = $db->select_obj($this->table,'rid',' id=\''.$page_id.'\' and (pid=0 or pid=-1)');
        if($result)
            return $result[0]->rid;
        else
            return false;
    }
    
    function get_single_value($id=0,$value='') {
        
        global $db;
        $id = (int)$id;
        $result = $db->select_obj($this->table,$value,' id=\''.$id.'\'');
        if($result) return $result[0];
        else return false;
    }
    
    function get_parent_list(&$arr,$id=0) {
        
        global $db;
        $id=abs((int)$id);
        
        $res = $db->select_obj($this->table,'id,pid',' id=\''.$id.'\'');
        
        if($res) {
            
            $result = $db->select_obj($this->table,'id,pid,p_name',' pid=\''.$res[0]->pid.'\'');
            if($result) {
                
                foreach($result as $val) {
                    
                    $arr[]=$val->id;
                }
                $this->get_parent_list($arr,$result[0]->pid);
                //echo $result[0]->id;
            }
        }
    }
    
    
    function get_single_branch(&$arr,$pid=0,$factor) {
        
        global $db;
        $pid=(int)$pid;
        
        $res=$db->select_obj($this->table,'id,rid,pid,p_name',' pid=\''.$pid.'\' and publish=1 order by sort_order');
        if($res) {
            $factor++;
            foreach($res as $val) {
                $val->factor=$factor;
                $arr[]=$val;
            }
        }
        
    }
    
    function expanded_menu(&$arr,$parents_arr=array(),$marker_id=0, $pid=0, $factor=-2) {
        
        global $db;
        $marker_id=(int)$marker_id;
        $pid=(int)$pid;
        $res = $db->select_obj($this->table,'id,rid,pid,p_name','pid=\''.$pid.'\' and publish=1 order by sort_order');
        
        if($res) {
            
            $factor++;
            
            //$this->get_parent_list($parents_arr,$pid);
            
            foreach($res as $val) {
                //echo $val->id;
                if(in_array($val->id,$parents_arr)) {
                    
                    $val->factor=$factor;
                    $arr[]=$val;
                    if($val->id == $marker_id)
                        $this->get_single_branch($arr,$val->id,$factor);
                    $this->expanded_menu($arr,$parents_arr,$marker_id,$val->id, $factor);
                }
                
            }
        }
    }
    
    function page_types() {
    	
    	global $db;
    	$valid_names = array(
    	
    		'common'=>'Обычная',
    		'sitemap'=>'Карта сайта',
    		'home'=>'Главная'
    		);
    	
    	$res = $db->select_obj('modules','id,module_name,nav_name');
    	if($res) {
    		
    		foreach($res as $val)
    			$valid_names[$val->module_name]=$val->nav_name;
    	}
    	
    	natsort($valid_names);
    	return $valid_names;
    }
    
    function get_layout() {
        
        $dir=opendir(base.$this->layout_folder);
        if($dir) {
            
            $layouts=array();
            while(false != ($file=readdir($dir))) {
                
                if($file != '.' && $file != '..') {
                    
                    $str=str_replace($this->layout_page_suf,'',$file);
                    $layouts[$str]=$this->page_types($str);
                }
            }
            
            if(count($layouts)) {
                ksort($layouts);
                return $layouts;
            }
            else
                return false;
        }
        else return false;
    }
    
    
    function paragraph($string='') {
        
        if($string != '') {
            
            $arr = explode("\r\n",$string);
            $r_arr=array();
            foreach($arr as $val) {
                if($val != '')
                    $r_arr[]=$val;
            }
            $r_arr[0] = '<i>'.$r_arr[0].'</i>';
            return '<p>'.join('</p>'."\r\n".'<p>',$r_arr).'</p>';
        }
        else
            return '&nbsp;';
    }
    
    
    function renew_branch($new_rid=0,$id=0) {
    
    	global $db;
    	$new_rid = (int)$new_rid;
    	$id = (int)$id;
    	
    	if($new_rid != 0 && $id != 0) {
    	
    		$arr=array();
    		$this->collect_branch_ids($arr,$id);
    		
    		if(!empty($arr)) {
    		
    			$data='';
    			$data->rid = $new_rid;
    			
    			$db->update_obj($this->table,$data,'id in ('.join(',',$arr).')');
			}
		}
	}
	
	
	function collect_branch_ids(&$arr,$id=0) {
	
		global $db;
    	$id = (int)$id;
    	
    	$res = $db->select_obj($this->table,'id','pid=\''.$id.'\'');
    	
    	if($res) {
    	
    		foreach($res as $val) {
    		
    			$arr[] = $val->id;
    			$this->collect_branch_ids($arr,$val->id);
			}
		}
	}
    
    function get_parent_id_branch(&$arr,$id=0) {
        
        global $db;
        $id=(int)$id;
        
        $res = $db->select_obj($this->table,'id,rid,pid','publish=1 and id=\''.$id.'\'');
        
        if ($res) {
            foreach($res as $val) {
                $arr[]=$val->id;
                if ($val->pid != 0) {
                    $this->get_parent_id_branch($arr,$val->pid);
                }
            }
        }
        
    }
    
    function get_neighbour_id_branch(&$arr,$id=0) {
        
        global $db;
        $id=(int)$id;

        $res = $db->select_obj($this->table,'pid','publish=1 and id=\''.$id.'\'');
         
        if ($res) {
            $p_id = (int)$res[0]->pid;
            
            $res = $db->select_obj($this->table,'id','publish=1 and pid=\''.$p_id.'\' and id!=\''.$id.'\'');
            if ($res) {
                foreach($res as $val) {
                    $arr[]=$val->id;
                }
            }
        }
        
    }
    
    function get_child_id_branch(&$arr,$id=0) {
        
        global $db;
        $id=(int)$id;
        
        $res = $db->select_obj($this->table,'id','publish=1 and pid=\''.$id.'\'');
        
        if ($res) {        
            foreach($res as $val) {
                $arr[] = $val->id;
            }

        }
        
    }
    
    function get_top_id_branch(&$arr) {
        
        global $db;
                
        $res = $db->select_obj($this->table,'id','publish=1 and pid=\'0\'');
        if ($res) {
            foreach($res as $val) {
                $arr[]=$val->id;
            }
        }
        
    }
    
    function get_one_step_id_branch(&$arr) {
        
        global $db;
        
        $res = $db->select_obj($this->table,'id','publish=1 and pid=\'0\'');
        if ($res) {
            foreach($res as $val) {
                $arr[] = $val->id;
            }
        }
        
    }

    public function main_pages_list($flag = 0)
    {
        global $db;

        $list = array();

        if ($result = $db->select_obj($this->table, 'id,pid,p_name,a_title,layout,external,ext_link,main_page', 'publish=1 and top_menu=0 and pid=0 order by sort_order')) {
            foreach ($result as $val) {
                if ($val->external == 1) {
                    $page_link = 'href="http://' . ($val->ext_link != '' ? htmlspecialchars($val->ext_link) : $_SERVER['HTTP_HOST']) . '" target="_blank" title="Страница откроется в новом окне"';
                }
                else {
                    if (1 === (int) $val->main_page) {
                        $page_link = 'href="/"';
                    }
                    else {
                        $page_link = 'href="/?set=' . $val->id . '"';
                    }
                }

                $a_class = $val->id == $flag ? 'class="current"' : '';
                $list[]  = array('value' => '<a ' . $page_link . ' ' . $a_class . ' title="'. htmlspecialchars($val->a_title) .'">' . $val->p_name . '</a>');
            }
        }

        return $list;
    }
    
    function childs_pages_list($id) {
        
        global $db;
        $id=(int)$id;
        if($id <= 0)
        	return false;
        $list = array();
        
        if($res = $db->select_obj($this->table,'id,p_name,external,ext_link','publish=1 and pid=\''.$id.'\' order by sort_order'))
        {        
            foreach($res as $val)
            {
                
                if($val->external == 1)
        			$page_link = 'href="http://'.($val->ext_link != '' ? htmlspecialchars($val->ext_link) : $_SERVER['HTTP_HOST']).'" target="_blank" title="Страница откроется в новом окне"';
			    else
			    	$page_link = 'href="/?top='.$id.'&set='.$val->id.'"';
                $list[] = array(
                	'value'=>'<a '.$page_link.'>'.$val->p_name.'</a>'
                );
            }

        }
        
        return $list;
    }
    
    function get_top_menu_navigation() {
        
        global $db;
        
        return $db->select_obj($this->table,'id,pid,p_name,layout,external,ext_link','publish=1 and top_menu=1 order by sort_order');
    }
    
    function bradcrumb($page_id)
    {
    	$bradcrumb = array();
    	$list = array();
    	$this->bradcrumb_recurs($list,$page_id);
    	
    	if(!empty($list))
    		array_shift($list);
    		
    	if(!empty($list))
    	{
    		foreach($list as $val)
    		{
    			
    			if($val->pid == 0)
    				$page_link = 'href="/?set='.$val->id.'"';
    			else
    				$page_link = 'href="/?top='.$val->rid.'&set='.$val->id.'"';
    			
    			$bradcrumb[] = array(
    				'value'=>'&laquo;&nbsp;<a '.$page_link.'>'.$val->p_name.'</a>&nbsp;'
    			);
			}
		}
    	
    	return array_reverse($bradcrumb);
	}
    
    function bradcrumb_recurs(&$arr,$id) {
        
        global $db;
        $id=(int)$id;
        
        if($res = $db->select_obj($this->table,'id,rid,pid,p_name','publish=1 and id=\''.$id.'\''))
        {
            foreach($res as $val) {
                $arr[]=$val;
                if ($val->pid != 0) {
                    $this->bradcrumb_recurs($arr,$val->pid);
                }
            }
        }
        
    }
}

?>
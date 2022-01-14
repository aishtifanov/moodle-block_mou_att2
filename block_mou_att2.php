<?php // $Id: block_mou_att2.php,v 1.2 2011/05/10 12:15:39 shtifanov Exp $

require_once('lib_att2.php');

class block_mou_att2 extends block_list
{
    function init() {
        $this->title = get_string('title2', 'block_mou_att');
        $this->version = 2010121000;
    }


    function get_content() {
        global $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $items = array();
        $icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
            $this->content = '';
        } else {
            $this->load_content();
        }

        return $this->content;
    }

    function load_content() {
        global $CFG, $yearmonit, $USER;


        $items = array();
        $icons = array();
        $index_items = get_items_menu ($items, $icons); 
		
		if (!empty($index_items))	{			
			foreach ($index_items as $index_item)	{
				$this->content->items[] = $items[$index_item];
				$this->content->icons[] = $icons[$index_item];
			}

        	$this->content->footer = '<a href="'.$CFG->wwwroot.'/blocks/mou_att2/index.php">'.get_string('title2', 'block_mou_att').'</a>'.' ...';
 		}
    }


    function instance_allow_config() {
        return false;
    }

/*
    function specialization() {
        $this->title =  get_string('title2', 'block_mou_att');
    }
*/    
}

?>

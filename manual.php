<?php // $Id: index.php,v 1.2 2011/05/10 12:15:39 shtifanov Exp $

    require_once('../../config.php');
    require_once('../monitoring/lib.php');
    require_once('lib_att2.php');    

    $rid = optional_param('rid', 1, PARAM_INT);       // Rayon id
    
    require_login();

    $strmonit = get_string('frontpagetitle2', 'block_mou_att');
    print_header_mou("$SITE->shortname: $strmonit", $SITE->fullname, $strmonit);

    print_heading($strmonit);

    $table->align = array ('left', 'left');
    $table->size = array ('20%', '80%');

    $items = array();
    $icons = array();
    $index_items = get_items_menu ($items, $icons); 

	if (!empty($index_items))	{			
		foreach ($index_items as $index_item)	{
		    $table->data[] = array("<strong>{$items[$index_item]}</strong>" , 
                                    get_string ('description_'.$index_item, 'block_mou_att'));
		}
	}

    print_table($table);

    print_footer();

?>



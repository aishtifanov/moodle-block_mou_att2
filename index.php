<?php // $Id: index.php,v 1.4 2014/06/16 12:00:07 shtifanov Exp $

    require_once('../../config.php');
    require_once('../monitoring/lib.php');
    require_once('lib_att2.php');    

    $rid = optional_param('rid', 1, PARAM_INT);       // Rayon id
    
    require_login();

    $strmonit = get_string('frontpagetitle2', 'block_mou_att');
    print_header_mou("$SITE->shortname: $strmonit", $SITE->fullname, $strmonit);

    print_heading($strmonit);

    if (record_exists_select('monit_att_staff', "userid = $USER->id AND schoolid in (3385, 2769, 2116)"))   {
        print_heading(get_string('staffdeleted', 'block_monitoring'));        
        exit();
    } 

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

    // !!!!!!!!!!!!!!!!!!!!!! убрать в конце 2013 года и закрыть доступ к редактированию 2012/2013    
    // execute_sql('update mdl_monit_att_attestation set yearid=6 where criteriaid in (SELECT id FROM mdl_monit_att_criteria where yearid=6)');

    print_table($table);

    print_footer();
    
    /*
    delete from mdl_monit_att_attestation where criteriaid in (SELECT id FROM mdl_monit_att_criteria where yearid=7 and name like '..%');
    delete from mdl_monit_att_estimates where criteriaid in (SELECT id FROM mdl_monit_att_criteria where yearid=7 and name like '..%');
    delete FROM mdl_monit_att_criteria where yearid=7 and name like '..%';    
    */




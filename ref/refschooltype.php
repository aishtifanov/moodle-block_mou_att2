<?php // $Id: refschooltype.php,v 1.3 2011/09/07 14:15:53 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');

	$context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    $edit_capability = has_capability('block/mou_att2:editmeetingak', $context_region); 
    $view_capability = has_capability('block/mou_att2:viewmeetingak', $context_region);
	if (!$view_capability)  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('refschooltype','block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

	$currenttab = 'refschooltype';
    include('tabs.php');

    if ($recs = data_submitted())  {
		// print_r($recs); echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), "points.php?rid=0&yid=$yid");
		$redirlink = "refschooltype.php";
        $st = array();
		foreach($recs as $fieldname => $compname)	{
			if ($compname != '')	{
	            $mask = substr($fieldname, 0, 4);
	            if ($mask == 'num_')	{
	            	$ids = explode('_', $fieldname);
	            	$compid = $ids[1];
                    $st[$compid]->name = $compname;
                } else if ($mask == 'cod_')	{
	            	$ids = explode('_', $fieldname);
	            	$compid = $ids[1];
                    $st[$compid]->cod = $compname;
                    
                }
            }
        }
        
        foreach ($st as $compid => $st1) {         
        	if (record_exists('monit_school_type', 'id', $compid))	{
        	    $st1->id = $compid;
       			if (!update_record('monit_school_type', $st1))    {
       			  error(get_string('errorinupdatingingcomponent','block_mou_school'), $redirlink);
       			}
        	} else {
        	   $st1->is_att_type = 1;
		       if (!insert_record('monit_school_type', $st1))	{
				    error(get_string('errorinaddingcomponent','block_mou_school'), $redirlink);
			   }

        	}
       }
	    // notice(get_string('succesavedata','block_mou_school'), $redirlink);
		redirect($redirlink, get_string('succesavedata','block_monitoring'), 0);
	}

    echo  '<form name="components" method="post" action="refschooltype.php">';
  	$table =  table_refschooltype();
    print_color_table($table);
	// $options = array('rid' => $rid, 'oid' => 0, 'yid' => $yid, 'typeou' => $typeou);
    echo  '<div align="center">';
    if ($edit_capability)   {
        $options = array();
        print_single_button("refschooltype.php", $options, get_string('savechanges'));
    }    
    // $options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'typeou' => $typeou, 'sesskey' => $USER->sesskey, 'action' => 'all');
    // print_single_button("refschooltype.php", $options, get_string('shawoallou', 'block_mou_ege'));
    echo  '</div></form>';

    print_footer();


function table_refschooltype()
{
	global $CFG, $edit_capability;

    $table = new stdClass();
    $table->head  = array (	get_string('name', 'block_mou_school'), "Код",  get_string('action', 'block_mou_school'));
    $table->align = array ("left", "center", "center");
    $table->class = 'moutable';
  	$table->width = '40%';
    $table->size = array ('70%', '20%', '10%');

	$component = get_records_sql ("SELECT id, name, cod FROM {$CFG->prefix}monit_school_type
 								   WHERE is_att_type=1
								   ORDER BY id");

	if ($component)	{
			foreach ($component as $comp) {

				$insidetable = "<input type=text  name=num_{$comp->id} size=80 value=\"{$comp->name}\">";
                $insidetable2 = "<input type=text  name=cod_{$comp->id} size=3 value=\"{$comp->cod}\">";

				$title = get_string('deleteitem','block_mou_att');
                if ($edit_capability)   {
			         $strlinkupdate  = "<a title=\"$title\" href=\"deleteitem.php?part=st\">";
                     $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
                } else {
                     $strlinkupdate = '';
                }     

				$table->data[] = array ($insidetable, $insidetable2, $strlinkupdate);
			}
	}
    $insidetable = "<input type=text  name=num_0 size=80 value=''>";
	$table->data[] = array ($insidetable, '');

    return $table;
}	

?>
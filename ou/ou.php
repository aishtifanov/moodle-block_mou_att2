<?php // $Id: ou.php,v 1.4 2011/05/17 14:19:29 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');

    $rid = optional_param('rid', 0, PARAM_INT);       // Rayon id
    $oid = optional_param('oid', 0, PARAM_INT);       // OU id
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
    $typeou = optional_param('typeou', '-');       // Type OU
	$action   = optional_param('action', '');

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

/*
	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editou', $context_rayon);

    $context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    $edit_capability_region = has_capability('block/mou_att2:editou', $context_region); 
*/
    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("ou.php?oid=0&yid=$yid&rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("ou.php?rid=$rid&yid=$yid&oid=0&typeou=", $rid, $typeou);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('ou','block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);

    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlistrayons;
	echo $strlisttypeou;
	// echo $typeou;
	if ($typeou != '-')	{
		if ($strlistou = listbox_ou_att("ou.php?rid=$rid&yid=$yid&typeou=$typeou&oid=", $rid, $typeou, $oid, $yid))	{ 
			echo $strlistou;
		} else {
			echo '</table>';
			notice(get_string('ounotfound', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid");
		}	
	} 
	echo '</table>';
	
	if ($rid != 0 && $typeou != '-')   {

        if ($action == 'all')   {
            $table =  table_ou_all($rid, $typeou, $yid);
            print_color_table($table);
        }   
    
        if ($oid != 0) {
	
        	// print_tabs_years($yid, "ou.php?rid=$rid&typeou=$typeou&oid=$oid&yid=");
            get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
            
        	$context = get_context_instance($CONTEXT_OU, $oid);
            $view_capability = has_capability('block/mou_att2:viewou', $context);
            $edit_capability = has_capability('block/mou_att2:editou', $context);
        	if ($view_capability || $edit_capability_region || $edit_capability_rayon)	{
        	
            	print_tabs_years_ou("ou.php?", $rid, $typeou, $oid, $yid);
            	$table =  table_ou($rid, $typeou, $oid, $yid);
            	print_color_table($table);
        	} else {
        		error(get_string('permission', 'block_mou_school'), '../index.php');
        	}
        } 
        
        
        get_constants_rayon($typeou, $CONTEXT_RAYON, $tablename);
        $context = get_context_instance($CONTEXT_RAYON, $rid);
        $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);        
   		echo '<table align="center" border=0><tr><td>';
		if ($edit_capability)	{
			$options = array('rid' => $rid, 'oid' => 0, 'yid' => $yid, 'typeou' => $typeou);
		    print_single_button("editou.php", $options, get_string('addou','block_monitoring'));
			echo '</td><td>';
		}
        $options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'typeou' => $typeou, 'sesskey' => $USER->sesskey, 'action' => 'all');
	    print_single_button("ou.php", $options, get_string('shawoallou', 'block_mou_att'));
		echo '</td></tr></table>';
    }    
    
	
    print_footer();


function table_ou($rid, $typeou, $oid, $yid)
{
	global $CFG, $edit_capability;
	
	$curryearid = get_current_edu_year_id();
	$admin_is = isadmin();
	
    $numberf = get_string('symbolnumber', 'block_monitoring');;
    $strname = get_string("name");
    $strheadname = get_string('directorschool', 'block_monitoring');
	$strphone = get_string('telnum','block_monitoring');
 	$straddress = get_string('realaddress','block_monitoring');
	$straction = get_string("action","block_monitoring");

    $table = new stdClass();
    $table->head  = array ($numberf, $strname,
						   $strheadname, $strphone, $straddress, '№ лицензии ОД', 'WWW', 'Email', $straction);
    $table->align = array ('center', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'center');
    $table->class = 'moutable';

  	get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);

	if (!$ou = get_record_select($tablename, "id = $oid AND isclosing = 0", 'id, number, name, fio, phones, realaddress, numlicense, www, email'))   {
	   return $table;
	}
	
	$ouname = "<strong>$ou->name</strong></a>&nbsp;";
		
	$strlinkupdate = '';
    
	if ($edit_capability)	{
		$title = get_string('editou','block_monitoring');		
		$strlinkupdate .= "<a title=\"$title\" href=\"editou.php?typeou=$typeou&rid=$rid&oid=$oid&yid=$yid\">";
		$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
    	if ($context = get_record('context', 'contextlevel', $CONTEXT_OU, 'instanceid', $ou->id)) {
    		$title = get_string('assignroles','role');
    	    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_att2/roles/assign.php?contextid={$context->id}&rid=$rid&oid=$oid&yid=$yid&typeou=$typeou\">";
    		$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/roles.gif\" alt=\"$title\" /></a>&nbsp;";
    	}	
		$title = get_string('deleteou','block_monitoring');
	    $strlinkupdate .= "<a title=\"$title\" href=\"delou.php?typeou=$typeou&rid=$rid&oid=$oid&yid=$yid\">";
		$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
	}	
        
	$hname = $ou->fio;
	$phone = $ou->phones;
    $wwwwww = "<a target=\"_blank\" href=\"$ou->www\">$ou->www</a>";
    $emaill = "<a href=\"mailto:$ou->email\">$ou->email</a>";
        //		   print_r($school->number);
        
    $ouname = "<a href=\"../staff/staffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid\">" . $ouname . '</a>'; 
	$table->data[] = array ($ou->number, $ouname, $hname, $phone, $ou->realaddress, $ou->numlicense, $wwwwww, $emaill, $strlinkupdate);
	
	return $table;
}	


// Print tabs years with auto generation link to OU
function print_tabs_years_ou($link, $rid, $typeou, $oid, $yid)
{
	global $CFG;
	
	get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
	
	$toprow1 = array();

	$uniqueconstcode = 0;
   	if ($rid != 0 && $oid != 0)	{
   		if ($ou = get_record_select($tablename, "id = $oid", 'id, uniqueconstcode'))		{
			$uniqueconstcode = $ou->uniqueconstcode;   			
   		}
   	} 

    if ($years = get_records('monit_years'))  {
    	foreach ($years as $year)	{
    		$fulllink = $link . "rid=$rid&typeou=$typeou&oid=$oid&yid=" . $year->id;
	    	if ($uniqueconstcode != 0)	{
				if ($ou = get_record_select($tablename, "uniqueconstcode=$uniqueconstcode AND yearid = {$year->id}", 'id, rayonid'))	{
					$fulllink = $link . "rid={$ou->rayonid}&typeou=$typeou&oid={$ou->id}&yid={$year->id}";
				} else {
					$fulllink = $link . "rid=$rid&typeou=$typeou&oid=0&yid={$year->id}";
				}	
	    	}
 	        $toprow1[] = new tabobject($year->id, $fulllink, get_string('uchyear', 'block_monitoring', $year->name));
	    }
  	}
    $tabs1 = array($toprow1);

   //  print_heading(get_string('terms','block_dean'), 'center', 4);
	print_tabs($tabs1, $yid, NULL, NULL);
}


function table_ou_all($rid, $typeou, $yid)
{
	global $CFG, $edit_capability_rayon, $edit_capability_region;

    get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
	
    $numberf = get_string('symbolnumber', 'block_monitoring');;
    $strname = get_string("name");
    $strheadname = get_string('directorschool', 'block_monitoring');
	$strphone = get_string('telnum','block_monitoring');
 	$straddress = get_string('realaddress','block_monitoring');
	$straction = get_string("action","block_monitoring");

    $table = new stdClass();
    $table->head  = array ($numberf, $strname,
						   $strheadname, $strphone, $straddress, '№ лицензии ОД', 'WWW', 'Email', $straction);
    $table->align = array ('center', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'center');
    $table->class = 'moutable';

  	// get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
    $edutype = get_config_typeou($typeou);
    
    if ($edit_capability_region)    {
        $rayonselect = '';
        $countall = count_records_sql("SELECT count(id) FROM {$CFG->prefix}{$edutype->tblname} WHERE typeinstitution=$edutype->id and yearid=$yid");
        if ($countall > 100)    {
            $rayonselect = "AND rayonid = $rid";
        }
    } else {
        $rayonselect = "AND rayonid = $rid";
    }
    
	if (!$ous = get_records_select($edutype->tblname, "typeinstitution=$edutype->id AND isclosing = 0 and yearid=$yid $rayonselect ", 'rayonid, number', 
        'id, number, name, fio, phones, realaddress, numlicense, www, email'))   {
	   return $table;
	}
	
    foreach ($ous as $ou)   {
    	// $ouname = "<strong>$ou->name</strong></a>&nbsp;";
        $context = get_context_instance($CONTEXT_OU, $ou->id);
    	$oid = 	$ou->id;
    	$strlinkupdate = '';
        
    	if ($edit_capability_region || $edit_capability_rayon)	{
    		$title = get_string('editou','block_monitoring');		
    		$strlinkupdate .= "<a title=\"$title\" href=\"editou.php?typeou=$typeou&rid=$rid&oid=$oid&yid=$yid\">";
    		$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
        	if ($context = get_record('context', 'contextlevel', $edutype->context, 'instanceid', $ou->id)) {
        		$title = get_string('assignroles','role');
        	    $strlinkupdate .= "<a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/mou_att2/roles/assign.php?contextid={$context->id}&rid=$rid&oid=$oid&yid=$yid&typeou=$typeou\">";
        		$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/roles.gif\" alt=\"$title\" /></a>&nbsp;";
        	}	
    		$title = get_string('deleteou','block_monitoring');
    	    $strlinkupdate .= "<a title=\"$title\" href=\"delou.php?typeou=$typeou&rid=$rid&oid=$oid&yid=$yid\">";
    		$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
    	}	
            
    	$hname = $ou->fio;
    	$phone = $ou->phones;
        $wwwwww = "<a target=\"_blank\" href=\"$ou->www\">$ou->www</a>";
        $emaill = "<a href=\"mailto:$ou->email\">$ou->email</a>";
            //		   print_r($school->number);
    	$table->data[] = array ($ou->number, $ou->name, $hname, $phone, $ou->realaddress, $ou->numlicense, $wwwwww, $emaill, $strlinkupdate);
    }    
	
	return $table;
}	


?>
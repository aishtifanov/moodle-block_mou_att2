<?php // $Id: meeting.php,v 1.5 2012/05/18 11:47:12 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib_att2.php');
    
    $level  = optional_param('level', 0, PARAM_INT);
    $rid = optional_param('rid', 0, PARAM_INT);          // Rayon id
    $nyear = optional_param('nyear', 0, PARAM_INT);		// Numberofyear

    if ($nyear == 0)	{
    	$nyear = get_current_edu_year_id();
    }
    
    $strlistbox = listbox_rayons_att("", $rid);
    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
	$context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    $view_capability = has_capability('block/mou_att2:viewmeetingak', $context_region);
    if ($rid == 0 && $strlistbox !== false) {
        $view_capability = true;
    }
	if (!$edit_capability_region && !$edit_capability_rayon && !$view_capability)  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
    
	$strtitle = get_string('title2', 'block_mou_att');
	$strgak  = get_string('meetinggak', 'block_mou_att');
    $stroldak = get_string('meeting2008', 'block_mou_att');
   
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strgak , 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strgak, $SITE->fullname, $navigation, "", "", true, "&nbsp;");
    
    // $strnever = get_string('never');

    $toprow = array();
    $toprow[] = new tabobject('ak0', "meeting.php?level=0", $strgak);
    $toprow[] = new tabobject('ak8', "meeting.php?level=8", $stroldak);
    $tabs = array($toprow);
    print_tabs($tabs, 'ak'.$level, NULL, NULL);

    if ($level == 0)    {
        echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
        listbox_years("meeting.php?rid=$rid&level=$level&nyear=", $nyear);
        echo '</table>';
        $table = table_gak($nyear);
    } else {
        $table = table_ak2008();
    }   

  	print_color_table($table);

	if ($edit_capability_region && $level == 0) 	{
   		echo '<table align="center" border=0><tr><td>';
		$options = array('level' => $level, 'sesskey' => $USER->sesskey, 'mode' => 'new');
	    print_single_button("editmeeting.php", $options, get_string('addmeeting', 'block_mou_att'));
		echo '</td><td>';
		echo '</td></tr></table>';
     }

//    echo '<div align=center><b>'. get_string('attentionstaff', 'block_mou_att') . '</b></div>';
    print_footer();




function table_gak($nyear)
{
    global $CFG, $edit_capability_region;

    $table = new stdClass();
    $table->head  = array ('№', // get_string('meeting', 'block_mou_att'),
                            get_string('date'), 
                            get_string('amountstaff', 'block_mou_att'), get_string('action', 'block_monitoring'));
    // $table->align = array ('right', 'left', 'center', 'center', 'center');
    // $table->size = array ('5%', '30%', '10%', '10%', '10%');
    $table->align = array ('right', 'center', 'center', 'center');
    $table->size = array ('5%', '10%', '10%', '10%');
    $table->class = 'moutable';
    $table->width = '40%';

    if ($nyear > 0) {
        if ($year = get_record_select('monit_years', "id = $nyear"))   {
            list($prevyear, $lastyear) = explode ('/', $year->name);
        } else {
            $curryear = current_edu_year();
            list($prevyear, $lastyear) = explode ('/', $curryear);
            $prevyear++;
            $lastyear++;
        }
    }
    $strselect = "level_ak=0 AND date_ak>'$prevyear-09-01' AND date_ak<'$lastyear-09-01'";
	if($meetings = get_records_select('monit_att_meeting_ak', $strselect, 'date_ak', 'id, edutypeid, name, date_ak, level_ak, type_ou')) {
		$num = 1;
        
        foreach ($meetings as $meeting) {
            
                $redirlink = "listrayons.php?mid={$meeting->id}&level=0";
                $meetingamount = count_records('monit_att_appointment', 'meetingid', $meeting->id);

				$title = get_string('listcertifiable', 'block_mou_att');
				$strlinkupdate = "<a title=\"$title\" href=\"$redirlink\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/report.gif\" alt=\"$title\" /></a>&nbsp;";

				if ($edit_capability_region ) 	{

						$title = get_string('editprofilemeeting', 'block_mou_att');
						$strlinkupdate .= "<a title=\"$title\" href=\"editmeeting.php?mode=edit&mid={$meeting->id}&level=0\">";
						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

                        if ($meetingamount == 0)    {
    						$title = get_string('deleteprofilemeeting','block_mou_att');
    					    $strlinkupdate .= "<a title=\"$title\" href=\"delmeeting.php?mid={$meeting->id}\">";
    						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
                        }    
				}

               	$meetingdate = convert_date($meeting->date_ak, 'en', 'ru');
       			$table->data[] = array ($num.'.', // $meeting->name,
 			     			"<div align=center><strong><a href=\"$redirlink\">". $meetingdate ."</a></strong></div>",
			       			"<div align=center><strong><a href=\"$redirlink\">". $meetingamount ."</a></strong></div>",
			       			$strlinkupdate);

				$num++;
        }
	}
    return $table;    
}


function table_ak2008()
{
    global $CFG, $edit_capability_region;

    $table = new stdClass();
    $table->head  = array ('№', get_string('meeting', 'block_mou_att'),
                            get_string('date'), 
                            get_string('amountstaff', 'block_mou_att'), get_string('action', 'block_monitoring'));
    $table->align = array ('right', 'left', 'center', 'center', 'center');
    $table->size = array ('5%', '30%', '10%', '10%', '10%');
    // $table->align = array ('right', 'center', 'center', 'center');
    // $table->size = array ('5%', '10%', '10%', '10%');
    $table->class = 'moutable';
    $table->width = '60%';

	if($meetings = get_records_select('monit_att_meeting_ak', 'level_ak > 0 OR (level_ak=0 AND date_ak<\'2011-08-01\')', 'level_ak, date_ak', 'id, edutypeid, name, date_ak, level_ak, type_ou')) {
		$num = 1;
       
        foreach ($meetings as $meeting) {
            
                $redirlink = "listrayons.php?mid={$meeting->id}&level={$meeting->level_ak}";
                $meetingamount = count_records('monit_att_appointment', 'meetingid', $meeting->id);

				$title = get_string('listcertifiable', 'block_mou_att');
				$strlinkupdate = "<a title=\"$title\" href=\"$redirlink\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/report.gif\" alt=\"$title\" /></a>&nbsp;";

				if ($edit_capability_region) 	{
/*
						$title = get_string('editprofilemeeting', 'block_mou_att');
						$strlinkupdate .= "<a title=\"$title\" href=\"editmeeting.php?mode=edit&mid={$meeting->id}\">";
						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
*/                        
                        if ($meetingamount == 0)    {
    						$title = get_string('deleteprofilemeeting','block_mou_att');
    					    $strlinkupdate .= "<a title=\"$title\" href=\"delmeeting.php?mid={$meeting->id}\">";
    						$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
                        }   
				}

               	// $meetingdate = convert_date($meeting->date_ak, 'en', 'ru');
                list($year, $month, $day) = explode("-", $meeting->date_ak);
                $meetingdate = get_string('nm_'.$month, 'block_mou_att') . ' ' . $year . 'г.';
                
       			$table->data[] = array ($num.'.', $meeting->name,
 			     			"<div align=center><strong><a href=\"$redirlink\">". $meetingdate ."</a></strong></div>",
			       			"<div align=center><strong><a href=\"$redirlink\">". $meetingamount ."</a></strong></div>",
			       			$strlinkupdate);

				$num++;
        }
	}
    return $table;    
}


?>


<?php // $Id: staffs.php,v 1.10 2014/06/16 12:00:06 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_att2.php');

    $rid = optional_param('rid', 0, PARAM_INT);       // Rayon id
    $oid = optional_param('oid', 0, PARAM_INT);       // OU id
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
    $typeou = optional_param('typeou', '03');       // Type OU
    $stft = optional_param('stft', 0, PARAM_INT);  	  // Type stafftype id
	$action   = optional_param('action', '');

	$currentyearid = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $currentyearid;
    }

    if ($action == 'word' || $action == 'writer') {
        $edutype = get_config_typeou($typeou);
    	$buffer = table_staff($rid, $oid, $yid, $typeou, $stft, $edutype, $action);
        // print_table_to_word($table, 1);
        $downloadfilename = 'staff_' .$oid; 
        print_table_staff_to_word($buffer, $downloadfilename, $action);
        exit();
	}

    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	$strlistrayons =  listbox_rayons_att("staffs.php?oid=0&yid=$yid&rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("staffs.php?rid=$rid&yid=$yid&oid=0&typeou=", $rid, $typeou);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlistrayons;
	echo $strlisttypeou;
	// echo $typeou;
	if ($rid != 0 && $typeou != '-')	{
		if ($strlistou = listbox_ou_att("staffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=", $rid, $typeou, $oid, $yid))	{ 
			echo $strlistou;
		} else {
			echo '</table>';
			notice(get_string('ounotfound', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid");
		}	
	} 
	echo '</table>';

	if ($rid != 0 && $typeou != '-')   {
    
        if ($oid != 0) {
	        /*   
            $teachersid = "teacher-".$oid.'-0-0';
            if ($user = get_record_sql("SELECT u.id, u.username FROM {$CFG->prefix}user u WHERE username = '$teachersid'")) {
           		delete_records('user', 'id', $user->id);
           		delete_records('monit_att_staff', 'userid', $user->id);
            }
            */

        	// print_tabs_years($yid, "ou.php?rid=$rid&typeou=$typeou&oid=$oid&yid=");
            $edutype = get_config_typeou($typeou);

        	$context = get_context_instance($edutype->context, $oid);
            $view_capability = has_capability('block/mou_att2:viewattestationuser', $context);
            $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
            $editownprofile = has_capability('block/mou_att2:editownprofile', $context);
        	if ($view_capability || $edit_capability_region || $edit_capability_rayon)	{
        	
            	// print_tabs_years_ou("ou.php?", $rid, $typeou, $oid, $yid);
               	$currenttab = 'shtatstaffs';
                include('tabs.php');
                
              	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
            	listbox_stafftype("staffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid&stft=", $stft, $edutype->id);
                echo '</table>'; 
                
                if ($stft != 0) {
                    $buffer = table_staff($rid, $oid, $yid, $typeou, $stft, $edutype);
                    print $buffer; 
                	// print_color_table($table);
                }    
        	} else {
        		error(get_string('permission', 'block_mou_school'), '../index.php');
        	}
            
            if ($stft > 0) {
           		echo '<p><table align="center" border=0><tr><td>';
        		if ($edit_capability || $edit_capability_region || $edit_capability_rayon)	{   
        			$options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'typeou' => $typeou, 'stft' => $stft, 'newuser' => true, 'sesskey' => $USER->sesskey);
        		    print_single_button("newstaff.php", $options, get_string('addstaff','block_mou_att'));
        			echo '</td><td>';
        		}
                $options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'stft' => $stft, 'typeou' => $typeou, 'sesskey' => $USER->sesskey, 'action' => 'word');
        	    print_single_button("staffs.php", $options, get_string('downloadword', 'block_mou_att'));
                $options['action'] = 'writer';
                echo '</td><td>';
                print_single_button("staffs.php", $options, get_string('downloadwriter', 'block_mou_att'));
        		echo '</td></tr></table>';
            }  else if ($stft == -1)    {
           		echo '<p><table align="center" border=0><tr><td>';
                $options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'stft' => $stft, 'typeou' => $typeou, 'sesskey' => $USER->sesskey, 'action' => 'word');
        	    print_single_button("staffs.php", $options, get_string('downloadword', 'block_mou_att'));
                echo '</td><td>';
                $options['action'] = 'writer';
                print_single_button("staffs.php", $options, get_string('downloadwriter', 'block_mou_att'));
        		echo '</td></tr></table>';
                
            }  
        }    
    }    

    print_footer();



function table_staff($rid, $oid, $yid, $typeou, $stft, $edutype, $action='')
{
	global $CFG, $USER, $edit_capability, $editownprofile, $view_capability; 
    
    $buffer = ''; 

    if ($stft == -1)    {
        $strselect = '';
    } else {
        $strselect = "AND stafftypeid=$stft";
    }
    $strnever = get_string('never');

    $table = new stdClass();
    $table->dblhead = new stdClass();
    if ($action == '') {
        $table->dblhead->head1  = array ('', get_string('fullname'), get_string ('sstaffapointment','block_mou_att'), get_string('action', 'block_monitoring'));
    } else {
        $table->dblhead->head1  = array ('', get_string('fullname'), get_string ('sstaffapointment','block_mou_att'));        
    }    
    $table->dblhead->span1  = array ("rowspan=2", "rowspan=2", "colspan=5", "rowspan=2");
    $table->dblhead->head2  = array ( get_string ('nameappointmnt','block_mou_att'), get_string ('qqualify','block_mou_att') . ',' . get_string("qqualify_date", 'block_mou_att'),  
                                      get_string ('qualifynow','block_mou_att'), get_string ('ddatecertifiable','block_mou_att'),  
                                      get_string('total_mark', 'block_mou_att'));
	$table->align = array ('center', 'left', 'left', 'center', 'center', 'center', 'center', 'center');
    $table->size = array ('5%', '20%', '15%', '10%', '10%', '5%', '5%', '5%');
	$table->columnwidth = array (5, 20, 10, 10, 10, 10, 10, 10);
    $table->class = 'moutable';
   	$table->width = '80%';

    foreach ($table->align as $key => $aa) {
        if ($aa && $aa != 'left') {
            $align[$key] = ' align='. $aa;
        } else {
            $align[$key] = '';
        }
    }

    foreach ($table->size as $key => $ss) {
        if ($ss) {
            $size[$key] = ' width="'. $ss .'"';
        } else {
            $size[$key] = '';
        }
    }
    
    $yearids = get_array_yearid_in_criteria();
    
    $tbl_monit_att   = $CFG->prefix.'monit_att_attestation'; 
    $tbl_monit_staff = $CFG->prefix.'monit_att_staff';
    
    $timenow = time();
    

/*    	
	$school = get_record('monit_school', 'id', $sid);
	$table->titles = array();
	$table->titles[] = get_string('title','block_mou_att') .': '.$school->name;
	$table->titles[] = $strstaffs;
	$table->titlesrows = array(30, 30);
	$table->worksheetname = 'staffs_'.$rid.'_'.$sid;
	$table->downloadfilename = $table->worksheetname;
*/
    $teachersql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.picture, u.lastaccess,
    			   s.id as staffid, s.birthday, s.graduate
                  FROM {$CFG->prefix}user u
	              LEFT JOIN {$CFG->prefix}monit_att_staff s ON s.userid = u.id
 	              WHERE s.{$edutype->idname}=$oid AND u.deleted = 0 AND u.confirmed = 1";
	$teachersql .= ' ORDER BY u.lastname, firstname';

    if($teachers = get_records_sql($teachersql)) {
        // print_r($teachersql);  echo '<hr>';
        // print_r($teachers);    exit();
        print_head_table_staff($table, $buffer);
        // echo '</table>'."\n";   exit ();
        foreach ($teachers as $teacher) {
            if (!$edit_capability)  {
                if ($editownprofile && $teacher->id != $USER->id) continue;
            } 
            
            $appointments = get_records_select ('monit_att_appointment', "staffid={$teacher->staffid} $strselect", 'id DESC', 'id, staffid, stafftypeid, meetingid, appointment, pedagog_time, standing, standing_this, qualify, qualify_date, qualifynow, place_advan_train, date_advan_train, total_mark');
            if ($appointments)  {
                $cnt = count($appointments);
                if ($cnt == 1)  {
                    $rowspan = '';
                } else {
                    $rowspan = " rowspan=$cnt ";
                }

                $buffer .= "<tr>"."\n";
                $foto = print_user_picture($teacher->id, 1, $teacher->picture, false, true);
                $buffer .= '<td '. $rowspan. $align[0].$size[0]. '>'. $foto .'</td>';
                // $strfullname = "<strong>".fullname($teacher)."</strong>";
                $strfullname = "<a href=\"viewstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou\"><strong>".fullname($teacher)."</a></strong>";
                $buffer .= '<td '. $rowspan. $align[1].$size[1]. '>'. $strfullname .'</td>';
               
                $i = 0;
                foreach ($appointments as $appointment) {
                    $i++;
                    
                    if ($i > 1) {
                        $buffer .= "<tr>"."\n";
                    }
                    $urlparam = "rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou&stft=$appointment->stafftypeid";
                    
                    if ($edit_capability || ($editownprofile && $teacher->id == $USER->id) || $view_capability) {
                        $strappointment = "<strong><a href=\"attestation.php?$urlparam\">".$appointment->appointment."</a></strong>";
                    } else {
                        $strappointment = "<strong>". $appointment->appointment . "</strong>";
                    }    
                    $buffer .= '<td '. $align[2].$size[2]. '>'.  $strappointment .'</td>';
                    
                    $qualifydate = convert_date($appointment->qualify_date, 'en', 'ru');
                    $qualifydate = $appointment->qualify . ', <br>' . $qualifydate; 
                    $buffer .= '<td '. $align[3].$size[3]. '>'. $qualifydate .'</td>';
                    $buffer .= '<td '. $align[4].$size[4]. '>'. $appointment->qualifynow .'</td>';
                    
                    
                    $YEARID_CRITERIA = $yearids[$appointment->stafftypeid];// get_last_yearid_in_criteria($appointment->stafftypeid);
                    
                    if ($meeting = get_record('monit_att_meeting_ak', 'id', $appointment->meetingid))	{

                            if ($meeting->date_ak > '2012-09-01')  {
                                $YEARID_CRITERIA = get_criteria_yearid_by_date_ak($meeting->date_ak);
                            }    
                        
    					  list($year, $month, $day) = explode("-", $meeting->date_ak);
                          $date_ak = convert_date($meeting->date_ak, 'en', 'ru');
    					  // print_r($meeting); echo '<br>';
    					  $timemeeting = make_timestamp($year, $month, $day, 12);
                          if ($timemeeting >  $timenow)  {
                                $strdate = '<b>' . $date_ak . '</b>'; 
                          } else {
                                $strdate = $date_ak;                            
                          }
    					  // $timenewver = make_timestamp(2009, 11, 10, 12);
    					  //if ($timemeeting <= $timenewver) $yid = 2;
    					  /* 	 
                    	  if ($meeting->level == 0)	{
    	                	  $date_ak = convert_date($meeting->date_ak, 'en', 'ru');
    	                  } else {
    					      $date_ak = get_string('nm_'.$month, 'block_mou_att') . ' ' . $year . ' г.';
    	                  }
                          */
    					  // $date_ak = get_string('meet'.$meeting->level, 'block_mou_att') . '<br>' . $date_ak;
                    } else {
                    	  $strdate = '-';
                    }

                    // print $strfullname;
                    // print_object($appointment);
                    // echo $YEARID_CRITERIA . '<br>';
                    
                    $minstatus = 1;
    				$countcriterions =  count_records('monit_att_criteria', 'stafftypeid', $appointment->stafftypeid, 'yearid', $YEARID_CRITERIA);
				    // echo '$countcriterions = '. $countcriterions . '  === ';
				    $strsql = "SELECT Min({$tbl_monit_att}.status) AS min
						       FROM $tbl_monit_staff INNER JOIN {$tbl_monit_att} ON {$tbl_monit_staff}.id = {$tbl_monit_att}.staffid
						       WHERE {$tbl_monit_staff}.userid = {$teacher->id} AND {$tbl_monit_att}.yearid = $YEARID_CRITERIA AND stafftypeid={$appointment->stafftypeid}";
                    
                    if ($rec = get_record_sql($strsql))  {
					   if (!empty($rec->min))	{
						  $minstatus = $rec->min;
						  // echo '$minstatus  = '. $minstatus . '  === ';
						  $strsql = "SELECT Count({$tbl_monit_att}.status) AS cnt
						  		     FROM $tbl_monit_staff INNER JOIN {$tbl_monit_att} ON {$tbl_monit_staff}.id = {$tbl_monit_att}.staffid
								     WHERE {$tbl_monit_staff}.userid = {$teacher->id} AND {$tbl_monit_att}.yearid = $YEARID_CRITERIA AND stafftypeid={$appointment->stafftypeid}";
						// echo '<br>'.$strsql.'<br>'; 		   
		                  if ($rec = get_record_sql($strsql))  {
							 if (!empty($rec->cnt))	{
								// echo '$rec->cnt  = '. $rec->cnt . '  === ';
								if ($countcriterions > $rec->cnt) $minstatus = 1;
			                 }    
						  }
	                   }
				    }

				    // echo '$minstatus  = '. $minstatus . '  === '; echo '<br>';
				    $strcolor = get_string('status'.$minstatus.'color', 'block_monitoring');
                    if ($action == '') {
                        $tdbgcolor = ' bgcolor="#'.$strcolor.'"';
                    } else {
                        $tdbgcolor = '';
                    }    
                   
                    $buffer .= '<td '. $align[5].$size[5]. '>'. $strdate .'</td>';
                    
                  
    				$strsql = "SELECT Sum({$tbl_monit_att}.mark) AS sum
    						   FROM $tbl_monit_staff INNER JOIN $tbl_monit_att ON {$tbl_monit_staff}.id = {$tbl_monit_att}.staffid
    						   WHERE {$tbl_monit_att}.yearid = $YEARID_CRITERIA AND {$tbl_monit_staff}.userid = {$teacher->id} AND stafftypeid={$appointment->stafftypeid}";
                    // print $strsql . '<hr>';
    			 	$sum = '';
    			 	// $sum .= '<br><small>2009: </small>';
                    if ($rec = get_record_sql($strsql))  {
    					if (empty($rec->sum))	{
    						$sum .= '-';
    	                } else {
    						$sum .= '<b>' . $rec->sum . '</b>';
    	                }
    				}
                    
                    $strsum = "<strong><a href=\"../staff/attestation.php?$urlparam\">".$sum."</a></strong>";
                    $buffer .= '<td '. $align[6].$size[6].$tdbgcolor. '>'. $strsum .'</td>';
                    
                    $strlinkupdate = '';
                    if ($edit_capability)   {
    				    $titl = get_string('editprofilestaff','block_mou_att');
    				    $strlinkupdate = "<a title=\"$titl\" href=\"viewstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou\">";
    				    $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$titl\" /></a>&nbsp;";
    
    					$titl = get_string('deleteprofilestaff','block_mou_att');
    				    $strlinkupdate .= "<a title=\"$titl\" href=\"delstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&stft=$stft&sesskey=$USER->sesskey&typeou=$typeou\">";
    					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$titl\" /></a>&nbsp;";
    
    			   		$titl = get_string('teachermoveschool','block_mou_att');
    				    $strlinkupdate .= "<a title=\"$titl\" href=\"movestaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&sesskey=$USER->sesskey&typeou=$typeou&stft=$stft\">";
    					$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_school/i/btn_move.png\" alt=\"$titl\" /></a>&nbsp;";
                    } else if ($editownprofile && $teacher->id == $USER->id) {
    				    $titl = get_string('editprofilestaff','block_mou_att');
    				    $strlinkupdate = "<a title=\"$titl\" href=\"viewstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou\">";
    				    $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$titl\" /></a>&nbsp;";
                    }       
                    if ($i == 1) {
                        if ($action == '') {
                            $buffer .= '<td '.  $rowspan. $align[7].$size[7]. '>'. $strlinkupdate .'</td>';
                        }    
                    }
                    $buffer .= '</tr>'."\n";                    
                }
            }  else if ($stft == -1)    {

                    $cnt = 1;
                    $rowspan = '';
                    $buffer .= "<tr>"."\n";
                    $foto = print_user_picture($teacher->id, 1, $teacher->picture, false, true);
                    $buffer .= '<td '. $rowspan. $align[0].$size[0]. '>'. $foto .'</td>';
                    // $strfullname = "<strong>".fullname($teacher)."</strong>";
                    $strfullname = "<a href=\"viewstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou\"><strong>".fullname($teacher)."</a></strong>";
                    $buffer .= '<td '. $rowspan. $align[1].$size[1]. '>'. $strfullname .'</td>';
                    $buffer .= '<td '. $align[2].$size[2]. '><b>ВНИМАНИЕ!<br> У сотрудника нет ни одной должности.</b></td>';
                    $buffer .= '<td '. $align[3].$size[3]. '>!</td>';
                    $buffer .= '<td '. $align[4].$size[4]. '>!</td>';
                    $buffer .= '<td '. $align[5].$size[5]. '>!</td>';
                    $buffer .= '<td '. $align[6].$size[6]. '>!</td>';                    
                    $strlinkupdate = '';
    
                    if ($edit_capability)   {
    				    $titl = get_string('editprofilestaff','block_mou_att');
    				    $strlinkupdate = "<a title=\"$titl\" href=\"viewstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou\">";
    				    $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$titl\" /></a>&nbsp;";
    
    					$titl = get_string('deleteprofilestaff','block_mou_att');
    				    $strlinkupdate .= "<a title=\"$titl\" href=\"delstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&stft=$stft&sesskey=$USER->sesskey&typeou=$typeou\">";
    					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$titl\" /></a>&nbsp;";
    
    			   		$titl = get_string('teachermoveschool','block_mou_att');
    				    $strlinkupdate .= "<a title=\"$titl\" href=\"movestaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&sesskey=$USER->sesskey&typeou=$typeou&stft=$stft\">";
    					$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_school/i/btn_move.png\" alt=\"$titl\" /></a>&nbsp;";
                    } else if ($editownprofile && $teacher->id == $USER->id) {
    				    $titl = get_string('editprofilestaff','block_mou_att');
    				    $strlinkupdate = "<a title=\"$titl\" href=\"viewstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou\">";
    				    $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$titl\" /></a>&nbsp;";
                    }   
                    if ($action == '') {    
                        $buffer .= '<td '.  $rowspan. $align[7].$size[7]. '>'. $strlinkupdate .'</td>';
                    }                        
                    $buffer .= '</tr>'."\n";                    
                }
                

        }
        $buffer .= '</table>'."\n";
    }
		
    return $buffer;
}


function print_head_table_staff($table, &$buffer)
{
    $table->cellpadding = '5';
    $table->cellspacing = '1';

    $buffer .= '<table width="'. $table->width . '" border=1 align=center ';
    $buffer .= " cellpadding=\"$table->cellpadding\" cellspacing=\"$table->cellspacing\"  class=\"$table->class\">\n"; //bordercolor=gray

    $countcols = 0;

    if (!empty($table->dblhead)) {
        $countcols = count($table->dblhead->head1);
        $buffer .= '<tr>';
        foreach ($table->dblhead->head1 as $key => $heading) {

            if (isset($table->dblhead->size[$key])) {
                $size[$key] = $table->dblhead->size[$key];
            } else {
                $size[$key] = '';
            }

            if (!isset($align[$key])) {
                $align[$key] = '';
            }
            if (isset($table->wraphead) && $table->wraphead == 'nowrap') {
            	$headwrap = ' nowrap="nowrap" ';
            } else 	{
            	$headwrap = '';
            }

            if (isset($table->dblhead->span1[$key])) {
            	$span1 = $table->dblhead->span1[$key];
            } else 	{
            	$span1 = '';
            }

            $buffer .= "<th $span1 ". $align[$key].$size[$key] . $headwrap . " class=\"header\">". $heading .'</th>'; // class="header c'.$key.'
			// $output .= '<th style="vertical-align:top;'. $align[$key].$size[$key] .';white-space:nowrap;" class="header c'.$key.'" scope="col">'. $heading .'</th>';
        }
        $buffer .= '</tr>'."\n";

        $countcols = count($table->dblhead->head2);
        $buffer .= '<tr>';
        foreach ($table->dblhead->head2 as $key => $heading) {

            if (!isset($size[$key])) {
                $size[$key] = '';
            }
            if (!isset($align[$key])) {
                $align[$key] = '';
            }
            if (isset($table->wraphead) && $table->wraphead == 'nowrap') {
            	$headwrap = ' nowrap="nowrap" ';
            } else 	{
            	$headwrap = '';
            }

            $buffer .= '<th '. $align[$key].$size[$key] . $headwrap . " class=\"header\">". $heading .'</th>'; // class="header c'.$key.'
			// $output .= '<th style="vertical-align:top;'. $align[$key].$size[$key] .';white-space:nowrap;" class="header c'.$key.'" scope="col">'. $heading .'</th>';
        }
        $buffer .= '</tr>'."\n";
    }
}



function print_table_staff_to_word($text, $downloadfilename, $action)
{
    global $CFG;
    
    if ($action == 'word')   {
        $ext = 'doc';
    } else if ($action == 'writer')   {
        $ext = 'odt';
    }
   
    $buffer = ''; 
    if ($ext == 'doc')  { 
        
    	header("Content-type: application/vnd.ms-word");
    	header("Content-Disposition: attachment; filename=\"$downloadfilename.doc\"");	
    	header("Expires: 0");
    	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    	header("Pragma: public");
        
       //$numcolumn = count ($table->columnwidth) - $lastcols;
        
        $buffer = '<html xmlns:v="urn:schemas-microsoft-com:vml"
    	xmlns:o="urn:schemas-microsoft-com:office:office"
    	xmlns:w="urn:schemas-microsoft-com:office:word"
    	xmlns="http://www.w3.org/TR/REC-html40">
    	<head>
    	<meta http-equiv=Content-Type content="text/html; charset=utf-8">
    	<meta name=ProgId content=Word.Document>
    	<meta name=Generator content="Microsoft Word 11">
    	<meta name=Originator content="Microsoft Word 11">
    	<title>';
    } else if ($ext == 'odt')   {
    	header("Content-type: application/vnd.oasis.opendocument.text");
    	header("Content-Disposition: attachment; filename=\"$downloadfilename.odt\"");	
    	header("Expires: 0");
    	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    	header("Pragma: public");
        
       //$numcolumn = count ($table->columnwidth) - $lastcols;
        
        $buffer = '<HTML><HEAD><META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8"><title>';
    }
               
   	$title = get_string('tableaccreditword', 'block_mou_ege');
	$buffer .= $title;
    	
   	$buffer .= '</title></head><body lang=RU>';

    $buffer .= $text;
      
    $buffer .= '</body></html>';
    
    print $buffer;
}
?>


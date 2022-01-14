<?PHP // $Id: listcertifiable.php,v 1.8 2014/06/16 12:00:06 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');    

    $mid = required_param('mid', PARAM_INT);          // Meeting id
    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $levelmonit  = required_param('level', PARAM_INT);
//   	$action = optional_param('action', '');
    $yid = optional_param('yid', 0, PARAM_INT);  	  //    	
    $numsym = optional_param('numsym', 0, PARAM_INT);  	  //
//    $vidou = optional_param('vidou', '-');       // Kind of OU
    $typeou = optional_param('typeou', '-');       // Type OU
    $cyid = optional_param('cyid', 0, PARAM_INT);  	  // Current criteria year id (бывший $YEARID_CRITERIA)

	$curryear = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $curryear; 
    }
    
    listbox_rayons_att("", $rid);
    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
	$context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    $view_capability = has_capability('block/mou_att2:viewmeetingak', $context_region);
	if (!$edit_capability_region && !$edit_capability_rayon && !$view_capability)  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
/*    
	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editmeetingak', $context_rayon);
    
	$context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    $edit_capability_region = has_capability('block/mou_att2:editmeetingak', $context_region); 
    $view_capability_region = has_capability('block/mou_att2:viewmeetingak', $context_region);
    if (!$edit_capability_rayon && !$view_capability_region)  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
 */
    if (!$meeting = get_record('monit_att_meeting_ak', 'id', $mid))	{
    	error ('Meeting not found!');
    }
        // print_r($meeting);

    if ($levelmonit == 0) {
	    $strmeeting = $meeting->name . ' ' . convert_date($meeting->date_ak, 'en', 'ru');
	} else {
        list($year, $month, $day) = explode("-", $meeting->date_ak);
        $strmeeting = $meeting->name . ': ' . get_string('nm_'.$month, 'block_mou_att') . ' ' . $year . ' г.';
	}

    $strtitle =  get_string('title2','block_mou_att');
    $strrayons =  get_string('rayons','block_monitoring');
    $strcommission = get_string('meetingcertcomm'.$levelmonit, 'block_mou_att');
    $strlist = get_string('listcertifiable', 'block_mou_att');
    
    $navlinks   = array();
    $navlinks[] = array('name' => $strtitle,       'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strcommission , 'link' => "meeting.php?level=$levelmonit", 'type' => 'misc');
    $navlinks[] = array('name' => $strmeeting,     'link' => "listrayons.php?level=$levelmonit&mid=$mid", 'type' => 'misc');
    $navlinks[] = array('name' => $strlist, 'link' => null, 'type' => 'misc');    
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strmeeting, $SITE->fullname, $navigation, "", "", true, "&nbsp;");

    if (!$rayon = get_record('monit_rayon', 'id', $rid))	{
    	error ('rayon not found!');
    }

    if ($typeou != '-')   {
        $outype = get_config_typeou($typeou);
        //$idou = $vidou. 'id';
        // $strselect = "and $idou > 0"; 
        $strselect = "and edutypeid=$outype->id";
    } else {
        $strselect = '';
    }    
    
    $strsql = "SELECT concat(s.id, a.id), s.userid FROM mdl_monit_att_staff s 
               INNER JOIN mdl_monit_att_appointment a ON s.id=a.staffid
               WHERE rayonid=$rayon->id and meetingid=$mid $strselect";  
    if($staffs = get_records_sql($strsql))   {
         $amount = count($staffs);
    }     

	print_heading($strmeeting, 'center', 2);

	print_heading($rayon->name, 'center', 3);
    
    $table = table_liststaff($mid, $rid, $levelmonit, $yid, $amount, $numsym, $typeou);

    print_color_table($table);

    print_footer();




function table_liststaff($mid, $rid, $levelmonit, $yid, $amount = 0, $numsym = 0, $typeou = '-')
{
    global $CFG, $USER, $rayon, $edit_capability_region, $edit_capability_rayon, $meeting;

    $table = new stdClass();
    $table->head  = array ('', get_string('fio', 'block_monitoring') . '<br>(' . get_string ('ou1','block_mou_att') . ')',
                        get_string ('nameappointmnt','block_mou_att'), get_string ('qqualify','block_mou_att') . ',' . get_string("qqualify_date", 'block_mou_att'),  
                        get_string ('qualifynow','block_mou_att'),  
                        get_string('total_mark', 'block_mou_att'), 
                        get_string('action', 'block_monitoring'));
/*    
    $table->dblhead->head1  = array ('', get_string('fullname'), get_string ('sstaffapointment','block_mou_att'), get_string('action', 'block_monitoring'));
    $table->dblhead->span1  = array ("rowspan=2", "rowspan=2", "colspan=5", "rowspan=2");
    $table->dblhead->head2  = array ( get_string ('nameappointmnt','block_mou_att'), get_string ('qqualify','block_mou_att') . ',' . get_string("qqualify_date", 'block_mou_att'),  
                                      get_string ('qualifynow','block_mou_att'), get_string ('ddatecertifiable','block_mou_att'),  
                                      get_string('total_mark', 'block_mou_att'));
*/                                      
	$table->align = array ('center', 'left', 'left', 'center', 'center', 'center', 'center', 'center');
    $table->size = array ('5%', '20%', '10%', '10%', '10%', '5%', '5%', '5%');
	$table->columnwidth = array (5, 20, 10, 10, 10, 10, 10, 10);
    $table->class = 'moutable';
   	$table->width = '80%';

    $schooltypes = get_records_select ('monit_school_type', 'is_att_type = 1', '', 'id,  cod');
    $typesou = array();
    foreach ($schooltypes as $schooltype)   {
        $typesou[$schooltype->id] =  $schooltype->cod;
    }   
        
    $strselect = '';    
    if ($amount > 50)   {    
        $arrRus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'К', 'Л', 'М',
                      'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Э', 'Ю', 'Я');
                      
        $toprow = array();
        foreach ($arrRus as $key => $aRus)	{
           $toprow[] = new tabobject($key, "listcertifiable.php?rid=$rid&mid=$mid&yid=$yid&level=$levelmonit&numsym=".$key, $aRus);
    	}	      
        $tabs = array($toprow);
        print_tabs($tabs, $numsym, NULL, NULL);
        
        $strselect = " AND lastname like '{$arrRus[$numsym]}%'"; 
    }
  /*  
    if ($vidou != '-')   {
        $idou = $vidou. 'id';
        $strselect .= " AND $idou > 0";
    }     
   */
   
    if ($typeou != '-')   {
        $outype = get_config_typeou($typeou);
        //$idou = $vidou. 'id';
        // $strselect = "and $idou > 0"; 
        $strselect = "and s.edutypeid=$outype->id";
    } else {
        $strselect = '';
    }    
    
    $yearids = get_array_yearid_in_criteria();
    
    $tbl_monit_att   = $CFG->prefix.'monit_att_attestation'; 
    $tbl_monit_staff = $CFG->prefix.'monit_att_staff';
                  
    // id, rayonid, edutypeid, userid, deleted, listegeids, listmiids, pswtxt, graduate, birthday, whatgraduated, yeargraduate, gos_awards, reg_awards, thanks, brevet, timemodified, speciality, totalstanding                  
    $strsql = "SELECT concat(u.id, a.id) as iid, u.id, u.username, u.firstname, u.lastname, u.email, u.picture, u.lastaccess,
    		   s.id as staffid, s.rayonid, s.schoolid, s.collegeid, s.udodid, s.douid, s.birthday, s.graduate, 
               a.stafftypeid, a.meetingid, a.appointment, a.qualify, a.qualify_date, a.qualifynow
               FROM {$CFG->prefix}user u LEFT JOIN 
	                {$CFG->prefix}monit_att_staff s ON s.userid = u.id INNER JOIN
                    {$CFG->prefix}monit_att_appointment a ON s.id=a.staffid
               WHERE s.rayonid=$rayon->id and a.meetingid=$mid $strselect
               ORDER BY u.lastname, u.firstname";
               
    // echo $strsql . '<br>';              

    if($teachers = get_records_sql($strsql)) 	{

            foreach ($teachers as $teacher) {
                // print_r($teacher); echo '<hr>';
                    
                $rid = $teacher->rayonid;
               
               /*
				if ($edit_capability_rayon && !$edit_capability_region) {
					if ($rayon_operator_is != $rid) continue;
				}
                */
                
                if (!empty($teacher->schoolid)) {
                    $ou = get_record_select('monit_school', "id = $teacher->schoolid", 'id, name, typeinstitution'); 
                } else if (!empty($teacher->collegeid)) {
                    $ou = get_record_select('monit_college', "id = $teacher->collegeid", 'id, name, typeinstitution');
                } else if (!empty($teacher->udodid)) {
                    $ou = get_record_select('monit_udod', "id = $teacher->udodid", 'id, name, typeinstitution');
                } else if (!empty($teacher->douid)) {
                    $ou = get_record_select('monit_education', "id = $teacher->douid", 'id, name, typeinstitution');
                }  else {
                    error('Not found OU!');
                }
                $oid = $ou->id;
                
                if (!empty($typesou[$ou->typeinstitution]))  {
                    $typeou = $typesou[$ou->typeinstitution];   
                } else {
                    $typeou = '03';
                }
                
                $edutype = get_config_typeou($typeou);   

                $cyid = $yearids[$teacher->stafftypeid];// get_last_yearid_in_criteria($appointment->stafftypeid);
                if ($meeting->date_ak > '2012-09-01')  {
                    $cyid = get_criteria_yearid_by_date_ak($meeting->date_ak);
                }    
                
                $urlparam = "cyid=$cyid&rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou&stft=$teacher->stafftypeid";
 
                $mesto = $ou->name;
                $foto = print_user_picture($teacher->id, 1, $teacher->picture, false, true);
                $strfullname = "<a href=\"../staff/viewstaff.php?$urlparam\"><strong>".fullname($teacher)."</a></strong><br><small>($mesto)</small>";
                $strappointment = "<strong><a href=\"../staff/attestation.php?$urlparam\">".$teacher->appointment."</a></strong>";
                $qualifydate = convert_date($teacher->qualify_date, 'en', 'ru');
                $qualifydate = $teacher->qualify . ', <br>' . $qualifydate; 
                                
                $stft = $teacher->stafftypeid;
                
                $minstatus = 1;
				$countcriterions =  count_records('monit_att_criteria', 'stafftypeid', $teacher->stafftypeid, 'yearid', $cyid);
			    // echo '$countcriterions = '. $countcriterions . '  === ';
			    $strsql = "SELECT Min({$tbl_monit_att}.status) AS min
					       FROM $tbl_monit_staff INNER JOIN {$tbl_monit_att} ON {$tbl_monit_staff}.id = {$tbl_monit_att}.staffid
					       WHERE {$tbl_monit_staff}.userid = {$teacher->id} AND {$tbl_monit_att}.yearid = $cyid AND stafftypeid={$teacher->stafftypeid}";
                
                if ($rec = get_record_sql($strsql))  {
				   if (!empty($rec->min))	{
					  $minstatus = $rec->min;
					  // echo '$minstatus  = '. $minstatus . '  === ';
					  $strsql = "SELECT Count({$tbl_monit_att}.status) AS cnt
					  		     FROM $tbl_monit_staff INNER JOIN {$tbl_monit_att} ON {$tbl_monit_staff}.id = {$tbl_monit_att}.staffid
							     WHERE {$tbl_monit_staff}.userid = {$teacher->id} AND {$tbl_monit_att}.yearid = $cyid AND stafftypeid={$teacher->stafftypeid}";
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
                // $tdbgcolor = ' bgcolor="#'.$strcolor.'"';
            
				$strsql = "SELECT Sum({$tbl_monit_att}.mark) AS sum
						   FROM $tbl_monit_staff INNER JOIN $tbl_monit_att ON {$tbl_monit_staff}.id = {$tbl_monit_att}.staffid
						   WHERE {$tbl_monit_att}.yearid = $cyid AND {$tbl_monit_staff}.userid = {$teacher->id} AND stafftypeid={$teacher->stafftypeid}";

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
                
                $strlinkupdate = '';
                if ($edit_capability_region || $edit_capability_rayon)   {
				    $titl = get_string('editprofilestaff','block_mou_att');
				    $strlinkupdate = "<a title=\"$titl\" href=\"../staff/viewstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou\">";
				    $strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$titl\" /></a>&nbsp;";

					$titl = get_string('deleteprofilestaff','block_mou_att');
				    $strlinkupdate .= "<a title=\"$titl\" href=\"../staff/delstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&stft=$stft&sesskey=$USER->sesskey&typeou=$typeou\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$titl\" /></a>&nbsp;";

			   		$titl = get_string('teachermoveschool','block_mou_att');
				    $strlinkupdate .= "<a title=\"$titl\" href=\"../staff/movestaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&sesskey=$USER->sesskey&typeou=$typeou&stft=$stft\">";
					$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_school/i/btn_move.png\" alt=\"$titl\" /></a>&nbsp;";
                } 

       			$table->data[] = array ($foto, $strfullname, $strappointment, $qualifydate, $teacher->qualifynow,
	                                    $strsum, $strlinkupdate);
                $table->bgcolor[] = array ('FFFFFF', 'FFFFFF', 'FFFFFF', 'FFFFFF', 'FFFFFF', $strcolor, 'FFFFFF');                        
            }
	} 
    return $table;
}

?>
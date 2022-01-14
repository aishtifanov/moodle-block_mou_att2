<?PHP // $Id: searchstaff.php,v 1.15 2014/06/16 12:00:06 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    // require_once('../../mou_school/lib_school.php');
    require_once('../lib_att2.php');
        
    define('ID_SCHOOLS_FOR_DELETED', '4585, 3990, 3385, 2769, 2116');

    $rid = optional_param('rid', 0, PARAM_INT);  // Rayon id
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
    $namestaff = optional_param('namestaff', '');		// staff lastname
   	$action = optional_param('action', '');
    $typeou = optional_param('typeou', '');       // Type OU
    $stft = optional_param('stft', 0, PARAM_INT);  	  // Type stafftype id
    $uid  = optional_param('uid', 0, PARAM_INT);
    $cyid = optional_param('cyid', 0, PARAM_INT);  	  // Current criteria year id (бывший $YEARID_CRITERIA)
    
    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

   
    $strlistrayons =  listbox_rayons_att("", $rid);
        
    if ($rid == 0 && $strlistrayons <> '')   {
        $rid = get_first_rid();
    }
    
    $admin_is = isadmin();
    $rayon_operator_is = $rid;
    
	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editattestationuser', $context_rayon);
    
	$context_region = get_context_instance(CONTEXT_REGION, 1);
	$edit_capability_region = has_capability('block/mou_att2:editattestationuser', $context_region);
    $view_capability = has_capability('block/mou_att2:viewmeetingak', $context_region);
    
    $strsearchstaff = get_string('searchstaff', 'block_mou_att');
    $strsearch = get_string("search");
    $searchtext1 = '';

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');
	$stronecriteria = get_string('onecriteria', 'block_mou_att');
        
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strsearchstaff , 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strsearchstaff, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    if (!$edit_capability_rayon && !$edit_capability_region && !$view_capability)  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
    }    

    if ($action == 'remove' && $uid != 0) 	{
        // $oid  = required_param('oid', PARAM_INT);
        set_field('monit_att_staff', 'schoolid', 0, 'userid', $uid);
    }
        
    if ($action == 'enrol' && $uid != 0) 	{
        $outype = get_config_typeou($typeou);
        
        $ctxcatblok = get_context_instance (CONTEXT_COURSECAT, 4);
        $ctxcatbase = get_context_instance (CONTEXT_COURSECAT, $outype->category);  
        $roleid = 5; // student
        if (role_assign($roleid, $uid, 0, $ctxcatblok->id)) {
            notify("Учитель с идентификатором $uid зарегистрирован в категории тестов с номером $ctxcatblok->id.", 'green');            
        } else {
            notify("Учитель с идентификатором $uid не зарегистрирован в категории тестов с номером $ctxcatblok->id.");            
        }
        if (role_assign($roleid, $uid, 0, $ctxcatbase->id)) {
            notify("Учитель с идентификатором $uid зарегистрирован в категории тестов с номером $ctxcatbase->id.", 'green'); 
        } else {
            notify("Учитель с идентификатором $uid не зарегистрирован в категории тестов с номером $ctxcatbase->id.");
        }
    }    
        
    if (isset($action) && !empty($action)) 	{

        // echo $namestaff . '<hr>';

	    if (isset($namestaff) && !empty($namestaff)) 	{
		     $searchtext1 = $namestaff;
	         $teachersql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.city, u.picture,
        			  			 t.id as staffid, t.userid, t.rayonid, t.schoolid, t.collegeid, t.udodid, t.douid, t.birthday, t.graduate, t.edutypeid
 	                        FROM {$CFG->prefix}user u, {$CFG->prefix}monit_att_staff t
	                       WHERE (t.userid = u.id) AND (u.lastname LIKE '$namestaff%') AND (u.deleted = 0) AND (u.confirmed = 1)";
/*                           
			 if ($edit_capability_rayon && !$edit_capability_region) {
				 $teachersql .= " AND (t.rayonid = $rayon_operator_is) ";
			 }
*/             
	   	 	 $teachersql .= "ORDER BY u.lastname";
             // echo $teachersql;
 	         $teachers = get_records_sql($teachersql);
	    }

        if(!empty($teachers)) 	{

         	if (count($teachers) > 100)  {
         		error(get_string('errorverybigcount', 'block_mou_att'), 'searchstaff.php');
         	}

       		$strappointment = get_string('appointments', 'block_mou_att');
		    $straction = get_string('action', 'block_monitoring');
		    $strschool = get_string('school', 'block_monitoring');
            $table = new stdClass();
       	    $table->head  = array ($strschool, '', get_string('fullname'), 
                                    get_string ('nameappointmnt','block_mou_att'), 
                                    get_string ('qqualify','block_mou_att') . ',' . get_string("qqualify_date", 'block_mou_att'),  
                                    get_string ('ddatecertifiable','block_mou_att'),  
                                    get_string('total_mark', 'block_mou_att'), 
                                    $straction); 
       	    						// get_string ('qualifynow','block_mou_att'), get_string('birthday', 'block_mou_att'), get_string('graduate', 'block_mou_att'), $strappointment,  $straction);
		    $table->align = array ('left', 'center', 'left', 'center', 'center', 'center', 'center', 'center');
	 		$table->class = 'moutable';

            $schooltypes = get_records_select ('monit_school_type', 'is_att_type = 1', '', 'id,  cod');
            $typesou = array();
            foreach ($schooltypes as $schooltype)   {
                $typesou[$schooltype->id] =  $schooltype->cod;
            }   

            $yearids = get_array_yearid_in_criteria();
            
            $tbl_monit_att   = $CFG->prefix.'monit_att_attestation'; 
            $tbl_monit_staff = $CFG->prefix.'monit_att_staff';
            
            $timenow = time();
            
            foreach ($teachers as $teacher) {

                // print_r($teacher); echo '<hr>';
               
                $rid = $teacher->rayonid;
                
                
				if ($edit_capability_rayon && !$edit_capability_region) {
					if (!($rayon_operator_is == $rid || $rid == 25)) continue;
				}

                
                $aou = array();
                
                if (!empty($teacher->schoolid)) {
                    $aou[] = get_record_select('monit_school', "id = $teacher->schoolid", 'id, name, typeinstitution'); 
                } 
                
                if (!empty($teacher->collegeid)) {
                    $aou[] = get_record_select('monit_college', "id = $teacher->collegeid", 'id, name, typeinstitution');
                } 
                
                if (!empty($teacher->udodid)) {
                    $aou[] = get_record_select('monit_udod', "id = $teacher->udodid", 'id, name, typeinstitution');
                } 
                
                if (!empty($teacher->douid)) {
                    $aou[] = get_record_select('monit_education', "id = $teacher->douid", 'id, name, typeinstitution');
                }  
                
                if (empty($aou))    {
                    notify('Не найдено ОУ для пользователя ' . fullname($teacher) . " ($teacher->email)");
                    continue;
                } 
                
                $ou = $aou[0];
                
                $oid = $ou->id;
                
                if (!empty($typesou[$ou->typeinstitution]))  {
                    $typeou = $typesou[$ou->typeinstitution];   
                } else {
                    $typeou = '03';
                }
                
                if ($teacher->edutypeid)    {
                    if (!empty($typesou[$teacher->edutypeid]))  {
                        $typeou = $typesou[$teacher->edutypeid];   
                    } else {
                        $typeou = '03';
                    }
                }
                
                $edutype = get_config_typeou($typeou);   
 
                $cntou = count($aou); 
                $mesto = ''; 
                foreach ($aou as $ou1)  {
                    $mesto .= $ou1->name . '(' . $teacher->city . ')<br>';
                    if ($cntou > 1) {
                        $mesto .= '<hr><b>';
                    } 
                }    
                if ($cntou > 1) {
                    $mesto .= '</b>';
                } 


                $appointments = get_records_select ('monit_att_appointment', "staffid={$teacher->staffid}", 'id DESC', 'id, staffid, stafftypeid, meetingid, appointment, pedagog_time, standing, standing_this, qualify, qualify_date, qualifynow, place_advan_train, date_advan_train, total_mark');                
                
				$strappointment =  $sum = $strdate = $strqualifydate = '';
                $i=0;
                foreach ($appointments  as $appointment)    {
                    
                    if ($i>0) {
                        $strappointment .= '<br><br>';
                        $sum .= '<br><br>';
                        $strdate .= '<br><br>';
                        $strqualifydate .= '<br><br>';
                    }

                    $cyid = $yearids[$appointment->stafftypeid];

                    if ($meeting = get_record('monit_att_meeting_ak', 'id', $appointment->meetingid))	{

                          if ($meeting->date_ak > '2012-09-01')  {
                              $cyid = get_criteria_yearid_by_date_ak($meeting->date_ak);
                          }    

    					  list($year, $month, $day) = explode("-", $meeting->date_ak);
                          $date_ak = convert_date($meeting->date_ak, 'en', 'ru');
    					  // print_r($meeting); echo '<br>';
    					  $timemeeting = make_timestamp($year, $month, $day, 12);
                          if ($timemeeting >  $timenow)  {
                                $strdate .= '<b>' . $date_ak . '</b>'; 
                          } else {
                                $strdate .= $date_ak;                            
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
                    	  $strdate .= '-';
                    }
                    
    				$strsql = "SELECT Sum({$tbl_monit_att}.mark) AS sum
    						   FROM $tbl_monit_staff INNER JOIN $tbl_monit_att ON {$tbl_monit_staff}.id = {$tbl_monit_att}.staffid
    						   WHERE {$tbl_monit_att}.yearid = $cyid AND {$tbl_monit_staff}.userid = {$teacher->id} AND stafftypeid={$appointment->stafftypeid}";
    
    			 	// $sum .= '<br><small>2009: </small>';
                    if ($rec = get_record_sql($strsql))  {
    					if (empty($rec->sum))	{
    						$sum .= '-';
    	                } else {
    						$sum .= '<b>' . $rec->sum . '</b>';
    	                }
    				}

                    $qualifydate = convert_date($appointment->qualify_date, 'en', 'ru');
                    $strqualifydate .= $appointment->qualify . ', ' . $qualifydate; 

                    // $strappointment .= $appointment->appointment;
                    $urlparam = "cyid=$cyid&rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou&stft=$appointment->stafftypeid";
                    $strappointment .= "<strong><a href=\"attestation.php?$urlparam\">".$appointment->appointment."</a></strong>";
                    $stft = $appointment->stafftypeid;
                    $i++;      
                }
                
               
                $redirlink = "attestation.php?cyid=$cyid&$rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&uid={$teacher->id}";

                $title = get_string('attteacher','block_mou_att');
				$strlinkupdate = "<a title=\"$title\" href=\"$redirlink\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/report.gif\" alt=\"$title\" /></a>&nbsp;";

				$title = get_string('editprofilestaff','block_mou_att');
				$strlinkupdate .= "<a title=\"$title\" href=\"viewstaff.php?attestation.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&uid={$teacher->id}\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

                /*
                if ($rid != 25) {
                    $rid2 = $rid; 
                } 
                */   
		   		$titl = get_string('teachermoveschool','block_mou_att');
			    $strlinkupdate .= "<a title=\"$titl\" href=\"movestaff.php?rid2=$rayon_operator_is&rid=$rid&oid=$oid&yid=$yid&typeou=$typeou&uid={$teacher->id}&sesskey=$USER->sesskey&stft=$stft\">";
				$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_school/i/btn_move.png\" alt=\"$titl\" /></a>&nbsp;";

		   		$titl = "Подписать учителя на тесты";
			    $strlinkupdate .= "<a title=\"$titl\" href=\"searchstaff.php?action=enrol&rid=$rid&namestaff=$namestaff&yid=$yid&typeou=$typeou&uid={$teacher->id}&sesskey=$USER->sesskey&stft=$stft\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/switch.gif\" alt=\"$titl\" /></a>&nbsp;";


			    if ($edit_capability_region) {
			        
					$title = get_string('deleteprofilestaff','block_mou_att');
				    $strlinkupdate .= "<a title=\"$title\" href=\"delstaff.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&uid={$teacher->id}&sesskey=$USER->sesskey&stft=$stft\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
        
                    $delids = explode(',', ID_SCHOOLS_FOR_DELETED);
                    if ($cntou > 1 || ($typeou == '03' && in_array($oid, $delids)) || $admin_is)  {
    					$titl = get_string('removefromschool','block_mou_att');
                        $strlinkupdate .= "<a title=\"$titl\" href=\"searchstaff.php?action=remove&rid=$rid&oid=$oid&namestaff=$namestaff&yid=$yid&typeou=$typeou&uid={$teacher->id}&sesskey=$USER->sesskey&stft=$stft\">";                    
    					$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/mou_att2/i/minus.gif\" alt=\"$titl\" /></a>&nbsp;";
                    }

				}

                $strfullname = "<a href=\"viewstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou\"><strong>".fullname($teacher)."</a></strong>";
                
       			$table->data[] = array ($mesto, print_user_picture($teacher->id, 1, $teacher->picture, false, true),
				    					$strfullname . '<br>('. $teacher->email. ')',
	                                    $strappointment,
	                                    $strqualifydate,
		                                // $appointment->qualifynow,
										$strdate,
                                        $sum,
										$strlinkupdate);
            }

           	print_heading(get_string('resultsearchstaff', 'block_mou_att'), 'center', 3);
           	print_color_table($table);

		}
		else {
			notify(get_string('staffnotfound','block_mou_att'));
			echo '<hr>';
		}

	}

	print_heading($strsearchstaff, 'center', 2);

	print_heading(get_string('searchstafflastname', 'block_mou_att'), 'center', 3);
    print_simple_box_start('center', '50%', 'white');
	echo '<center><form name="staffform1" id="staffform1" method="post" action="searchstaff.php?action=lastname">'.
		 get_string('lastname'). '&nbsp&nbsp'.
		 '<input type="text" name="namestaff" size="30" value="' . $searchtext1. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsearch . '" />'.
		 '</form></center>';
    print_simple_box_end();

    print_footer();
    
?>
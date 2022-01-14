<?php // $Id: sovmeststaff.php,v 1.4 2011/08/08 10:45:34 shtifanov Exp $

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
    $uid  = optional_param('uid', 0, PARAM_INT);    

	$currentyearid = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $currentyearid;
    }

    if ($action == 'excel') {
    	$table = table_staff($rid, $oid, $yid, $typeou, $stft);
        // print_table_to_word($table, 1);
        print_table_to_excel($table, 1);
        exit();
	} else if ($action == 'del')   {
	   $edutype = get_config_typeou($typeou);
	   if ($sharestaff = get_record_sql("SELECT ss.id FROM mdl_monit_att_staff s INNER JOIN 
                                         mdl_monit_att_staffshared ss ON s.id=ss.staffid 
                                         WHERE s.userid=$uid AND ss.{$edutype->idname} = $oid")) {
            delete_records_select('monit_att_staffshared', "id = $sharestaff->id");                                         
       }
                                         
	}

	$strlistrayons =  listbox_rayons_att("sovmeststaff.php?oid=0&yid=$yid&rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("sovmeststaff.php?rid=$rid&yid=$yid&oid=0&typeou=", $rid, $typeou);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('sovmeststaff','block_mou_att');

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
		if ($strlistou = listbox_ou_att("sovmeststaff.php?rid=$rid&yid=$yid&typeou=$typeou&oid=", $rid, $typeou, $oid, $yid))	{ 
			echo $strlistou;
		} else {
			echo '</table>';
			notice(get_string('ounotfound', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid");
		}	
	} 
	echo '</table>';

	if ($rid != 0 && $typeou != '-')   {
    
        if ($oid != 0) {
	
        	// print_tabs_years($yid, "ou.php?rid=$rid&typeou=$typeou&oid=$oid&yid=");
            $edutype = get_config_typeou($typeou);
            
        	$context = get_context_instance($edutype->context, $oid);
            $view_capability = has_capability('block/mou_att2:viewattestationuser', $context);
            $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
            $editownprofile = has_capability('block/mou_att2:editownprofile', $context);
        	if ($view_capability)	{
            	// print_tabs_years_ou("ou.php?", $rid, $typeou, $oid, $yid);
               	$currenttab = 'sovmeststaff';
                include('tabs.php');
                // notice(get_string('vstadii', 'block_mou_att'), "staff.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid");
                $table = table_sovmeststaff($rid, $oid, $yid, $edutype);
                print_color_table($table);  
        	} else {
        		error(get_string('permission', 'block_mou_school'), '../index.php');
        	}

       		echo '<p><table align="center" border=0><tr><td>';
    		if ($edit_capability)	{   
    			$options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'typeou' => $typeou, 'stft' => $stft, 'newuser' => true, 'sesskey' => $USER->sesskey);
    		    print_single_button("newsovmest.php", $options, get_string('addsovmest','block_mou_att'));
    			echo '</td><td>';
    		}
            $options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'typeou' => $typeou, 'sesskey' => $USER->sesskey, 'action' => 'excel');
    	    print_single_button("sovmeststaffs.php", $options, get_string('downloadexcel'));
    		echo '</td></tr></table>';
            
            echo '<p><center><i>Замечание: на данной странице формируется и отображается список тех сотрудников, которые ведут занятия в ОУ по совместительству и уже имеют свою учетную карточку в другом образовательном учреждении. При работе с совместителями возможно выполнение только двух функций: добавление совместителя и удаление.  Причем удаление совместителя никак не связано с его учетной карточкой. Т.е. учетная карточка по основному месту работы остается в целости и сохранности. Проводить аттестацию совместителя нельзя! Все аттестуемые сотрудники должны быть созданы как штатные. Формирование списка совместителей необходимо для системы "Электронной школы"</i><center></p>'; 

           
        }    
    }    

    print_footer();


// function table_sovmeststaff($rid, $oid, $yid, $typeou, $stft, $edutype)
function table_sovmeststaff( $rid, $oid, $yid, $edutype)
{
    global $CFG, $USER, $rayon, $edit_capability, $edit_capability_rayon;

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
     
    $yearids = get_array_yearid_in_criteria();
    
    $tbl_monit_att   = $CFG->prefix.'monit_att_attestation'; 
    $tbl_monit_staff = $CFG->prefix.'monit_att_staff';
    
    $strsql = "SELECT staffid FROM {$CFG->prefix}monit_att_staffshared WHERE $edutype->idname = $oid";
    if($staffs = get_records_sql($strsql)) 	{
        $schoolsarray = array();
	    foreach ($staffs as $sa)  {
	        $schoolsarray[] = $sa->staffid;
	    }
	    $liststaffid = implode(',', $schoolsarray);        
                      
    // id, rayonid, edutypeid, userid, deleted, listegeids, listmiids, pswtxt, graduate, birthday, whatgraduated, yeargraduate, gos_awards, reg_awards, thanks, brevet, timemodified, speciality, totalstanding                  
    $strsql = "SELECT concat(u.id, a.id) as iid, u.id, u.username, u.firstname, u.lastname, u.email, u.picture, u.lastaccess,
    		   s.id as staffid, s.rayonid, s.schoolid, s.collegeid, s.udodid, s.douid, s.birthday, s.graduate, 
               a.stafftypeid, a.meetingid, a.appointment, a.qualify, a.qualify_date, a.qualifynow
               FROM {$CFG->prefix}user u LEFT JOIN 
	                {$CFG->prefix}monit_att_staff s ON s.userid = u.id INNER JOIN
                    {$CFG->prefix}monit_att_appointment a ON s.id=a.staffid
               WHERE s.id in ($liststaffid)
               ORDER BY u.lastname, u.firstname";
               
     // echo $strsql . '<br>';              

    if($teachers = get_records_sql($strsql)) 	{

            foreach ($teachers as $teacher) {
                // print_r($teacher); echo '<hr>';
                    
               
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
                
                if (!empty($typesou[$ou->typeinstitution]))  {
                    $typeou = $typesou[$ou->typeinstitution];   
                } else {
                    $typeou = '03';
                }
                
                $edutype = get_config_typeou($typeou);   
                

                $mesto = $ou->name;
                $foto = print_user_picture($teacher->id, 1, $teacher->picture, false, true);
                $strfullname = "<strong>".fullname($teacher)."</strong><br><small>($mesto)</small>";
                $strappointment = "<strong>".$teacher->appointment."</strong>";
                $qualifydate = convert_date($teacher->qualify_date, 'en', 'ru');
                $qualifydate = $teacher->qualify . ', <br>' . $qualifydate; 
                                
                $stft = $teacher->stafftypeid;
                
                $YEARID_CRITERIA = $yearids[$teacher->stafftypeid];// get_last_yearid_in_criteria($appointment->stafftypeid);
				/*
                $oid = $ou->id;                
                $urlparam = "rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&typeou=$typeou&stft=$teacher->stafftypeid";
                $minstatus = 1;
				$countcriterions =  count_records('monit_att_criteria', 'stafftypeid', $teacher->stafftypeid, 'yearid', $YEARID_CRITERIA);
			    // echo '$countcriterions = '. $countcriterions . '  === ';
			    $strsql = "SELECT Min({$tbl_monit_att}.status) AS min
					       FROM $tbl_monit_staff INNER JOIN {$tbl_monit_att} ON {$tbl_monit_staff}.id = {$tbl_monit_att}.staffid
					       WHERE {$tbl_monit_staff}.userid = {$teacher->id} AND {$tbl_monit_att}.yearid = $YEARID_CRITERIA AND stafftypeid={$teacher->stafftypeid}";
                
                if ($rec = get_record_sql($strsql))  {
				   if (!empty($rec->min))	{
					  $minstatus = $rec->min;
					  // echo '$minstatus  = '. $minstatus . '  === ';
					  $strsql = "SELECT Count({$tbl_monit_att}.status) AS cnt
					  		     FROM $tbl_monit_staff INNER JOIN {$tbl_monit_att} ON {$tbl_monit_staff}.id = {$tbl_monit_att}.staffid
							     WHERE {$tbl_monit_staff}.userid = {$teacher->id} AND {$tbl_monit_att}.yearid = $YEARID_CRITERIA AND stafftypeid={$teacher->stafftypeid}";
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
                */
                            
				$strsql = "SELECT Sum({$tbl_monit_att}.mark) AS sum
						   FROM $tbl_monit_staff INNER JOIN $tbl_monit_att ON {$tbl_monit_staff}.id = {$tbl_monit_att}.staffid
						   WHERE {$tbl_monit_att}.yearid = $YEARID_CRITERIA AND {$tbl_monit_staff}.userid = {$teacher->id} AND stafftypeid={$teacher->stafftypeid}";

			 	$sum = '';
			 	// $sum .= '<br><small>2009: </small>';
                if ($rec = get_record_sql($strsql))  {
					if (empty($rec->sum))	{
						$sum .= '-';
	                } else {
						$sum .= '<b>' . $rec->sum . '</b>';
	                }
				}
                
                $strsum = "<strong>".$sum."</strong>";
                
                $strlinkupdate = '';
                if ($edit_capability)   {
					$titl = get_string('deleteprofilestaff','block_mou_att');
				    $strlinkupdate .= "<a title=\"$titl\" href=\"sovmeststaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$teacher->id}&stft=$stft&sesskey=$USER->sesskey&typeou=$typeou&action=del\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$titl\" /></a>&nbsp;";
                } 

       			$table->data[] = array ($foto, $strfullname, $strappointment, $qualifydate, $teacher->qualifynow,
	                                    $strsum, $strlinkupdate);
                //$table->bgcolor[] = array ('', '', '', '', '', $strcolor, '');                        
            }
	} 
    }
    return $table;
}

?>


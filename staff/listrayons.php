<?PHP // $Id: listrayons.php,v 1.7 2012/05/18 11:47:12 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');    

    $mid = required_param('mid', PARAM_INT);          // Meeting id
    $levelmonit  = required_param('level', PARAM_INT);
    $typeou = optional_param('typeou', '-');       // Type OU
    // $vidou = optional_param('vidou', '-');       // Kind of OU
    $rid = 1;
    
    $rid = optional_param('rid', 0, PARAM_INT);          // Rayon id
    $action = optional_param('action', '');
    
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

    if (!$meeting = get_record('monit_att_meeting_ak', 'id', $mid))	{
    	error ('Meeting not found!');
    }
        // print_r($meeting);
    if ($levelmonit == 0)	{
	    $strmeeting = $meeting->name . ' ' . convert_date($meeting->date_ak, 'en', 'ru');
	} else {
        list($year, $month, $day) = explode("-", $meeting->date_ak);
        $strmeeting = $meeting->name . ': ' . get_string('nm_'.$month, 'block_mou_att') . ' ' . $year . ' г.';
	}

    $strtitle =  get_string('title2','block_mou_att');
    $strrayons =  get_string('rayons','block_monitoring');
    $strcommission = get_string('meetingcertcomm'.$levelmonit, 'block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strcommission , 'link' => "meeting.php?level=$levelmonit", 'type' => 'misc');
    $navlinks[] = array('name' => $strmeeting, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strmeeting, $SITE->fullname, $navigation, "", "", true, "&nbsp;");

    if ($action <> '')  {
    	$rez = enrol_teacher ($mid, $levelmonit, $typeou, $action);
        redirect("listrayons.php?mid=$mid&rid=$rid&level=$levelmonit&typeou=$typeou", "Регистрация (подписка/отписка) учителей в тестах выполнена.", 5);
	}		

    $strlisttypeou =  listbox_typeou_att("listrayons.php?mid=$mid&level=$levelmonit&typeou=", $rid, $typeou);
    
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlisttypeou;
    // listbox_group_ou("listrayons.php?mid=$mid&level=$levelmonit&vidou=", $vidou);
	// echo $typeou;
	echo '</table>';

	if ($typeou != '-')   {
    
    	print_heading($strmeeting, 'center', 2);
    //	print_heading($strrayons, 'center', 3);
        $table = table_certifiable ($mid, $levelmonit, $typeou);
      	print_color_table($table);
        
        echo '<table align="center" border=0><tr><td>';
     	$options = array('rid' => $rid, 'mid' => $mid, 'level' => $levelmonit, 'typeou' => $typeou, 'action' => 'enroll');
     	print_single_button("listrayons.php", $options, 'Подписать аттестуемых на тесты');
        echo '</td><td>';
     	$options = array('rid' => $rid, 'mid' => $mid, 'level' => $levelmonit, 'typeou' => $typeou, 'action' => 'unenroll');
     	print_single_button("listrayons.php", $options, 'Отписать');
        echo '</td></tr></table>';
        
    }    

    print_footer();


function table_certifiable ($mid, $levelmonit, $typeou)
{
    global $CFG;
    
    $outype = get_config_typeou($typeou);

    $table = new stdClass();
    $table->head  = array ('№', get_string('rayon', 'block_monitoring'),
                            get_string('amountstaffvid', 'block_mou_att'),
                            get_string('amountstaffall', 'block_mou_att'));
    $table->align = array ('right', 'left', 'center', 'center');
    $table->size = array ('5%', '70%', '10%', '10%');
    $table->class = 'moutable';
    $table->width = '85%';

	$allrayons = get_records_sql("SELECT id, name, number FROM {$CFG->prefix}monit_rayon ORDER BY number");
	if ($allrayons)	 {
	    $total = $totalvid = 0;
		foreach ($allrayons as $rayon) 	{
		        $strsql = "SELECT concat(s.id, a.id), s.userid FROM mdl_monit_att_staff s 
                           INNER JOIN mdl_monit_att_appointment a ON s.id=a.staffid
                           WHERE rayonid=$rayon->id and meetingid=$mid";  
			    if($staffs = get_records_sql($strsql))   {
			         $amount = count($staffs);
                     $total += $amount;  
                     $redirlink = "listcertifiable.php?mid=$mid&rid={$rayon->id}&level=$levelmonit";
                     
                     $idou = $outype->idname; // $vidou. 'id';
    		         $strsql = "SELECT concat(s.id, a.id), s.userid FROM mdl_monit_att_staff s 
                               INNER JOIN mdl_monit_att_appointment a ON s.id=a.staffid
                               WHERE rayonid=$rayon->id and meetingid=$mid and edutypeid = $outype->id"; 
                               // WHERE rayonid=$rayon->id and meetingid=$mid and $idou > 0";  
    			     if($staffs = get_records_sql($strsql))   {
    			         $amountvid = count($staffs);
                         $totalvid += $amountvid;
                     } else {
                         $amountvid = 0;
                     }
                     $redirlink2 = "listcertifiable.php?mid=$mid&rid={$rayon->id}&level=$levelmonit&typeou=$typeou";                         
                     
                     
       				 $table->data[] = array ($rayon->number.'.',
 			     			"<div align=left><strong><a href=\"$redirlink2\">". $rayon->name ."</a></strong></div>",
                            "<div align=center><strong><a href=\"$redirlink2\">". $amountvid."</a></strong></div>",
			       			"<div align=center><strong><a href=\"$redirlink\">". $amount."</a></strong></div>");
			    }
        }
        $table->data[] = array ('', '<b>'.get_string('vsego', 'block_monitoring').'</b>', '<b>'.$totalvid.'</b>', '<b>'.$total.'</b>');
	}
    
    return $table;
 }


function enrol_teacher ($mid, $levelmonit, $typeou, $action)
{
    global $CFG;
    
    $outype = get_config_typeou($typeou);
    
    $ctxcatblok = get_context_instance (CONTEXT_COURSECAT, 4);
    $ctxcatbase = get_context_instance (CONTEXT_COURSECAT, $outype->category);  
    $roleid = 5; // student

	$allrayons = get_records_sql("SELECT id, name, number FROM {$CFG->prefix}monit_rayon ORDER BY number");
	if ($allrayons)	 {
		foreach ($allrayons as $rayon) 	{
	         $strsql = "SELECT concat(s.id, a.id), s.userid FROM mdl_monit_att_staff s 
                        INNER JOIN mdl_monit_att_appointment a ON s.id=a.staffid
                        WHERE rayonid=$rayon->id and meetingid=$mid and edutypeid = $outype->id"; 
                        // WHERE rayonid=$rayon->id and meetingid=$mid and $idou > 0";  
		     if($staffs = get_records_sql($strsql))   {
	            foreach ($staffs as $staff)    {
	                switch ($action) {
	                   case 'enroll':  
                                    if (!role_assign($roleid, $staff->userid, 0, $ctxcatblok->id)) {
                                        notify("Учитель с идентификатором $staff->userid не зарегистрирован в категории с номером $ctxcatblok->id."); 
                                    }
                                    if (!role_assign($roleid, $staff->userid, 0, $ctxcatbase->id)) {
                                        notify("Учитель с идентификатором $staff->userid не зарегистрирован в категории с номером $ctxcatbase->id."); 
                                    }
                       break;             
	                   case 'unenroll':  
                                    role_unassign($roleid, $staff->userid, 0, $ctxcatblok->id);
                                    role_unassign($roleid, $staff->userid, 0, $ctxcatbase->id);
                       break;
                    }                
		        }
            }
        }     
	}
    
    return true;
 }

?>
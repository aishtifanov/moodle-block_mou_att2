<?PHP // $Id: delstaff.php,v 1.7 2012/04/05 11:08:34 shtifanov Exp $

    require_once("../../../config.php");
//	require_once($CFG->libdir.'/adminlib.php');
    require_once('../../monitoring/lib.php');
	require_once('../lib_att2.php');

    define('ID_SCHOOL_FOR_DELETED', 2769); // 2116
    
    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = required_param('stft', PARAM_INT);  	  // Type stafftype id    
    $delete  = required_param('uid', PARAM_INT);
    $confirm = optional_param('confirm');    

    $edutype = get_config_typeou($typeou);
    
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);

	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editattestationuser', $context_rayon);

    $teacher = get_record_select('monit_att_staff', "userid = $delete", 'id, userid');
    if (!$user = get_record_sql("SELECT id, username, lastname, firstname, deleted, email FROM {$CFG->prefix}user WHERE id=$delete")) {
        error("No such user!", $redirlink);
    }
    $fullname = fullname($user);

	if ($appointments = get_records_select('monit_att_appointment', "staffid = $teacher->id", '', 'id, appointment'))    {
	   foreach ($appointments as $appointment)    {
	       $fullname .= ', ' . $appointment->appointment;
       }       
	}   

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');

    $redirlink = "staffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid&stft=$stft";
    
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $fullname, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $fullname, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    if (!$edit_capability)  {
		error(get_string('permission', 'block_mou_school'), $redirlink);
    }    
    
 	if ($delete and confirm_sesskey()) {              // Delete a selected user, after confirmation

        $primaryadmin = get_admin();
        if ($user->id == $primaryadmin->id) {
            error("You are not allowed to delete the primary admin user!", '', true);
        }

        if ($confirm != md5($delete)) {
            print_heading(get_string('deleteprofilestaff', 'block_mou_att'));
            $optionsyes  = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'typeou' => $typeou, 
                                 'stft' => $stft, 'uid'=>$delete,
            					'confirm'=>md5($delete), 'sesskey'=>sesskey());
	        notice_yesno(get_string('deletecheckfull', 'block_mou_att', "'$fullname'"), 'delstaff.php', 'staffs.php', $optionsyes, $optionsyes, 'post', 'get');

        } else if (data_submitted() and !$user->deleted) {
            
            if ($oid == ID_SCHOOL_FOR_DELETED) { //  || isadmin() ) {
                $updateuser = new object();
                $updateuser->id           = $user->id;
                $updateuser->deleted      = 1;
                $updateuser->username     = $user->username . '_' . time();  // Remember it just in case
                $updateuser->email        = '';               // Clear this field to free it up
                $updateuser->idnumber     = '';               // Clear this field to free it up
                $updateuser->timemodified = time();
                if (update_record('user', $updateuser)) {
                    // Removing a user may have more requirements than just removing their role assignments.
                    // Use 'role_unassign' to make sure that all necessary actions occur.
                    role_unassign(0, $user->id);
                    // remove all context assigned on this user?
                    // notify(get_string('deletedactivity', '', fullname($user, true)) );
              		delete_records('monit_att_staff', 'userid', $delete);
    		   		redirect($redirlink, get_string('deletedactivity', '', fullname($user, true)), 3);
                } else {
               		redirect($redirlink, get_string('deletednot', '', fullname($user, true)), 5);
                }
                
            } else {
/*                   
                   $att1 = get_records_select('monit_att_attestation', "staffid = $teacher->id", '', 'id');
                   if (!$edit_capability_rayon && $att1)  {
                       error(get_string('errordeletecardteacher', 'block_mou_att'), $redirlink);
                   }
*/        
               	   $teacher->rayonid  = 25;
               	   $teacher->schoolid = ID_SCHOOL_FOR_DELETED;
                   $teacher->collegeid =  0;
                   $teacher->udodid =  0;
                   $teacher->douid =  0;
              	
        	       if (!update_record('monit_att_staff', $teacher))	{
        				error(get_string('errorinupdateprofilepupil','block_mou_ege'));
         		   }
        
            	   $schoolout = get_record_select('monit_school', "id = ".ID_SCHOOL_FOR_DELETED, 'id, name');
            	 
            	   $msg = get_string('leavededactivityteacher', 'block_mou_school', fullname($user, true));
            	   $msg .= '"' . mb_substr($schoolout->name, 0,  64) . '... "';
        
                   add_to_log(1, 'mou_att', 'staff deleted', $redirlink . "&userid=$teacher->userid", $USER->lastname.' '.$USER->firstname); 
            	   redirect($redirlink, $msg, 3);
             }      
        }
    }

	print_footer();
?>
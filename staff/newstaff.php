<?php // $Id: newstaff.php,v 1.2 2011/06/09 11:22:48 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
	require_once('../lib_att2.php');

    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = required_param('stft', PARAM_INT);  	  // Type stafftype id    
    $newuser = optional_param('newuser', false);  // Add new user

    $edutype = get_config_typeou($typeou);
    
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');

    $redirlink = "staffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid&stft=$stft";
    
    $fullname = 'Фамилия Имя Отчество';
    
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $fullname, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $fullname, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    if (!$edit_capability)  {
		error(get_string('permission', 'block_mou_school'), $redirlink);
    }    

    if ($newuser and confirm_sesskey())   {           // Create a new user
        $rayon = get_record_select('monit_rayon', "id = $rid", "id, shortname");
          
          
        $strsql = "SELECT id FROM {$CFG->prefix}monit_att_staff WHERE {$edutype->idname}=$oid";
        if ($staffs = get_records_sql($strsql)) {
            $random = count($staffs)+1;
        } else {             
            /*
            $r1 =  mt_rand(); 
            $r2 =  mt_rand();
            $r3 =  rand(1, 9999);
            $random = md5 ($r1.$r2.$r3);
            */
            $random = 1;
        }    
        $pswtxt =  $rid. '-' . $oid . '-'.$stft. '-' . $random;
        $username = 'teacher'.$pswtxt;
        
        if (get_record_select('user', "username = '{$username}'"))  {
            $random++;
            $pswtxt =  $rid. '-' . $oid . '-'.$stft. '-' . $random;
            $username = 'teacher'.$pswtxt;
            if (get_record_select('user', "username = '{$username}'"))  {
                $random++;
                $pswtxt =  $rid. '-' . $oid . '-'.$stft. '-' . $random;
                $username = 'teacher'.$pswtxt;
            }
        }
          
        $user = new stdClass();  
        $user->auth      = "manual";
        $user->firstname = "Имя Отчество";
        $user->lastname  = "Фамилия";
        $user->username  = $username;
        $user->password  = hash_internal_user_password($pswtxt);
        $user->email     = "{$username}@temp.ru";
        $user->city      = $rayon->shortname;
        $user->country   = 'RU';
        $user->lang      = 'ru_utf8';
        $user->icq 		 = '';
        $user->skype	 = '';
        $user->yahoo 	 = '';
        $user->msn       = '';
        $user->display   = 1;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->mailformat    = 1;
        $user->maildigest    = 0;
        $user->autosubscribe = 1;
        $user->htmleditor    = 1;
        $user->emailstop     = 0;
        $user->trackforums   = 1;
        $user->confirmed = 1;
        $user->timemodified = time();

        // if (!$user = get_record("user", "username", "teacher"))	{
	        if (!$user->id = insert_record("user", $user)) 	{
 	           if (!$user = get_record_select("user", "username = '$username'", 'id ,username, lastname, firstname')) 	{   // half finished user from another time
                  $fullname = $username . ':' . fullname ($user);  
  	              error(get_string('existstaff', 'block_mou_att', $fullname), $redirlink);
   		       }
     	    }
     	// }

        $uid = $user->id;
        unset($user);
        if (!$teacher = get_record('monit_att_staff', 'userid', $uid))	{
            $teacher = new stdClass();
            $teacher->userid = $uid;
            $teacher->rayonid = $rid;
            $teacher->schoolid = 0;
            $teacher->collegeid = 0;
            $teacher->udodid = 0;
            $teacher->douid = 0;
            $teacher->{$edutype->idname} = $oid;
	        $teacher->edutypeid = $edutype->id;
            $teacher->stafftypeid = $stft;
            $teacher->pswtxt = $pswtxt;

            if (!add_staff($teacher, $redirlink)) {
                error("--> Can not add <b>teacher</b> in staff: $user->username ($user->lastname $user->firstname)"); //TODO: localize
            }
            unset($teacher);
        }
        redirect("registrationcard.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid&stft=$stft&uid=$uid", '', 0);
    }
?>
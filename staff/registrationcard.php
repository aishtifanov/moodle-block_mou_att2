<?php // $Id: registrationcard.php,v 1.2 2011/06/20 07:22:00 shtifanov Exp $

    require_once("../../../config.php");
    require_once("$CFG->libdir/gdlib.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_school/lib_school.php');
    require_once('../lib_att2.php');

    require_login();
    
    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);       // OU id
    $uid = required_param('uid', PARAM_INT);  // User id    
    $typeou = required_param('typeou');       // Type OU
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
    $newuser = optional_param('newuser', false);  // Add new user
    
    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }
    
    $edutype = get_config_typeou($typeou);
    
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
    $editownprofile = has_capability('block/mou_att2:editownprofile', $context);

    if (!$user = get_record_select('user', "id = $uid", 'id, auth, username, deleted, lastname, firstname, picture, description, city, country, address, phone1, phone2, icq, email, emailstop')) {
        error("No such user in this course");
    }

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');
    $fullname = fullname($user);

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => "staffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid", 'type' => 'misc');
    $navlinks[] = array('name' => $fullname, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $fullname, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    if (!$edit_capability && !($editownprofile && $uid == $USER->id))  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
    }

    $profile = new stdClass();
	$profile->fields = array('birthday', 'graduate', 'speciality', 'whatgraduated', 'yeargraduate', 'totalstanding', 'gos_awards', 'reg_awards'); // 'thanks', 'brevet');
	$profile->type   = array('date',     'text',     'text',       'text',          'text',         'real',          'text',       'text'); // , 'text', 'text');

    $numericfield = array('yeargraduate', 'pedagog', 'standing', 'standing_this', 'standing', 'standing_this', 'totalstanding');

    $listfields = implode(',', $profile->fields);
    $teacher = get_record_select('monit_att_staff', "userid = $uid", 'id, pswtxt, ' . $listfields);

    // $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$uid");
    $redirlink = "viewstaff.php?rid=$rid&oid=$oid&yid=$yid&uid=$uid&typeou=$typeou";

    $currenttab = 'registrationcard';
    include('tabsprofile.php');

   	$biguser = (array)$user + (array)$teacher;
   	$user = (object)$biguser;
    $user->staffid = $teacher->id;
    unset($biguser);
   	// print_r ($user);   	echo '<hr>';

    if (isadmin()) {             // Current user is an admin
         if ($mainadmin = get_admin()) {
               if ($user->id == $mainadmin->id) {  // Can't edit primary admin
                  print_error('adminprimarynoedit');
             }
         }
    }

    /// If data submitted, then process and store.
    if ($usernew = data_submitted()) {
        // print_r($usernew); echo '<hr>';

        if (update_user_staff($user, $usernew, $err))   {
            update_staff($user, $usernew, $profile);
            update_appointment($usernew);
        }

        if (!empty($err)) {
        	notify('<b>'.get_string("someerrorswerefound", 'block_mou_att').'</b>');
        } else {
            redirect($redirlink, get_string('succesavedata', 'block_mou_school'), 1);
        }



    }

    $personalprofile = get_string("personalprofile");
    $participants = get_string("participants");

    if ($user->deleted) {
        print_heading(get_string("userdeleted"));
    }

    $streditmyprofile = get_string("editmyprofile");
    $strparticipants = get_string("participants");
    $strnewuser = get_string("newuser");

    print_simple_box_start("center", '70%', 'white');

    include("regcardedit.php");

	print_simple_box_end();

	echo '<p><table align="center" border=0><tr><td>';
	$options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'typeou' => $typeou, 'uid' => $uid, 'sesskey' => $USER->sesskey);
    print_single_button("addappointment.php", $options, get_string('adddolgnost','block_mou_att'));
	echo '</td></tr></table>';

    print_footer();



function update_user_staff(&$user, $usernew, &$err)
{
    global $CFG, $USER;

   /*
    if (($USER->id <> $usernew->id) && !isadmin()) {
        error("You can only edit your own information");
    }
   */

    if (isset($usernew->password)) {
        unset($usernew->password);
    }

    // data cleanup
    // username is validated in find_regform_errors
    $usernew->country = 'RU';
    $usernew->lang    = 'ru_utf8';
    // $usernew->url     = clean_param($usernew->url,     PARAM_URL);
    $usernew->icq     = clean_param($usernew->icq, PARAM_INT);
    if (!$usernew->icq) {
        $usernew->icq = '';
    }
    $usernew->skype   = '';
    $usernew->yahoo   = '';
    // $usernew->aim   = clean_param($usernew->aim,   PARAM_CLEAN);
    $usernew->msn   = '';

    $usernew->mnethostid    = $CFG->mnet_localhost_id;
    $usernew->maildisplay   = 1;
    $usernew->mailformat    = 1;
    $usernew->maildigest    = 0;
    $usernew->autosubscribe = 1;
    $usernew->htmleditor    = 1;
    $usernew->emailstop     = 0;
    $usernew->trackforums   = 1;

    if (isset($usernew->timezone)) {
        if ($CFG->forcetimezone != 99) { // Don't allow changing this in any way
            unset($usernew->timezone);
        } else { // Clean up the data a bit, just in case of injections
            $usernew->timezone = str_replace(';', '',  $usernew->timezone);
            $usernew->timezone = str_replace('\'', '', $usernew->timezone);
        }
    }




	if (!get_magic_quotes_gpc()) {
        foreach ($usernew as $key => $data) {
            // $strinput = $usernew->gos_awards;    $strinput = trim($strinput);    $strinput = clean_text($strinput);    $strinput = stripslashes($strinput);     $strinput = addslashes($strinput);     echo $strinput . '<br>';
            $usernew->$key = addslashes(clean_text(stripslashes(trim($usernew->$key)), FORMAT_MOODLE));
        }
    } else {
        foreach ($usernew as $key => $data) {
            $usernew->$key = clean_text(trim($usernew->$key), FORMAT_MOODLE);
        }
    }

    // print_r($usernew); echo '<hr>';

    $usernew->lastname    = strip_tags($usernew->lastname);
    $usernew->firstname   = strip_tags($usernew->firstname);
    $usernew->secondname  = strip_tags($usernew->secondname);

    if (isset($usernew->username)) {
        $usernew->username = moodle_strtolower($usernew->username);
    }

    require_once($CFG->dirroot.'/lib/uploadlib.php');
    $um = new upload_manager('imagefile',false,false,null,false,0,true,true);


    if (find_regform_errors($user, $usernew, $err, $um, $profile)) {
        if (empty($err['imagefile']) && $usernew->picture = save_profile_image($user->id, $um)) {
            set_field('user', 'picture', $usernew->picture, 'id', $user->id);  /// Note picture in DB
        } else {
            if (!empty($usernew->deletepicture)) {
                set_field('user', 'picture', 0, 'id', $user->id);  /// Delete picture
                $usernew->picture = 0;
            }
        }

        $usernew->auth = $user->auth;
        $usernew->deleted = $user->deleted;
        $user = $usernew;
        return false;

    } else {

        $timenow = time();

        if (!$usernew->picture = save_profile_image($user->id,$um)) {
            if (!empty($usernew->deletepicture)) {
                set_field('user', 'picture', 0, 'id', $user->id);  /// Delete picture
                $usernew->picture = 0;
            } else {
                $usernew->picture = $user->picture;
            }
        }

        $usernew->timemodified = time();

         if (!empty($usernew->newpassword))  {
                $usernew->password = md5($usernew->newpassword);
         }
           // store forcepasswordchange in user's preferences
            if (!empty($usernew->forcepasswordchange)){
                set_user_preference('auth_forcepasswordchange', 1, $user->id);
            } else {
                unset_user_preference('auth_forcepasswordchange', $user->id);
            }

        $usernew->firstname  .= ' ' . $usernew->secondname;

        if (update_record("user", $usernew)) {
            add_to_log(1, 'mou_att', "user update", 'registrationcard.php', $USER->lastname.' '.$USER->firstname);
        } else {
            error("Could not update the user record ($user->id: $fullname)");
        }
   }
   return true;
}


function update_staff(&$teacher, $usernew, $profile)
{
    global $CFG, $USER, $edutype, $oid;

	$i = 0;
	foreach ($profile->fields as $pf)  {
		if (isset($usernew->{$pf}))  {
			$teacher->{$pf} = $usernew->{$pf};

			switch ($profile->type[$i]) {
				case 'text': case 'real':
				break;
				case 'date':
				    if ($usernew->{$pf} != '-' && !empty($usernew->{$pf})) {
					    $teacher->{$pf} = convert_date($usernew->{$pf}, 'ru', 'en');
					} else {
					    $teacher->{$pf} = '0000-00-00';
					}
				break;
			}

		} else {
			switch ($profile->type[$i]) {
				case 'text': $teacher->{$pf} = '-';
				break;
				case 'real': $teacher->{$pf} = 0;
				break;
			}
		}
		$i++;
	}

/*
	if (isset($usernew->date_ak_id))    {
		$teacher->date_ak_id = $usernew->date_ak_id;
	}
*/
	// if ($admin_is || $region_operator_is || $rayon_operator_is || $school_operator_is || $college_operator_is) {
	   if (!empty($usernew->newpassword))  {
            	$teacher->pswtxt = $usernew->newpassword;
        }
    // }

    $teacher1 = new stdClass();
	foreach ($teacher as $keyt => $atrib)	{
		if (isset($teacher->{$keyt}) && !empty($teacher->{$keyt})) 	{
			$teacher1->{$keyt} = $teacher->{$keyt};
		}
	}

    // print_r($teacher); echo '<hr>';
    // print_r($teacher1); echo '<hr>';

    $teacher1->userid = $teacher1->id;
    $teacher1->id = $teacher->staffid;
    if (!update_monit_record('monit_att_staff', $teacher1))	{
        return false;
		//	error(get_string('errorinupdateprofile','block_mou_att'), "$CFG->wwwroot/blocks/mou_att/staff/viewstaff.php?rid=$rid&sid=$sid&lid=$lid&did=$did&cat=$category&uid={$user->id}");
    }


    if ($oid != 0)	{
		$role_sotrudnik = get_record('role', 'shortname', 'sotrudnik');
		$ctx = get_context_instance($edutype->context, $oid);

   		if (!role_assign_mou($role_sotrudnik->id, $teacher1->userid, $ctx->id))	{
   			notify("SOTRUDNIK $teacher1->userid not assigned.");
		}
    }

    return true;
}


function update_appointment($usernew)
{
   global $CFG, $USER, $edutype, $oid, $rid, $uid;


    for ($num=1; $num<9; $num++)    {
        $appointmentidi = 'appointmentid' . $num;
        if (!isset($usernew->{$appointmentidi}))  continue;

        $profile = new stdClass();
        $profile->fields = array('appointmentid', 'staffid', 'stafftypeid', 'meetingid', 'appointment', 'standing_this', 'qualify', 'qualify_date', 'place_advan_train', 'date_advan_train', 'qualifynow'); // , 'stafftypeid', 'meetingid');
        $profile->type   = array('real', 'real', 'real', 'real', 'text', 'real', 'text', 'date', 'text', 'date', 'text'); // , 'stft', 'meet');
        
        foreach ($profile->fields as $j => $fld1) {
            $profile->fields[$j] = $profile->fields[$j].$num;
        }     
        unset($teacher);
         
    	$i = 0;
        $teacher = new stdClass();
    	foreach ($profile->fields as $pf)  {
    		if (isset($usernew->{$pf}))  {
    			$teacher->{$pf} = $usernew->{$pf};
    
    			switch ($profile->type[$i]) {
    				case 'text': case 'real':
    				break;
    				case 'date':
    				    if ($usernew->{$pf} != '-' && !empty($usernew->{$pf})) {
    					    $teacher->{$pf} = convert_date($usernew->{$pf}, 'ru', 'en');
    					} else {
    					    $teacher->{$pf} = '0000-00-00';
    					}
    				break;
    			}
    
    		} else {
    			switch ($profile->type[$i]) {
    				case 'text': $teacher->{$pf} = '-';
    				break;
    				case 'real': $teacher->{$pf} = 0;
    				break;
    			}
    		}
    		$i++;
    	}

        $teacher1 = new stdClass();
    	foreach ($teacher as $keyt => $atrib)	{
    		if (isset($teacher->{$keyt}) && !empty($teacher->{$keyt})) 	{
                $truekeyt1 = substr($keyt, 0, -1);		  
    			$teacher1->{$truekeyt1} = $teacher->{$keyt};
    		}
    	}
    
         // exit();
        // continue;
    
        $teacher1->id = $teacher1->appointmentid;
        //  print_r($teacher); echo '<hr>';
          // print_r($teacher1); echo '<hr>';
        
        if (!update_monit_record('monit_att_appointment', $teacher1))	{
            return false;
    		//	error(get_string('errorinupdateprofile','block_mou_att'), "$CFG->wwwroot/blocks/mou_att/staff/viewstaff.php?rid=$rid&sid=$sid&lid=$lid&did=$did&cat=$category&uid={$user->id}");
        }
       
       $fileform = 'preddoc'.$teacher1->appointmentid;
        
	   if (!empty($_FILES[$fileform]['name']))	{
       		$dir = "0/users/att/$rid/$uid/_".$teacher1->appointmentid;
       		$um = new upload_manager($fileform, true, false, 1, false, MAX_SCAN_COPY_SIZE);
       		// print_r($um);  echo '<hr>';
	        if ($um->process_file_uploads($dir))  {
		          // $newfile_name = $um->get_new_filename();
        	      print_heading(get_string('uploadedfile'), 'center', 4);
          	} else {
	          	  notify(get_string("uploaderror", "assignment")); //submitting not allowed!
       		}
	   }
        
        unset($teacher1);    
     /*   
        if ($oid != 0)	{
    	    
    	   	$role_dir = get_record('role', 'shortname', 'direktor');
    		$role_zam = get_record('role', 'shortname', 'zamdirektora');
    		
    	    $textlib = textlib_get_instance();
    	    if (isset($teacher1->is_header) &&  $teacher1->is_header == 1) {
    			$appointment_head = $textlib->strtolower($teacher->appointment_head);
    			$pos = $textlib->strpos($appointment_head, 'дир');
    			if ($pos !== false) {
    				$pos2 = $textlib->strpos($appointment_head, 'зам');
    				if ($pos2 !== false) {
    	     			if (!role_assign_mou($role_zam->id, $teacher1->userid, $ctx->id))	{
    	     				notify("not assigned ZAM DIRECTOR SCHOOL {$teacher1->userid}.");
    	     			}
    				} else {
    	     			if (!role_assign_mou($role_dir->id, $teacher1->userid, $ctx->id))	{
    	     				notify("not assigned DIRECTOR SCHOOL {$teacher1->userid}.");
    	     			}
    	     		}	
    			}	
    	   }
       } 
    */
   }       
}


function find_regform_errors(&$user, &$usernew, &$err, &$um, &$profile)
{
    global $CFG, $rid, $oid, $edutype, $uid;


    $pos = strpos($usernew->username, 'teacher');
//     print_r($usernew); echo '<hr>';
//     print_r($user); echo '<hr>';
//	if ($admin_is || $region_operator_is || $rayon_operator_is || $school_operator_is) {
        if (empty($usernew->username)) {
            $err["username"] = get_string("missingusername");

        } else if (!($pos === false))  {
	        $err["username"] = get_string('errorinusernamename', 'block_mou_att');

        }  else {
            if (empty($CFG->extendedusernamechars)) {
                $string = eregi_replace("[^(-\.[:alnum:])]", "", $usernew->username);
                if (strcmp($usernew->username, $string)) {
                    $err["username"] = get_string("alphanumerical");
                }
            }
        }

        if (strtolower($usernew->username) != strtolower($user->username))    {
        	if (record_exists_select("user", "username = '$usernew->username'")) {
	            $err["username"] = get_string("usernameexists");
	        }
	    }

        /*
        if (empty($usernew->newpassword) and empty($user->password))
            $err["newpassword"] = get_string("missingpassword");

        if (($usernew->newpassword == "admin") or ($user->password == md5("admin") and empty($usernew->newpassword)) ) {
            $err["newpassword"] = get_string("unsafepassword");
        }
        */
  //  }

    if (empty($usernew->email))
        $err["email"] = get_string("missingemail");

    if (over_bounce_threshold($user) && $user->email == $usernew->email)
        $err['email'] = get_string('toomanybounces');
  /*
    if (empty($usernew->description) and !isadmin())
        $err["description"] = get_string("missingdescription");
  */
    if (empty($usernew->city))
        $err["city"] = get_string("missingcity");

    if (empty($usernew->firstname))
        $err["firstname"] = get_string("missingfirstname");

    if (empty($usernew->lastname))
        $err["lastname"] = get_string("missinglastname");

    if (empty($usernew->country))
        $err["country"] = get_string("missingcountry");

    if (!validate_email($usernew->email)) {
        $err["email"] = get_string("invalidemail");

    } else if ($otherusers = get_records_select("user", "email = '$usernew->email'", '', 'id')) {
    	if (count($otherusers)>1)  {
            $err["email"] = get_string("emailexists");
    	} else {
    	   foreach ($otherusers as $otheruser) 	{
		        if ($otheruser->id <> $user->id) {
  			          $err["email"] = get_string("emailexists");
		        }
		   }
		}
    }

    if (empty($err["email"]) and !isadmin()) {
        if ($error = email_is_not_allowed($usernew->email)) {
            $err["email"] = $error;
        }
    }

    if (!$um->preprocess_files()) {
        $err['imagefile'] = $um->notify;
    }

    $user->email = $usernew->email;

    $teachersql = "SELECT u.id, u.username, u.firstname, u.lastname
                  FROM {$CFG->prefix}user u
	              LEFT JOIN {$CFG->prefix}monit_att_staff s ON s.userid = u.id
 	              WHERE s.{$edutype->idname}=$oid AND u.deleted = 0 AND u.confirmed = 1";
	$tezki = array();

	if ($teachers = get_records_sql($teachersql))	{
        foreach ($teachers as $teacher)  {
            if ($uid != $teacher->id)	{
	        	$tezki[] = mb_strtolower ($teacher->lastname . ' '. $teacher->firstname, 'UTF-8');
	        }
        }
	}

    $ln_fn = mb_strtolower ($usernew->lastname . ' '. $usernew->firstname . ' ' . $usernew->secondname, 'UTF-8');
    if (in_array($ln_fn, $tezki))	{
	      $err["lastname"] = $err["firstname"] = $err["secondname"] = get_string('fulltezka', 'block_mou_att');
    }
    // print_r($tezki); echo '<hr>';
    // print_r($ln_fn); echo '<hr>';

    return count($err);
}



function find_appointments_errors(&$user, &$usernew, &$err, &$um, &$profile)
{
    global $numericfield; 

	$i = 0;
	foreach ($profile->fields as $pf)  {
		if (in_array($pf, $numericfield)) {
		     if (isset($usernew->{$pf}))  { // && !empty($usernew->{$pf}))	{
		   		if ($usernew->{$pf} == '-') {
		   			$usernew->{$pf} = 0;
		   		}
		   	    if (!is_numeric($usernew->{$pf})) {
 		      		$err[$pf] = get_string('errorinputdata', 'block_mou_att');
  		     	}
   			 }
		} else {
			if (empty($usernew->{$pf}))  {
	  			$err[$pf] = get_string('missingname');
			} else {
				switch ($profile->type[$i]) {
					case 'date':
						if ($usernew->{$pf} != '-' && !is_date($usernew->{$pf})) {
			 	      		$err[$pf] = get_string('missingdate', 'block_mou_att');
			  	     	}
					break;
				}
	 		}
 		}
		$i++;
	}
}

function print_staffeditfields($user, $profile, $num=0)
{
    global $err, $numericfield;;

        // print_object($numericfield);
		$i = 0;
		foreach ($profile->fields as $pf)  {
		    $printstr = get_string($pf, 'block_mou_att');
			if (!empty($user->{$pf}))  {
			    $printval = $user->{$pf};
				switch ($profile->type[$i]) {
					case 'date':
						if ($user->{$pf} == '0000-00-00')	{
							$printval = '-';
						} else if (!is_date($user->{$pf})) {
						    $printval = convert_date($user->{$pf}, 'en', 'ru');
						} else {
							$printval = '-';							
						}
					break;
				}
			} else {
				if (in_array($pf, $numericfield)) {
					$printval = '0';
				} else {
					$printval = '-';
				}
			}

			switch ($profile->type[$i]) {
				case 'text':
				    $size = 100;
				    $maxlenth = 255;
				break;
				case 'str':
				    $size = 100;
				    $maxlenth = 100;
				break;
				case 'real':
				    $size = 6;
				    $maxlenth = 6;
				break;
				case 'date':
				    $size = 10;
				    $maxlenth = 10;
				break;
			}
            $printval = stripslashes($printval);
			$i++;
	    	// print_row($printstr . ':', $printval);
			echo "<tr><th>$printstr<font color=red>*</font>:</th>";
            if ($num > 0) {
                $pfi = $pf.$num;
            } else {
                $pfi = $pf;
            }   
			echo "<td><input type=text name=$pfi size=$size alt=\"$printstr\" maxlength=$maxlenth ";
			echo "value=\"";
			p ($printval);
			echo "\" />";
		    if (isset($err[$pfi])) formerr($err[$pfi]);
			echo '</td></tr>';
	}
}    


?>
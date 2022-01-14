<?php // $Id: viewstaff.php,v 1.3 2011/08/03 06:58:14 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');

    require_login();
    
    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);       // OU id
    $uid = required_param('uid', PARAM_INT);  // User id    
    $typeou = required_param('typeou');       // Type OU
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }
    
    $edutype = get_config_typeou($typeou);
    
	$context = get_context_instance($edutype->context, $oid);
    $view_capability = has_capability('block/mou_att2:viewattestationuser', $context);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
    $editownprofile = has_capability('block/mou_att2:editownprofile', $context);

    if (!$user = get_record_select('user', "id = $uid", 'id, username, lastname, firstname, picture, description, city, country, address, phone1, phone2, icq, email, emailstop')) {
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

	$profile = new stdClass();
	$profile->fields = array('birthday', 'graduate', 'reg_awards');
	$profile->type   = array('date', 'text', 'text');
			    
    $numericfield = array('yeargraduate', 'pedagog', 'standing', 'standing_this', 'standing', 'standing_this');
	
    $listfields = implode(',', $profile->fields); 
    $teacher = get_record_select('monit_att_staff', "userid = $uid", 'id, pswtxt, ' . $listfields);

    // $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$uid");

    $currenttab = 'profile';
    include('tabsprofile.php');

    if ($view_capability)	{

    	echo "<table width=\"80%\" align=\"center\" border=\"0\" cellspacing=\"0\" class=\"userinfobox\">";
	    echo "<tr>";
	    echo "<td width=\"100\" valign=\"top\" class=\"side\">";
    	print_user_picture($user->id, 1, $user->picture, true, false, false);
	    echo "</td><td width=\"100%\" class=\"content\">";

    	// Print the description

    	if ($user->description) {
        	echo format_text($user->description, FORMAT_MOODLE)."<hr />";
	    }

    	// Print all the little details in a list

	    echo '<table border="0" cellpadding="0" cellspacing="0" class="list">';

    	print_row(get_string('fio', 'block_monitoring').':', '<b><i>'.$fullname.'</i><b>');

        print_stafffields($teacher, $profile, $numericfield);

        $appointments = get_records_select ('monit_att_appointment', "staffid={$teacher->id}", 'id DESC', 'id, staffid, stafftypeid, meetingid, appointment, pedagog_time, standing, standing_this, qualify, qualify_date, qualifynow, place_advan_train, date_advan_train, total_mark, date_start_app, prevappointment');
        if ($appointments)  {
            // $profile->fields = array('appointment', 'pedagog_time', 'standing', 'standing_this', 'qualify', 'qualify_date', 'place_advan_train', 'date_advan_train');
            // $profile->type   = array('text', 'real', 'real', 'real', 'text', 'date', 'text', 'date');
            $profile->fields = array('appointment', 'date_start_app', 'prevappointment', 'qualify', 'qualify_date', 'place_advan_train', 'qualifynow', 'stafftypeid', 'meetingid');
            $profile->type   = array('text', 'date', 'text', 'text', 'date', 'text', 'text', 'stft', 'meet');
            foreach ($appointments as $appointment) {
           	    print_row('<hr>', '<hr>');
                print_stafffields($appointment, $profile, $numericfield);
            }
        }    
        
    	print_row('<hr>', '<hr>');

		$stradress = "";
	    if ($user->city or $user->country) {
	        $countries = get_list_of_countries();
			$stradress .= $countries["$user->country"].", $user->city";
	    }
    	if ($user->address) {
			$stradress .= ", $user->address";
	    }
        print_row(get_string("address").":", $stradress);

    	print_row("E-mail:", obfuscate_mailto($user->email, '', $user->emailstop));

	    if ($user->phone1) {
	    	$phone1 = $user->phone1;
    	} else  {
	    	$phone1 = '-';
    	}
        print_row(get_string("phone").":", $phone1);

	    if ($user->phone2) {
	    	$phone2 = $user->phone2;
    	} else  {
	    	$phone2 = '-';
    	}
        print_row(get_string('mobilephone', 'block_monitoring').":", $phone2);

    	if ($user->icq) {
	       	print_row(get_string('icqnumber').':',"<a href=\"http://web.icq.com/wwp?uin=$user->icq\">$user->icq<img src=\"http://web.icq.com/whitepages/online?icq=$user->icq&img=5\" width=\"18\" height=\"18\" border=\"0\" alt=\"\" /></a>");

	    } else {
	       	print_row(get_string('icqnumber').':',"-");
	    }

	    if ($edit_capability ) 	{
	    	print_row('<hr>', '<hr>');
	       	print_row(get_string('username').':', $user->username);
	       	print_row(get_string('startpassword', 'block_mou_att').':', $teacher->pswtxt);
	    }
    }
    echo "</table>";
    echo "</td></tr></table>";

    print_footer();


/// Functions ///////
function print_stafffields($teacher, $profile, $numericfield)
{
		$i = 0;
        // echo '<pre>'; print_r($profile); echo '</pre>';
		foreach ($profile->fields as $pf)  {
		    $printstr = get_string($pf, 'block_mou_att');
			if (!empty($teacher->{$pf}))  {
				switch ($profile->type[$i]) {
					case 'text': case 'real':
					    $printval = $teacher->{$pf};
					break;
					case 'date':
						if ($teacher->{$pf} == '0000-00-00')	{
							$printval = '-';
						} else {
					    	$printval = convert_date($teacher->{$pf}, 'en', 'ru');
					    }
                    break;     
                    case 'stft':
                        $stftid = $teacher->{$pf};
                        if ($stafftype = get_record_select('monit_att_stafftype', "id = $stftid", 'id, name'))  {
                            $printval = $stafftype->name; 
                        } else {
                            $printval = '-';
                        }    	
					break;
                    case 'meet':
                        $meetid = $teacher->{$pf};
                        if ($meetingak = get_record_select('monit_att_meeting_ak', "id = $meetid", 'id, name, date_ak'))  {
                            $tmp_date = convert_date($meetingak->date_ak, 'en', 'ru');
                            $printval = $tmp_date . ' ('. $meetingak->name . ')'; 
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
			$i++;
            if ($pf == 'appointment')   {
                $printval = '<b><i>'. $printval . '</i></b>';
            }
	    	print_row($printstr . ':', $printval);
		}
    
}

function print_row($left, $right) {
    echo "\n<tr><td nowrap=\"nowrap\" valign=\"top\" class=\"label c0\" align=\"left\">$left</td><td align=\"left\" valign=\"top\" class=\"info c1\">$right</td></tr>\n";
}


?>


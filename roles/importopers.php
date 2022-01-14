<?php // $Id: importopers.php,v 1.3 2011/09/21 07:37:50 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../monitoring/users/lib_users.php');
	require_once($CFG->dirroot.'/lib/uploadlib.php');
    require_once('../lib_att2.php');	

    $rid = optional_param('rid', 0, PARAM_INT);          // Rayon id
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
    $level = optional_param('level', 'school');          // Level: school, college, udod, dou
    $levelmonit = optional_param('tab', 'importoper');          // Level: school, college, udod, dou

	define('ROLE_OPERATOR_EMOU', 8);

    $typeou = optional_param('typeou', '-');       // Type OU    
   	$action = optional_param('action', '');       // action    

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    listbox_rayons_att("", $rid);
    
    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);

	if (!$edit_capability_region && !$edit_capability_rayon)  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$strtitle = get_string('title2','block_mou_att');
	$struser = get_string('user');
    $stroperators = get_string($level.'opers', 'block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $stroperators, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $stroperators, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); 

    include('tabsoperators.php');

    // add_to_log(SITEID, 'monit', 'school view', 'school.php?id='.SITEID, $strschool);
    $struploadusers = get_string("uploadusers", "block_monitoring"); //   . " ($level - with password)";
    print_heading_with_help($struploadusers, 'importgroup');

    
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    listbox_type_operators("importopers.php?rid=$rid&level=", $level);
    echo '</table>';

	/// If a file has been uploaded, then process it

//	if (!empty($frm) ) {
	$um = new upload_manager('userfile',false,false,null,false,0);
	$f = 0;
	if ($um->preprocess_files()) {
	    @set_time_limit(0);
	    @raise_memory_limit("192M");
	    if (function_exists('apache_child_terminate')) {
	        @apache_child_terminate();
	    }
	  
		$filename = $um->files['userfile']['tmp_name'];
        // $rayon = get_record_select('monit_rayon', "id = $rid", 'id, name');
        importopers($level, $filename);
    }

/// Print the form

    $maxuploadsize = get_max_upload_file_size();
	$strchoose = ''; // get_string("choose"). ':';
    echo '<center>';
    echo '<form method="post" enctype="multipart/form-data" action="importopers.php">'.
         $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
         '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">'.
         '<input type="hidden" name="rid" value="'.$rid.'">'.
         '<input type="hidden" name="level" value="'.$level.'">'.
         '<input type="file" name="userfile" size="50">'.
         '<br><input type="submit" value="'.$struploadusers.'">'.
         '</form>';
    echo '</center>';

    print_footer();


        
function importopers($level, $filename)
{
    global $CFG; // , $rid, $yid, $rayon;

    $csv_delimiter = ';';
    $usersnew = 0;
	$userserrors  = 0;
    $linenum = 2; // since header is line 1

    $text = file($filename);
    if($text == FALSE){
    	error(get_string('errorfile', 'block_monitoring'), "importopers.php");
    }
    $size = sizeof($text);
    
    $textlib = textlib_get_instance();
    for($i=0; $i < $size; $i++)  {
    	$text[$i] = $textlib->convert($text[$i], 'win1251');
    }
    unset ($textlib);
    
    $required = array('username' => 1,  'password' => 1, 'lastname' => 1, 'firstname' => 1, 'email' => 1,
    				  'phone1' => 1, 'city' => 1, 'description' => 1, 'idschool' => 1, 'idrayon' => 1);
    
    // --- get and check header (field names) ---
    $header = split($csv_delimiter, $text[0]);
    // check for valid field names
    foreach ($header as $i => $h) {
        $h = trim($h);
        $header[$i] = $h;
        if (!isset($required[$h])) {
            error(get_string('invalidfieldname', 'error', $h), "importopers.php");
        }
        if (isset($required[$h])) {
            $required[$h] = 0;
        }
    }
    
    echo 'login;password;lastname;firstname;email<br>';
    
    for($i=1; $i < $size; $i++)  {
        $line = split($csv_delimiter, $text[$i]);
        foreach ($line as $key => $value) {
            $record[$header[$key]] = trim($value);
        }
    
        // print_r($record);
        // add fields to object $user
        foreach ($record as $name => $value) {
            // check for required values
            if (isset($required[$name]) and !$value) {
                    error(get_string('missingfield', 'error', $name). " ".
                          get_string('erroronline', 'error', $linenum),
                          'importopers.php');
            }
            // normal entry
            else {
                $user->{$name} = addslashes($value);
            }
        }
    
    	// check existing users
         
    	if($olduser = get_record("user", "username", $user->username,  '', '', '', '', 'id, username, lastname, firstname'))		{
    	      if ($olduser->firstname == $user->firstname && $olduser->lastname == $user->lastname)	{
                   //Record not added - user is already registered
                   //In this case, output userid from previous registration
                   //This can be used to obtain a list of userids for existing users
                   notify("$olduser->id ".get_string('usernotaddedregistered', 'error', $user->username . ' '. $user->lastname. ' '.  $user->firstname));
                   $userserrors++;
                   continue;
              }
        }
    
        $j = 1;
        $userusername = $user->username;
    	while (record_exists('user', 'username', $userusername))  {
    		$userusername = $user->username . $j;
    		if ($j++ > 10) break;
    	}
        
        if ($j > 1)  {
            $user->username = $userusername; 
        }
    	
        $user->mnethostid = $CFG->mnet_localhost_id;
        $userpassword = $user->password;
        $user->password = hash_internal_user_password($userpassword);
        $user->confirmed = 1;
        $user->timemodified = time();
        $user->country = 'RU';
        $user->lang = 'ru_utf8';
        $description = get_string('operator', 'block_monitoring') . ' '.$user->description . ' ('. $user->city . ')';
        $user->description = $description;
        // echo '<hr>';
        // print_r($user);
    
        if ($user->id = insert_record("user", $user)) {
            echo "$user->username; $userpassword; $user->lastname; $user->firstname; $user->email<br>";
            $usersnew++;
        } else {
            // Record not added -- possibly some other error
            notify(get_string('usernotaddederror', 'error', $user->username));
            $userserrors++;
            continue;
        }
    
    
   		$idrayon = $user->idrayon;
	    // $user->idschool = $schooool->uniqueconstcode;
    /*
        $coursecontext = get_context_instance(CONTEXT_COURSE, 1);
        if (!user_can_assign($coursecontext, ROLE_OPERATOR_EMOU)) {
            notify("--> Can not assign role: $user->id = $user->username ($user->lastname $user->firstname)"); //TODO: localize
        }
        $ret = role_assign(ROLE_OPERATOR_EMOU, $user->id, 0, $coursecontext->id);
    */
        if (!add_operator($user->id, $level, $idrayon, $user->idschool)) {
            notify("--> Can not add school <b>operator</b>: $user->id = $user->username ($user->lastname $user->firstname)"); //TODO: localize
        }
    
        $linenum++;
        unset($user);
    }
    $strusersnew = get_string("usersnew");
    notify("$strusersnew: $usersnew", 'green', 'center');
    notify(get_string('errors', 'admin') . ": $userserrors");
    echo '<hr />';
}

?>


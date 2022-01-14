<?php  // $Id: delete.php,v 1.1 2009/11/19 11:33:42 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');    
    
    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $cid = required_param('cid', PARAM_INT);          // Critetia id
    $uid = required_param('uid', PARAM_INT);
    $yid = required_param('yid', PARAM_INT);       // Year id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = required_param('stft', PARAM_INT);  	  // Type stafftype id
    $file     = required_param('file', PARAM_FILE);
	$confirm = optional_param('confirm', 0, PARAM_INT);
    
    if ($cid == -1) {
        $redirlink0 = "registrationcard.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&uid=$uid&cid=$cid";
        $redirlink1 = $redirlink0;
        $returnurl = $redirlink0;
        $returnscript = 'registrationcard.php';  
    } else {    
        $redirlink0 =      "staffs.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft";
        $redirlink1 = "attestation.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&uid=$uid";
        $returnurl = "editcriteria.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&uid=$uid&cid=$cid";
        $returnscript = 'editcriteria.php';
    }    

    $edutype = get_config_typeou($typeou);
    if ($cid > 0) {
        $stafftype = get_record_select('monit_att_stafftype', "id = $stft", 'id, name, att_result');
    }    
    
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
    $editownprofile = has_capability('block/mou_att2:editownprofile', $context);

	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editattestationuser', $context_rayon);
    
	$context_region = get_context_instance(CONTEXT_REGION, 1);
	$edit_capability_region = has_capability('block/mou_att2:editattestationuser', $context_region);
    
    if ($cid > 0) {
        $strsql = "SELECT s.id, a.appointment
                   FROM mdl_monit_att_staff s INNER JOIN mdl_monit_att_appointment a ON s.id=a.staffid
                   WHERE s.userid=$uid and a.stafftypeid=$stft";    
        $staff = get_record_sql($strsql);
    }    
    // print_r($staff); exit();
    if (!$user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$uid")) {
        error("No such user!", $redirlink0);
    }
    $fullname = fullname($user);

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');
    if ($cid > 0) {
        $strmarkscriteria = get_string('marks__', 'block_mou_att') . ': '. $stafftype->name;
    } else {
        $strmarkscriteria = get_string('editprofileatt', 'block_mou_att');
    }    
	$stronecriteria = get_string('onecriteria', 'block_mou_att');
        
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink0, 'type' => 'misc');
    $navlinks[] = array('name' => $strmarkscriteria, 'link' => $redirlink1, 'type' => 'misc');
    $navlinks[] = array('name' => $stronecriteria , 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $stronecriteria, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    if (!$edit_capability && !($editownprofile && $uid == $USER->id))  {
		error(get_string('permission', 'block_mou_school'), $redirlink);
    }    
    
    $optionsreturn = array('rid'=>$rid, 'oid'=>$oid, 'yid'=>$yid, 'uid'=>$uid,  'cid'=>$cid, 'stft'=>$stft, 'typeou' => $typeou);

    if (!$confirm) {
        $optionsyes = $optionsreturn;
		$optionsyes['file'] = $file;
		$optionsyes['confirm']=1;
		$optionsyes['sesskey'] = sesskey();
        print_heading(get_string('delete'));
        notice_yesno(get_string('confirmdeletefile', 'assignment', $file), 'deldoc.php', $returnscript, $optionsyes, $optionsreturn, 'post', 'get');
        print_footer('none');
        die;
    }
    
    if ($cid == -1) {
        $filepath = $CFG->dataroot."/0/users/att/$rid/$uid/_$stft/$file";        
    } else {
        $filepath = $CFG->dataroot."/0/users/att/$rid/$uid/$cid/$file";
    }    
    if (file_exists($filepath)) {
        if (@unlink($filepath)) {
            redirect($returnurl, get_string('clamdeletedfile') , 0);
        }
    }

    // print delete error
    print_header(get_string('delete'));
    notify(get_string('deletefilefailed', 'assignment'));
    print_continue($returnurl);
    print_footer('none');
?>
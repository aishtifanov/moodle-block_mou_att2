<?PHP // $Id: delremark.php,v 1.2 2011/09/15 13:59:16 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');
    
    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $yid = optional_param('yid', 0, PARAM_INT);  	  //
    $uid = required_param('uid', PARAM_INT);          // User id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = required_param('stft', PARAM_INT);  	  // Type stafftype id    
 	$mid = optional_param('mid', 0, PARAM_INT);			// Remark id
	$staffid = optional_param('staffid', 0, PARAM_INT);	// School id
	$confirm = optional_param('confirm');
    
    $edutype = get_config_typeou($typeou);
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editattestationuser', $context_rayon);
    
    $permission = false;
    if ($USER->username == 'atexpert' || $edit_capability_rayon)  {
        $permission = true;
    }
    if (!$permission)   {
        error(get_string('permission', 'block_mou_school'), $redirlink);		
    }    
    
    $strtitle = get_string('title2','block_mou_att');
    $strremarks = get_string('remarks', 'block_monitoring');
   	$straddremark = get_string('delremark','block_monitoring');
    $redirlink = "remark.php?rid=$rid&oid=$oid&yid=$yid&uid=$uid&typeou=$typeou&stft=$stft"; 
    
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strremarks, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $straddremark, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $straddremark, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); 

	if (isset($confirm)) {
		// delete_records('monit_att_remark', 'id', $mid);
        set_field_select('monit_att_remark', 'deleted', 1, "id = $mid");
		//  add_to_log(1, 'school', 'Discipline deleted', 'deldiscipline.php', $USER->lastname.' '.$USER->firstname);
		redirect($redirlink, get_string('remarkdeleted','block_monitoring'));
	}

	$remark = get_record("monit_att_remark", "id", $mid);

	print_heading($straddremark .' :: ' .$remark->name);

    // $str = get_string('disciplinelow', 'block_mou_ege') . ' ' . "'$adiscipl->name'";
    $str = "'$remark->name'";

	notice_yesno(get_string('deletecheckfull', '', $str),
               "delremark.php?rid=$rid&oid=$oid&yid=$yid&uid=$uid&typeou=$typeou&stft=$stft&mid=$mid&confirm=1",
               $redirlink);

	print_footer();
?>
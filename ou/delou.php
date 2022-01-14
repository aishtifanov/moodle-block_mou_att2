<?PHP // $Id: delou.php,v 1.4 2011/08/23 10:38:36 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');

    $rid     = required_param('rid', PARAM_INT);        // Rayon id
    $oid     = required_param('oid', PARAM_INT);        // School id
    $yid 	 = required_param('yid', PARAM_INT);       		// Year id
    $typeou  = optional_param('typeou', '-');       // Type OU
	$confirm = optional_param('confirm');

    get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
    $context = get_context_instance($CONTEXT_OU, $oid);
	if (!has_capability('block/mou_att2:editattestationuser', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    $redirlink = "ou.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou";
    
	// $strclasses = get_string('classes','block_mou_ege');
	$strtitle0 = get_string('title2','block_mou_att');
	$strscript = get_string('ou','block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle0, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $strtitle, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)


	if (!$ou = get_record_select($tablename, "id = $oid", 'id, name, isclosing, dateclosing')) {
	    error(get_string('errorschool', 'block_monitoring'), $redirlink);
	}

	if (isset($confirm))  {

		if ($ou->isclosing == true)	{
			// delete_records($tablename, 'id', $oid);
			// add_to_log(1, 'dean', 'Speciality deleted', 'delspeciality.php', $USER->lastname.' '.$USER->firstname);
			redirect($redirlink, get_string('oudeleted','block_monitoring'), 3);
		} else {
			$ou->isclosing = true;
			$ou->dateclosing = time();
			if (update_record($tablename, $ou))	{
				 // add_to_log(1, 'dean', 'speciality update', "blocks/dean/speciality/speciality.php?id=$fid", $USER->lastname.' '.$USER->firstname);
				 redirect($redirlink, get_string('ouupdate','block_monitoring'));
				 
			} else {
				error(get_string('errorinupdatingschool','block_monitoring'), $redirlink);
			}
		}

	}

    $strdelschool = get_string('deletingschool','block_monitoring');
	print_heading($strdelschool .': ' .$ou->name);

    $s1 = get_string('deletecheckfull', 'block_monitoring', 'б ОУ &laquo;'. $ou->name.'&raquo;');

	notice_yesno($s1, "delou.php?rid=$rid&oid=$oid&yid=$yid&typeou=$typeou&confirm=1", $redirlink);

	print_footer();
?>
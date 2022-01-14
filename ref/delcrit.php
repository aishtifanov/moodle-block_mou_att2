<?PHP // $Id: delcrit.php,v 1.1 2011/05/25 10:17:42 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');

    $yid = optional_param('yid', 0, PARAM_INT);  	  //
	$part = required_param('part', PARAM_ALPHA);    // crit, est
    $stft = optional_param('stft', 0, PARAM_INT);  	  // Type staff id
	$id   = required_param('id', PARAM_INT);		// id criteria | estimates 
	$confirm = optional_param('confirm');

	$context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
	if (!has_capability('block/mou_att2:editmeetingak', $context_region))  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$curryear = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $curryear; 
    }

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('attcriteria','block_mou_att');
    $indexlink = "$CFG->wwwroot/blocks/mou_att2/index.php?yid=$yid";
    $redirlink = "attcriteria.php?yid=$yid&stft=$stft";
    
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => $indexlink, 'type' => 'misc');    
    switch ($part)	{
    	case 'crit':
                    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
					$table = 'monit_att_criteria';
    	break;
    	case 'est':
                    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
                    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');    
					$table = 'monit_att_estimates';
    	break;
    }

    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;");


	if (!$record = get_record($table, 'id', $id)) {
		error(get_string('errorcurriculum', 'block_mou_school', $id), $indexlink);
	}

	if (isset($confirm)) {
		$check = false;
        $msg = '';
	    switch ($part)	{
	    	case 'crit':   if (record_exists('monit_att_attestation', 'criteriaid', $id))	 {
		            		$check = true;
                            $msg .= '(есть записи в таблице АТТЕСТАЦИЯ)';
		            	   }
                           if (record_exists('monit_att_estimates', 'criteriaid', $id))	 {
		            		$check = true;
                            $msg .= '(есть записи в таблице ОЦЕНКИ)';
		            	   }
	    	break;
	    	case 'est':
	    	break;
	    }

		if (!$check)  {
			delete_records($table, 'id', $id);
			// add_to_log(1, 'school', 'Curriculum deleted', 'delcurriculum.php', $USER->lastname.' '.$USER->firstname);
		}
		else 	{
			error(get_string('errorcurriculums2','block_mou_school', $id . ' (' . $table . ')' . $msg), $redirlink);
		}
		redirect($redirlink, get_string('deletecompleted', 'block_mou_school'), 20);
	}


	print_heading($strtitle .': ' .$record->name);
	notice_yesno(get_string('deletecheckfull', '', "<b>{$record->name}</b> ..."),
               "delcrit.php?yid=$yid&part=$part&id=$id&stft=$stft&confirm=1",
               $redirlink);

	print_footer();
?>
<?php // $Id: changestatus.php,v 1.2 2014/06/16 12:00:07 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib_att2.php');    

    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $uid = required_param('uid', PARAM_INT);
    $yid = required_param('yid', PARAM_INT);       // Year id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = required_param('stft', PARAM_INT);  	  // Type stafftype id
	$confirm = optional_param('confirm', 0, PARAM_INT);
    $status = optional_param('status', 0, PARAM_INT);

    $redirlink0 = "staffs.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft";
    $redirlink = "attestation.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&uid=$uid";

    $edutype = get_config_typeou($typeou);
    $stafftype = get_record_select('monit_att_stafftype', "id = $stft", 'id, name, att_result');
    $cyid = get_last_yearid_in_criteria($stafftype->id);
    
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
    $editownprofile = has_capability('block/mou_att2:editownprofile', $context);

	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editattestationuser', $context_rayon);
    
	$context_region = get_context_instance(CONTEXT_REGION, 1);
	$edit_capability_region = has_capability('block/mou_att2:editattestationuser', $context_region);
    
    $strsql = "SELECT s.id, a.appointment
               FROM mdl_monit_att_staff s INNER JOIN mdl_monit_att_appointment a ON s.id=a.staffid
               WHERE s.userid=$uid and a.stafftypeid=$stft";    
    $staff = get_record_sql($strsql);
    // print_r($staff); exit();
    if (!$user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$uid")) {
        error("No such user!", $redirlink0);
    }
    $fullname = fullname($user);

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');
    $strmarkscriteria = get_string('marks__', 'block_mou_att') . ': '. $stafftype->name;
        
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink0, 'type' => 'misc');
    $navlinks[] = array('name' => $strmarkscriteria, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $fullname, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $fullname, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    if (!$edit_capability && !($editownprofile && $uid == $USER->id))  {
		error(get_string('permission', 'block_mou_school'), $redirlink);
    }    
    
    $rayon = get_record_select('monit_rayon', "id=$rid", 'id, name');

    $ou = get_record_select($edutype->tblname, "id=$oid", 'id, name');

	// echo $tbl_monit_att . '<hr>';
    // print_r($teacher); echo '<hr>';
	    
	if ($confirm == 1) {

    	$strsql= " SELECT id, name, num, description, is_loadfile FROM {$CFG->prefix}monit_att_criteria
    			 	WHERE stafftypeid=$stft AND yearid=$cyid
    				ORDER BY num";
    	// echo $strsql; 			
        if($criterions =  get_records_sql($strsql)) {
            // print_r ($criterions); echo '<hr>';
  	          foreach ($criterions as $criteria)  {

                    $strselect = "staffid = $staff->id AND stafftypeid = $stft AND criteriaid = $criteria->id";
				    if ($att = get_record_select('monit_att_attestation', $strselect, 'id, mark, status, namefield, fielddata' ))	 {
						// print_r($att); echo '<hr>';
						$att->status = $status;
				        if (!update_record('monit_att_attestation', $att))	{
							error(get_string('errorinupdatingstatus','block_mou_att'), $redirlink);
		 				}
  	   				}
  	          }

		      redirect($redirlink, get_string('succesupdatedata','block_monitoring'), 100);
		} else {
			notice('Критерии не найдены!');
		}
	}

    if ($status == 4)	{
		$s1 = get_string('changestatuscoordination', 'block_mou_att');
    } else  if ($status == 6)	{
    	$s1 = get_string('changestatussetok', 'block_mou_att');
    } else  if ($status == 3)	{
    	$s1 = get_string('changestatussetback', 'block_mou_att');
    }


	notice_yesno($s1, "changestatus.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&uid=$uid&status=$status&confirm=1",
					  $redirlink);

	print_footer();

?>


<?php // $Id: movestaff.php,v 1.8 2011/12/21 05:33:04 shtifanov Exp $

    require_once("../../../config.php");
    require_once("$CFG->libdir/gdlib.php");
    require_once('../../monitoring/lib.php');
	// require_once('../../mou_ege/lib_ege.php');
    require_once('../../mou_school/lib_school.php');
    require_once('../lib_att2.php');

    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = required_param('stft', PARAM_INT);  	  // Type stafftype id
    $uid  = required_param('uid', PARAM_INT);
    $rid2 = optional_param('rid2', $rid, PARAM_INT);          // Rayon id
    $oid2 = optional_param('oid2', $oid, PARAM_INT);       // School id
  //  $gid2 = optional_param('gid2', 0, PARAM_INT);          // Group id
	$action   = optional_param('action', '');

    $edutype = get_config_typeou($typeou);
    
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);

	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editattestationuser', $context_rayon);

	$context_rayon2 = get_context_instance(CONTEXT_RAYON, $rid2);
	$edit_capability_rayon2 = has_capability('block/mou_att2:editattestationuser', $context_rayon2);
    
	$context_region = get_context_instance(CONTEXT_REGION, 1);
	$edit_capability_region = has_capability('block/mou_att2:editattestationuser', $context_region);
    

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');
	$strstaffs = get_string('staffmoveou', 'block_mou_att');
    
    $redirlink = "staffs.php?rid=$rid2&yid=$yid&oid=$oid2&typeou=$typeou&stft=$stft";
    
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $strstaffs, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strstaffs, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    if (!$edit_capability  && !$edit_capability_rayon && !$edit_capability_rayon2 && !$edit_capability_region)  {
		error(get_string('permission', 'block_mou_school'), $redirlink);
    }    
    
/*    
    $strsql = "SELECT s.id, a.appointment
               FROM mdl_monit_att_staff s INNER JOIN mdl_monit_att_appointment a ON s.id=a.staffid
               WHERE s.userid=$uid and a.stafftypeid=$stft";
*/                   
    $strsql = "SELECT id, rayonid FROM mdl_monit_att_staff WHERE userid=$uid";
    $staff = get_record_sql($strsql);
    // echo '<pre>'; print_r($staff); echo '</pre><hr>'; 
    $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$uid");
   	$fullname = fullname($user);
    
    $rayon = get_record_select('monit_rayon', "id=$rid", 'id, name');

    $ou = get_record_select($edutype->tblname, "id=$oid", 'id, name');

    if ($action == 'move')	{
	
        $staff->rayonid = $rid2;
        $staff->{$edutype->idname} = $oid2;
        
        if ($edutype->idname != 'schoolid') {
            $staff->schoolid = 0;
        }
        
        // print_r($staff);
        
        if (!update_record('monit_att_staff', $staff))	{
            echo '<pre>'; print_r($staff); echo '</pre>'; 
        	error(get_string('errorinupdateprofile','block_mou_att'), $redirlink);
        }

        $edutype = get_config_typeou($typeou);
		$role_sotrudnik = get_record('role', 'shortname', 'sotrudnik');
		$ctx = get_context_instance($edutype->context, $oid2);

   		if (!role_assign_mou($role_sotrudnik->id, $uid, $ctx->id))	{
   			notify("Роль \"Сотрудника\" $fullname не назначена.");
		}
        
        $ctx = get_context_instance($edutype->context, $oid);
        role_unassign_mou($role_sotrudnik->id, $uid, $ctx->id);
            
        redirect($redirlink, get_string("changessaved"), 30);
    }



?>
<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">
<tr valign="top">
    <td align="left"><b><?php  print_string('rayon', 'block_monitoring') ?>:</b></td>
    <td align="left"> <?php p($rayon->name) ?> </td>
</tr>
<tr valign="top">
    <td align="left"><b><?php  print_string('school', 'block_monitoring') ?>:</b></td>
    <td align="left"> <?php echo $ou->name ?> </td>
</tr>
</table>
<?php


    print_heading(get_string('pupilmovein', 'block_mou_att', $fullname), "center", 3);

	$strlistrayons =  listbox_rayons_att("movestaff.php?rid=$rid&yid=$yid&oid=$oid&uid=$uid&typeou=$typeou&stft=$stft&rid2=", $rid2);
    if (!$strlistrayons)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlistrayons;
    
    if ($edutype->tblname == 'monit_school')    {
        listbox_schools("movestaff.php?rid=$rid&yid=$yid&oid=$oid&uid=$uid&typeou=$typeou&stft=$stft&rid2=$rid2&oid2=", $rid2, $oid2, $yid);
    } else { 
        if ($rid2 > 0)  {
        	if ($strlistou = listbox_ou_att("movestaff.php?rid=$rid&yid=$yid&oid=$oid&uid=$uid&typeou=$typeou&stft=$stft&rid2=$rid2&oid2=", $rid2, $typeou, $oid2, $yid))	{ 
        		echo $strlistou;
        	} else {
        		echo '</table>';
        		notice(get_string('ounotfound', 'block_mou_att'), $redirlink);
        	}
        }    
    }    	
	echo '</table>';
    
    if ($rid2 >0 && $oid2 > 0)  {
    	$options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'uid' => $uid, 'typeou' => $typeou, 'stft' => $stft,
    					 'rid2' => $rid2, 'oid2' => $oid2, 'action' => 'move');
    	echo '<table align="center" border=0><tr><td>';
        print_single_button("movestaff.php", $options, get_string('makepupilmovein', 'block_mou_school'));
    	echo '</td></tr></table>';
    }    

    print_footer();
?>


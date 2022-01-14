<?php // $Id: newsovmest.php,v 1.2 2011/08/29 12:07:56 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_school/lib_school.php');
	require_once('../lib_att2.php');

    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = required_param('stft', PARAM_INT);  	  // Type stafftype id    
    $newuser = optional_param('newuser', false);  // Add new user
    $rid2 = optional_param('rid2', $rid, PARAM_INT);          // Rayon id
    $oid2 = optional_param('oid2', $oid, PARAM_INT);       // School id
    $uid  = optional_param('uid', 0, PARAM_INT);
       
	$action   = optional_param('action', '');    

    $edutype = get_config_typeou($typeou);
    
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('sovmeststaff','block_mou_att');
   	$strstaffs = get_string('addsovmest', 'block_mou_att');

    $redirlink = "sovmeststaff.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid&stft=$stft";
    
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $strstaffs, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strstaffs, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    if (!$edit_capability)  {
		error(get_string('permission', 'block_mou_school'), $redirlink);
    }

       
    $rayon = get_record_select('monit_rayon', "id=$rid", 'id, name');

    $ou = get_record_select($edutype->tblname, "id=$oid", 'id, name');

    if ($action == 'move')	{
	
        if ($staff = get_record_sql ("SELECT id as staffid FROM mdl_monit_att_staff WHERE userid=$uid"))    {
            $staff->{$edutype->idname} = $oid;
            $staff->edutypeid = $edutype->id;
            
            // echo '<pre>'; print_r($staff); echo '</pre>';
          
            if (record_exists_mou('monit_att_staff', 'id', $staff->staffid, $edutype->idname, $oid)) {
                notify('Учитель уже является штатным сотрудником школы.');
            }  else  {       
                // print_r($staff);
                if (!record_exists_mou('monit_att_staffshared', 'staffid', $staff->staffid, $edutype->idname, $oid)) {  
                    if (insert_record('monit_att_staffshared', $staff))	{
                    	    if ($oid != 0)	{
		                        $role_sotrudnik = get_record('role', 'shortname', 'sotrudnik');
		                        $ctx = get_context_instance($edutype->context, $oid);

                                if (!role_assign_mou($role_sotrudnik->id, $uid, $ctx->id))	{
                                    notify("SOTRUDNIK $uid not assigned.");
                                }
                            }

                    } else {
                        error(get_string('errorinupdateprofile','block_mou_att'), $redirlink);                        
                    }
                }  else {
                    notify('Учитель уже есть в списках совместителей.');
                }
            }      
            redirect($redirlink, get_string("changessaved"), 0);
        }    
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


    print_heading(get_string('addsovmest', 'block_mou_att'), "center", 3);

	$strlistrayons =  listbox_rayons_att("newsovmest.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&rid2=", $rid2);
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlistrayons;
    
    /*
	if ($strlistou = listbox_ou_att("newsovmest.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&rid2=$rid2&oid2=", $rid2, $typeou, $oid2, $yid))	{ 
		echo $strlistou;
	} else {
		echo '</table>';
		notice(get_string('ounotfound', 'block_mou_att'), $redirlink);
	}
    */
    listbox_schools("newsovmest.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&rid2=$rid2&oid2=", $rid2, $oid2, $yid);	
    listbox_staff("newsovmest.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou&stft=$stft&rid2=$rid2&oid2=$oid2&uid=", $rid2, $oid2, $uid, $yid);
	echo '</table>';
    
    if ($rid2 >0 && $oid2 > 0)  {
    	$options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'uid' => $uid, 'typeou' => $typeou, 'stft' => $stft,
    					 'rid2' => $rid2, 'oid2' => $oid2, 'action' => 'move');
    	echo '<table align="center" border=0><tr><td>';
        print_single_button("newsovmest.php", $options, get_string('addsovmest', 'block_mou_att'));
    	echo '</td></tr></table>';
    }    

    print_footer();
     
?>
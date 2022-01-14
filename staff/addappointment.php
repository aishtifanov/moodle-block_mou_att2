<?php // $Id: add_staff.php,v 1.3 2010/03/17 12:14:04 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
	require_once('../lib_att2.php');

    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = optional_param('stft', 0, PARAM_INT);  	  // Type stafftype id    
    $uid = optional_param('uid', 0, PARAM_INT);

    $edutype = get_config_typeou($typeou);
    
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
    if ($uid > 0)   {
        $teacher = get_record_select('monit_att_staff', "userid = $uid", 'id, userid');
        if (!$user = get_record_sql("SELECT id, lastname, firstname, deleted FROM {$CFG->prefix}user WHERE id=$uid")) {
            error("No such user!", $redirlink);
        }
        $fullname = fullname($user);
    } else {
        $fullname = '-';
    }    

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');
    $strtitle2 =  get_string('teacherdolg','block_mou_att');
    $strtitle3 =  get_string('adddolgnost','block_mou_att');

    $redirlink = "staffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid&stft=$stft";
    
    $redirlink2 = "registrationcard.php?rid=$rid&oid=$oid&yid=$yid&uid=$uid&typeou=$typeou";
    
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $fullname, 'link' => $redirlink2, 'type' => 'misc');
    $navlinks[] = array('name' => $strtitle3, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle2, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    if (!$edit_capability)  {
		error(get_string('permission', 'block_mou_school'), $redirlink);
    }    

	if ($frm = data_submitted())   {
        $staff = get_record_select('monit_att_staff', "userid = $uid", 'id, userid');
		// print_r($staff); echo '<hr>';		
		// print_r($frm);
		if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
			foreach ($frm->addselect as $add_dolgnost) {
    		  $rec->staffid = $staff->id;
              $rec->stafftypeid = $add_dolgnost;
              if (!insert_record('monit_att_appointment', $rec))	{
                    error(get_string('errorinaddingteacher','block_mou_att'), $redirlink);
              }  
              // redirect("$CFG->wwwroot/blocks/mou_att/staff/add_staff.php?rid=$rid&lid=$lid&uid=$uid");
            }
		} else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {
			foreach ($frm->removeselect as $remove_dolgnost) {
			     // delete_records('monit_att_appointment',  'staffid', $staff->id, 'stafftypeid', $remove_dolgnost); 
                 delete_records('monit_att_appointment',  'id', $remove_dolgnost);
			}
		} 
	}

	$ou = get_record_select($edutype->tblname, "id = $oid", 'id, name');
	print_heading($edutype->strtitle.': '.$ou->name, "center", 3);
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    listbox_staff("addappointment.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid&stft=$stft&uid=", $rid, $oid, $uid, $yid);
	echo '</table>';


    $sesskey = !empty($USER->id) ? $USER->sesskey : '';	
   
    if ($rid != 0 && $oid != 0  && $uid !=0)  {

        $strsql = "SELECT b.id, b.name FROM {$CFG->prefix}monit_att_stst a INNER JOIN {$CFG->prefix}monit_att_stafftype b ON a.stafftypeid=b.id
                   WHERE edutypeid=$edutype->id";
        $alldolg = array();
        if ($arr_stfts =  get_records_sql($strsql))	{
          		foreach ($arr_stfts as $astf) 	{
        			$alldolg[$astf->id] = $astf->name;
        		}
        }

        if ($staff = get_record_select('monit_att_staff', "userid = $uid", 'id, userid'))   { 
            $stafappointment = array();       
            $stftids = array();
            if ($appointments = get_records_select('monit_att_appointment', "staffid = $staff->id", '', 'id, stafftypeid, appointment'))    {
               foreach ($appointments as $appointment)    {
                   $stafappointment[$appointment->id] = $alldolg[$appointment->stafftypeid] . ' ('. $appointment->appointment . ')';
                   $stftids[] = $appointment->stafftypeid;  
               }       
            }
        }       


        

?>

<form name="formpoint" id="formpoint" method="post" action="addappointment.php">
<input type="hidden" name="rid" value="<?php echo $rid ?>" />
<input type="hidden" name="oid" value="<?php echo $oid ?>" />
<input type="hidden" name="yid" value="<?php echo $yid ?>" />
<input type="hidden" name="uid" value="<?php echo $uid ?>" />
<input type="hidden" name="stft" value="<?php echo $stft ?>" />
<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
<input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
<table align="center" border="0" cellpadding="5" cellspacing="0">
    <tr>
      <td valign="top"> <?php echo $strtitle2 ?>  
	  </td>
      <td></td>
      <td valign="top"> <?php echo get_string('alldolg', 'block_mou_att');?> </td>
    </tr>
    <tr>
      <td valign="top">
          <select name="removeselect[]" size="20" id="removeselect"  multiple
                  onFocus="document.formpoint.add.disabled=true;
                           document.formpoint.remove.disabled=false;
                           document.formpoint.addselect.selectedIndex=-1;" />
          <?php

          	if ($staff)  {
              foreach ($stafappointment as $key => $a) {
                 //	print_r($key);
              		echo "<option value=\"$key\">" . $a . "</option>\n";
              }      
			}
          ?>
          </select></td>
      <td valign="top">
        <br />
        <input name="add" type="submit" id="add" value="&larr;" />
        <br />
        <input name="remove" type="submit" id="remove" value="&rarr;" />
        <br />
      </td>
      <td valign="top">
          <select name="addselect[]" size="20" id="addselect"  multiple
                  onFocus="document.formpoint.add.disabled=false;
                           document.formpoint.remove.disabled=true;
                           document.formpoint.removeselect.selectedIndex=-1;">
          <?php          
          foreach ($alldolg as $key => $a) {
          //	print_r($key);
          	if (!in_array($key, $stftids))	{
          		echo "<option value=\"$key\">" . $a . "</option>\n";
          		}
           }
          ?>
         </select>
       </td>
    </tr>
  </table>
</form>

<?php

    print_footer();

}


?>
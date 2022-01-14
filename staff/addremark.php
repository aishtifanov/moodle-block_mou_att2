<?PHP // $Id: addremark.php,v 1.4 2012/04/05 11:08:39 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update, answer
    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $yid = optional_param('yid', 0, PARAM_INT);  	  //
    $uid = required_param('uid', PARAM_INT);          // User id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = required_param('stft', PARAM_INT);  	  // Type stafftype id    
 	$mid = optional_param('mid', 0, PARAM_INT);			// Remark id
	$staffid = optional_param('staffid', 0, PARAM_INT);	// School id
    $modeanswer = false;
    $time = time();

    $redirlink = "remark.php?rid=$rid&oid=$oid&yid=$yid&uid=$uid&typeou=$typeou&stft=$stft";
    	
    $edutype = get_config_typeou($typeou);
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
    $editownprofile = has_capability('block/mou_att2:editownprofile', $context);
    
	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editattestationuser', $context_rayon);

    $permission = false;
    if ($USER->username == 'atexpert' || $edit_capability || $edit_capability_rayon || ($editownprofile && $uid == $USER->id))  {
        $permission = true;
    }
    if (!$permission)   {
        error(get_string('permission', 'block_mou_school'), $redirlink);		
    }    


    $strremarks = get_string('remarks', 'block_monitoring');
    if ($mode === "new" || $mode === "add" ) {
    	$straddremark = get_string('addremark','block_monitoring');
    } else {
    	$straddremark = get_string('updateremark','block_monitoring');
    }
    
    $strtitle = get_string('title2','block_mou_att');
   
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strremarks, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $straddremark, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $straddremark, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); 

	$rec->staffid = $staffid;
	$rec->name = '';
    $rec->stafftypeid = $stft;
    $rec->timemodified = $time;
    $rec->usermodified = $USER->id;
    $rec->deleted = 0;
    
    $sql = "SELECT max(yearid) as maxyid FROM mou.mdl_monit_att_staff s
            inner join mdl_monit_att_appointment aa on s.id=aa.staffid
            inner join mdl_monit_att_meeting_ak ak on ak.id=aa.meetingid
            where s.userid=$uid";
    if (!$rec->yearid = get_field_sql($sql))    {
        $rec->yearid = get_current_edu_year_id();
    }        
    
    if ($mode === 'answer')  {
        $mode = 'edit';
        $modeanswer = true;
        $readonly = 'readonly';
    } else {
        $readonly = '';
    }
	
    if ($USER->username == 'atexpert' || $edit_capability_rayon)    {
        $readonly = '';
    }
         
	if ($mode === 'add')  {
		$rec->name = required_param('name');
		if (find_form_disc_errors($rec, $err) == 0) {
			// $rec->timemodified = time();
			if ($mid = insert_record('monit_att_remark', $rec))		{
				 // add_to_log(1, 'school', 'one discipline added', "blocks/school/curriculum/addiscipline.php?mode=2&fid=$fid&sid=$sid&cid=$cid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('remarkadded','block_monitoring'), $redirlink);
			} else
				error(get_string('errorinaddingremark','block_monitoring'), $redirlink);
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($mid > 0) 	{
			$remark = get_record('monit_att_remark', 'id', $mid);
			$rec->id = $remark->id;
			$rec->name = $remark->name;
            $rec->answer = $remark->answer;
            $rec->stafftypeid = $remark->stafftypeid;
		}
	}
	else if ($mode === 'update')	{
		$rec->id = required_param('mid', PARAM_INT);
        $rec->stafftypeid = required_param('stft', PARAM_INT);
		$rec->name = required_param('name');
        $rec->answer = optional_param('answer', '');


		if (find_form_disc_errors($rec, $err) == 0) {
			if (update_record('monit_att_remark', $rec))	{
				 // add_to_log(1, 'school', 'discipline update', "blocks/school/curriculum/addiscipline.php?mode=2&fid=$fid&sid=$sid&cid=$cid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('remarkupdate','block_monitoring'), $redirlink);
			} else  {
				error(get_string('errorinupdatingremark','block_monitoring'), $redirlink);
			}
		}
	}


	print_heading($straddremark, "center", 3);

    print_simple_box_start("center");

	if ($mode === 'new') $newmode='add';
	else 				 $newmode='update';

?>

<form name="addform" method="post" action="addremark.php">
<center>
<table cellpadding="5">
<tr valign="top">
    <td align="right"><b><?php  print_string('remark', 'block_monitoring') ?>:</b></td>
    <td align="left">
        <textarea <?php echo $readonly ?>  id="edit-1" name="name" rows="15" cols="75"> <?php p($rec->name) ?> </textarea> 
		<?php if (isset($err["name"])) formerr($err["name"]); ?>
    </td>
</tr>
<?php  if ($modeanswer)  {  ?>
<tr valign="top">
    <td align="right"><b><?php  print_string('remarkanswer', 'block_mou_att') ?>:</b></td>
    <td align="left">
        <textarea id="edit-2" name="answer" rows="22" cols="75"> <?php p($rec->answer) ?> </textarea> 
    </td>
</tr>
<?php }  ?>
</table>

   <div align="center">
     <input type="hidden" name="mode" value="<?php echo $newmode ?>">
     <input type="hidden" name="rid" value="<?php echo $rid ?>">
     <input type="hidden" name="oid" value="<?php echo $oid ?>">
     <input type="hidden" name="yid" value="<?php echo $yid ?>">
     <input type="hidden" name="mid" value="<?php echo $mid ?>">
     <input type="hidden" name="uid" value="<?php echo $uid ?>">     
     <input type="hidden" name="staffid" value="<?php echo $staffid ?>">
     <input type="hidden" name="stft" value="<?php echo $stft ?>">     
     <input type="hidden" name="typeou" value="<?php echo $typeou ?>">
 	 <input type="submit" name="adddisc" value="<?php print_string('savechanges')?>">
  </div>

 </center>
</form>


<?php
    print_simple_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_disc_errors(&$rec, &$err, $mode='add') {

    if (empty($rec->name)) {
            $err["name"] = get_string("missingname");
	}

    return count($err);
}

?>
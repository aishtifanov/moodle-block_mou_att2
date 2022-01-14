<?PHP // $Id: editmeeting.php,v 1.3 2009/02/25 08:54:46 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
	$mid = optional_param('mid', 0, PARAM_INT);
    $levelmonit  = required_param('level', PARAM_INT);

	$yid = get_current_edu_year_id();
    $maxyid = get_field_sql("SELECT max(id) as maxid FROM mdl_monit_years");
    $nextyid = $maxyid + 1;

    $context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    $edit_capability = has_capability('block/mou_att2:editmeetingak', $context_region); 
	if (!$edit_capability )  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    if ($mode === "new" || $mode === "add" ) {
    	$straddmet = get_string('addmeeting', 'block_mou_att');
    } else  {
    	$straddmet = get_string('updatemeeting','block_mou_att');
    }

	$strtitle = get_string('title2', 'block_mou_att');
	$strgak  = get_string('meetinggak', 'block_mou_att');
    
    $redirlink = 'meeting.php?level=0';

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strgak , 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $straddmet , 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $straddmet, $SITE->fullname, $navigation, "", "", true, "&nbsp;");


	$rec->name = get_string('meetinggak', 'block_mou_att');;
	$rec->date_ak = date('d.m.Y');
	$rec->amount = 0;

	if ($mode === 'add')  {
		$rec->name = required_param('name');
		$rec->date_ak = required_param('date_ak');

		if (find_form_meeting_errors($rec, $err) == 0) 	{
	        $rec->date_ak = convert_date($rec->date_ak, 'ru', 'en');
            
            $sql = "SELECT id FROM mdl_monit_years where datestart<='{$rec->date_ak}' and dateend>='{$rec->date_ak}'";
            // print $sql . '<br>';
            if ($yid = get_field_sql($sql)) {
                // print $yid;
                $rec->yearid = $yid;
            } else {
                $rec->yearid = $nextyid;
            }
	        $rec->level_ak = $levelmonit;
            // print_object($rec);
			if (insert_record('monit_att_meeting_ak', $rec))	{
				 // add_to_log(1, 'dean', 'one faculty added', 'blocks/dean/faculty/faculty.php', $USER->lastname.' '.$USER->firstname);
				 notice(get_string('meetingadded','block_mou_att'), $redirlink);
			} else
				error(get_string('errorinaddingmeeting','block_mou_att'), $redirlink);
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($mid > 0) 	{
		    if (!$meeting = get_record('monit_att_meeting_ak', 'id', $mid))	{
		    	error ('Meeting not found!');
		    }
			$rec->id = $meeting->id;
			$rec->name = $meeting->name;
	        $rec->date_ak = convert_date($meeting->date_ak, 'en', 'ru');
            $sql = "SELECT id FROM mdl_monit_years where datestart<='{$rec->date_ak}' and dateend>='{$rec->date_ak}'";
            if ($yid = get_field_sql($sql)) {
                $rec->yearid = $yid;
            } else {
                $rec->yearid = $nextyid;
            }

	        $rec->level_ak = $levelmonit;
		}
	}
	else if ($mode === 'update')	{
		if ($mid > 0) 	{
		    if (!$meeting = get_record('monit_att_meeting_ak', 'id', $mid))	{
		    	error ('Meeting not found!');
		    }
		}
		$rec->id = $meeting->id;
		$rec->name = required_param('name');
		$rec->date_ak = required_param('date_ak');

		if (find_form_meeting_errors($rec, $err, $mode) == 0) 	{
	        $rec->date_ak = convert_date($rec->date_ak, 'ru', 'en');

            $sql = "SELECT id FROM mdl_monit_years where datestart<='{$rec->date_ak}' and dateend>='{$rec->date_ak}'";
            if ($yid = get_field_sql($sql)) {
                $rec->yearid = $yid;
            } else {
                $rec->yearid = $nextyid;
            }

	        $rec->level_ak = $levelmonit;
			if (update_record('monit_att_meeting_ak', $rec))	{
				 // add_to_log(1, 'dean', 'faculty update', 'blocks/dean/faculty/faculty.php', $USER->lastname.' '.$USER->firstname);
				 notice(get_string('meetingupdate','block_mou_att'), $redirlink);
			} else {
				error(get_string('errorinmeetingfaculty','block_mou_att'), $redirlink);
			}
		}
	}

	print_heading($straddmet);

    print_simple_box_start("center");

?>

<form name="addform" method="post" action="<?php if ($mode === 'new') echo "editmeeting.php?mode=add&level=$levelmonit";
												 else echo "editmeeting.php?mode=update&mid=$mid&level=$levelmonit"; ?>">
<center>
<table cellpadding="5">
<tr valign="top">
    <td align="right"><b><?php  print_string("name") ?>:</b></td>
    <td align="left">
		<input type="text" id="name" name="name" size="70" value="<?php p($rec->name) ?>" />
		<?php if (isset($err["name"])) formerr($err["name"]); ?>
    </td>
</tr>
<tr valign="top">
    <td align="right"><b><?php  print_string("datemeeting","block_mou_att") ?>:</b></td>
    <td align="left">
		<input type="text" id="date_ak" name="date_ak" size="10" value="<?php
		if (isset($rec->date_ak))	{
			  echo $rec->date_ak;
		}
		?>" />
		<?php if (isset($err["date_ak"])) formerr($err["date_ak"]); ?>
    </td>
</tr>
</table>
   <div align="center">
  <input type="hidden" name="mid" value="<?php echo $mid ?>" />
  <input type="submit" name="addmeeting" value="<?php print_string('savechanges')?>">
  </div>
 </center>
</form>


<?php
    print_simple_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_meeting_errors(&$rec, &$err)
{
        if (empty($rec->name))	{
		    $err["name"] = get_string("missingname");
		}
        if (empty($rec->date_ak))	{
			$err["date_ak"] = get_string("missingname");
		}

		if (!is_date($rec->date_ak)) {
      		$err["date_ak"] = get_string('missingdate', 'block_mou_att');
     	}

    return count($err);
}

?>
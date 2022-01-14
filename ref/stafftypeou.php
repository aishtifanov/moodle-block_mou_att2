<?PHP // $Id: stafftypeou.php,v 1.1.1.1 2011/03/25 07:53:50 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');

    $typeou = optional_param('typeou', '-');       // Type OU
    
	$context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
	if (!has_capability('block/mou_att2:editmeetingak', $context_region))  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('stafftypeou','block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

	$currenttab = 'stafftypeou';
    include('tabs.php');

	// Processing submitted data
	if ($frm = data_submitted())   {
	    $edutype = get_record_select ('monit_school_type', "cod = $typeou", 'id');
		if (!empty($frm->add) and !empty($frm->addselect) and confirm_sesskey()) {
			foreach ($frm->addselect as $addpupil) { // id, edutypeid, stafftypeid
			    if (record_exists('monit_att_stst', 'edutypeid', $edutype->id, 'stafftypeid', $addpupil))	{
                      notify('Error in adding stst!');
			    } else {
					$rec->edutypeid 	= $edutype->id;
			        $rec->stafftypeid 	= $addpupil;
			    	if (!insert_record('monit_att_stst', $rec)){
			    		error('Error in adding stst!');
			    	}
			    }
              //redirect("$CFG->wwwroot/blocks/mou_school/curriculum/editteachdiscip.php?mode=2&sid=$sid&did=$did&rid=$rid");
            }
		} else if (!empty($frm->remove) and !empty($frm->removeselect) and confirm_sesskey()) {
			foreach ($frm->removeselect as $removepupil) {
				delete_records('monit_att_stst', 'edutypeid', $edutype->id, 'stafftypeid', $removepupil);
				// add_to_log(1, 'dean', 'curator deleted', '/blocks/dean/gruppa/curatorsgroups.php', $USER->lastname.' '.$USER->firstname);
			}
		} 
	}

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    $strlisttypeou =  listbox_typeou_att("stafftypeou.php?typeou=", $rid, $typeou);
  	echo $strlisttypeou. '</table>';
  	
	if ($typeou != '-')	{  
	   $edutype = get_record_select ('monit_school_type', "cod = $typeou", 'id');
       
       $strsql = "SELECT a.id, a.name FROM {$CFG->prefix}monit_att_stafftype a
                  INNER JOIN {$CFG->prefix}monit_att_stst b ON b.stafftypeid = a.id
                  WHERE b.edutypeid = $edutype->id 
                  ORDER BY a.name";
	   $dstudents = get_records_sql($strsql);

  	    $strsql = "SELECT id, name FROM {$CFG->prefix}monit_att_stafftype ORDER BY id";
	    $cstudents = get_records_sql($strsql);

 	    $idsstudents  = array();
	    $dstudentmenu = array();
	 	if ($dstudents)	{
	 		foreach ($dstudents as $dstud)	{
	 			$dstudentmenu[$dstud->id] = $dstud->name;
	 			$idsstudents[] = $dstud->id;
	 		}
	 	}

		$schoolmenu = array();
	    if ($cstudents)	{
	  		foreach ($cstudents as $cstud) {
	  			$schoolmenu[$cstud->id] = $cstud->name;
			}
		}
	    print_simple_box_start("center", '70%');
	    // print_heading($strtitle, "center", 3);
	    $sesskey = !empty($USER->id) ? $USER->sesskey : '';
        echo '<form name="formpoint" id="formpoint" method="post" action="stafftypeou.php">';
        echo '<table align="center" border="0" cellpadding="5" cellspacing="0"><tr> <td valign="top">'; 
        echo get_string('oustafftype', 'block_mou_att');
        echo '</td><td></td><td valign="top">'; 
        echo get_string('allstafftype', 'block_mou_att');
        ?>
         </td>
    </tr>
    <tr>
      <td valign="top">
          <select name="removeselect[]" size="20" id="removeselect"  multiple
                  onFocus="document.formpoint.add.disabled=true;
                           document.formpoint.remove.disabled=false;
                           document.formpoint.addselect.selectedIndex=-1;" />
          <?php
          if (!empty($dstudentmenu))	{
              foreach ($dstudentmenu as $key => $pm) {
                  echo "<option value=\"$key\">" . $pm . "</option>\n";
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
          if (!empty($schoolmenu))	{
              foreach ($schoolmenu as $key => $sm) {
              	if (!in_array($key, $idsstudents))	{
                  echo "<option value=\"$key\">" . $sm . "</option>\n";
                }
              }
          }
          ?>
         </select>
       </td>
    </tr>
  </table>
  <input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
  <input type="hidden" name="sesskey" value="<?php echo $sesskey ?>" />
</form>

<?php
   print_simple_box_end();
   }

   print_footer();

?>
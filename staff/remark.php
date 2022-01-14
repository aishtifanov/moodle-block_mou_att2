<?php // $Id: remark.php,v 1.8 2014/06/16 12:00:07 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/filelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');

   	$action2 = optional_param('action2', '');       // action
	$mid = optional_param('mid', '0', PARAM_INT);       // Remark id   	

    $currenttab = 'remark';
    include('tabsatt.php');   
    
    if ($yid == 6) $yid--; 

    if ($action2 == 'ok')	{
       	  set_field('monit_att_remark', 'status', 1, 'id', $mid, 'staffid', $staff->id);
    } else if ($action2 == 'break')	{
       	  set_field('monit_att_remark', 'status', 0, 'id', $mid, 'staffid', $staff->id);
    }

    // notify (get_string ('vstadii', 'block_mou_att'));    exit();
    
	$context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
	$edit_capability_region = has_capability('block/mou_att2:editattestationuser', $context_region);
    
    $table = table_remark($staff);
    print_color_table($table);

	if  ($edit_capability_region || $edit_capability_rayon || $USER->username == 'atexpert') {
	    $options = array('rid' => $rid, 'oid' => $oid, 'yid' => $yid, 'mode' => 'new', 'uid' => $uid,
				          'stft' => $stft, 'typeou' => $typeou, 'staffid' => $staff->id);
		echo '<table align="center" border=0><tr><td>';
	    print_single_button("addremark.php", $options, get_string('addremark','block_monitoring'));
		echo '</td></tr></table>';
	} 



    print_footer();    
    
    

function table_remark($staff)
{
    global $USER, $CFG, $edit_capability_region, $rid, $oid, $yid, $uid, $typeou, $stft;

    $table = new stdClass();
    $table->head  = array (get_string('status', 'block_monitoring'),
    				        get_string('studyyear', 'block_mou_school'),
    						get_string('remarks', 'block_monitoring'),
                            get_string('remarkanswer', 'block_mou_att'),
	    					get_string('action', 'block_monitoring'));
    $table->align = array ('center', 'center', 'left', 'left', 'center');
    $table->class = 'moutable';
   	$table->width = '90%';
    $table->size = array ('10%', '10%', '40%', '30%', '10%');

    $staffid = $staff->id;
    
    $years = get_records_select_menu('monit_years', "", 'id', 'id, name');
    
    if($remarks =  get_records_select('monit_att_remark', "staffid = $staffid AND (stafftypeid = $stft or stafftypeid = 0) AND deleted=0", 'id', 'id, yearid, staffid, status, name, answer')) {

          foreach ($remarks as $remark) {
          	    $mid = $remark->id;

                $urlparam = "rid=$rid&oid=$oid&yid=$yid&uid=$uid&typeou=$typeou&stft=$stft&mid=$mid&staffid=$staffid";

				if ($edit_capability_region || $USER->username == 'atexpert') 	{

                    
					$title = get_string('editremark','block_monitoring');
					$strlinkupdate = "<a title=\"$title\" href=\"addremark.php?mode=edit&$urlparam\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

					$title = get_string('setokremark', 'block_monitoring');
					$strlinkupdate .= "<a title=\"$title\" href=\"remark.php?action2=ok&$urlparam\">";
					$strlinkupdate .=  "<img src=\"{$CFG->pixpath}/i/tick_green_big.gif\" alt=\"$title\" /></a>&nbsp;";

					$title = get_string('breakremark','block_monitoring');
					$strlinkupdate .= "<a title=\"$title\" href=\"remark.php?action2=break&$urlparam\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/minus.gif\" alt=\"$title\" /></a>&nbsp;";

					$title = get_string('deleteremark','block_monitoring');
				    $strlinkupdate .= "<a title=\"$title\" href=\"delremark.php?$urlparam\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

				} else {
					$title = get_string('addremarkanswer','block_mou_att');
					$strlinkupdate = "<a title=\"$title\" href=\"addremark.php?mode=answer&$urlparam\">";
					$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
    			}

				$strformrkpu_status = get_string('accrstatus'.$remark->status, "block_monitoring");
				$strcolor = get_string('accrstatus'.$remark->status.'color',"block_monitoring");
                
                if (isset($years[$remark->yearid])) {
                    $stryear = $years[$remark->yearid];
                } else {
                    $stryear = 'не определен';
                }    

       			$table->data[] = array ($strformrkpu_status, $stryear, $remark->name, $remark->answer, $strlinkupdate);
      			$table->bgcolor[] = array ($strcolor);
          }
    }
    return $table;
}    
   
?>

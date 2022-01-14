<?php // $Id: rangemarks.php,v 1.2 2011/09/07 14:15:53 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');
    require_once('../../mou_accredit/lib_accredit.php');
    
    $yid = optional_param('yid', 0, PARAM_INT);  	  //
    $stft = optional_param('stft', 0, PARAM_INT);  	  // Type staff id
    
	$curryear = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $curryear; 
    }
    
	$context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    $edit_capability = has_capability('block/mou_att2:editmeetingak', $context_region); 
    $view_capability = has_capability('block/mou_att2:viewmeetingak', $context_region);
	if (!$view_capability)  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('attcriteria','block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;");

	$currenttab = 'rangemarks';
    include('tabs.php');

	if ($rec = data_submitted())  {
	   set_field('monit_att_stafftype', "att_result", $rec->att_result, 'id', $stft); 
       notify('Диапазоны баллов квалификационных категорий сохранены.', 'green');
       $stft++;
	}   
	   
  	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_stafftype("rangemarks.php?stft=", $stft);
    echo '</table>'; 
	// echo $typeou;

    if ($stft != 0) {
        print_simple_box_start('center', '80%', 'white');
        $rec = get_record_select('monit_att_stafftype', "id = $stft");
 
?>
	<form name="addform" method="post" action="rangemarks.php">
	<center>
	<table cellpadding="5">
	<tr valign="top">
	    <td align="right"><b><?php  print_string("rangemarks", 'block_mou_att') ?>:</b></td>
	    <td align="left">
            <?php print_textarea(true, 25, 80, 0, 0, "att_result", $rec->att_result); ?>
	    </td>
	</tr>
    </table>
    </center>
<?php

	echo  '<input type="hidden" name="stft" value="' . $stft  . '">';
	echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
    
	echo  '<div align="center">';
	echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"></div>';
	echo  '</form><p>&nbsp;</p>';

        print_simple_box_end();
         
    }

    print_footer();
    


?>
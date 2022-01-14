<?php // $Id: editrefcrit.php,v 1.5 2012/08/20 05:35:13 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');
    require_once('../../mou_accredit/lib_accredit.php');
    
    $yid = optional_param('yid', 0, PARAM_INT);  	  //
    $stft = optional_param('stft', 0, PARAM_INT);  	  // Type staff id
    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
    $cid  = optional_param('cid',  0, PARAM_INT);	    // Criteria id
    
	$curryear = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $curryear; 
    }
    
	$context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
	if (!has_capability('block/mou_att2:editmeetingak', $context_region))  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('attcriteria','block_mou_att');
    $redirlink = "attcriteria.php?yid=$yid&stft=$stft";
    
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');    
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;");

    if ($nameedyear = get_field_sql("SELECT name FROM mdl_monit_years where id=$yid"))    {
        print_heading('Критерий на учебный год: ' . $nameedyear, 'center', 3); 
    } else {
        print_heading('Критерий на новый учебный год', 'center', 3);
    }
    
    $strsql = "SELECT id, name  FROM {$CFG->prefix}monit_att_stafftype WHERE id=$stft ORDER BY id";
    if ($stafftype =  get_record_sql($strsql))	{
        $strhead = get_string('staffapointment', 'block_mou_att').': ';
        $strhead .= $stafftype->name;
        print_heading($strhead, 'center', 3);
    }     
    
        
	if ($mode != 'new' && $recs = data_submitted())  {
		// print_r($recs); echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), $CFG->wwwroot.'/blocks/mou_school/index.php');

		if ($mode === 'add')  { //id, , , name, num, description, 
			$rec->yearid = $yid;
			$rec->stafftypeid = $stft;
			$rec->name = $recs->name;
			$rec->num = $recs->num;
			$rec->description = $recs->description;
			$rec->is_loadfile = $recs->is_loadfile;
			if (find_form_discipline_errors($rec, $err) == 0) {
				if ($cid = insert_record('monit_att_criteria', $rec))	{
					 save_estimates($recs, $yid, $stft, $cid);
					 // notice(get_string('recordadded','block_mou_att', $rec->name), $redirlink);
                     redirect($redirlink, get_string('recordadded','block_mou_att', $rec->name), 0);
				} else
					error(get_string('errorincreatingcriteria','block_mou_att'), $redirlink);
			} else $mode = "new";		
		} else if ($mode === 'update')	{
			$rec->id = required_param('cid', PARAM_INT);
			$rec->name = $recs->name;
			$rec->num = $recs->num;
			$rec->description = $recs->description;
			$rec->is_loadfile = $recs->is_loadfile;

			if (find_form_discipline_errors($rec, $err) == 0) {
				if (update_record('monit_att_criteria', $rec))	{
					 save_estimates($recs, $yid, $stft, $cid);
                     // notice(get_string('recordupdated','block_mou_att', $rec->name), $redirlink);
					 redirect($redirlink, get_string('recordupdated','block_mou_att', $rec->name), 0);
				} else
					error(get_string('errorinupdatingcriteria','block_mou_att'), $redirlink);
			}
		}
        //notice(get_string('succesavedata','block_mou_att'), $CFG->wwwroot.'/blocks/mou_school/curriculum/discipline.php?mode=2&amp;sid=$sid&amp;yid=$yid&amp;rid=$rid');
		// redirect("setpoints.php?rid=0&amp;yid=$yid", get_string('succesavedata','block_monitoring'), 0);
	}

	 $choosemenu = array();
	 $choosemenu[0] = get_string ('no');
	 $choosemenu[1] = get_string ('yes');
     
    if ($mode === 'new')  {
		$rec->id = 0;
		$rec->name = '';
		$rec->num = '';
		$rec->description = '';
		$rec->is_loadfile = 1;
		$rec2->id = 0;
		$cid = 0;
    	$mode = 'add';
    } else if ($mode === 'edit')	{
			if ($cid > 0) 	{
				$criteria = get_record('monit_att_criteria', 'id', $cid);
				$rec->id = $criteria->id;
				$rec->name = $criteria->name;
        		$rec->num = $criteria->num;
        		$rec->description = $criteria->description;
        		$rec->is_loadfile = $criteria->is_loadfile;
				// $rec2->id = $st->id;
			}
	    	$mode = 'update';
	}
    //  print_r($rec);
    // print_simple_box_start("center");
?>

	<form name="addform" method="post" action="editrefcrit.php">
	<center>
	<table cellpadding="5">

	<tr valign="top">
	    <td align="right"><b><?php  print_string("criteria", 'block_mou_att') ?>:</b></td>
	    <td align="left">
            <?php print_textarea(true, 6, 70, 0, 0, "name", $rec->name); 
            // <input name="name" type="text" id="name" value="p($rec->name)" size="80" />
            ?>
			
			<?php if (isset($err["name"])) formerr($err["name"]); ?>
	    </td>
	</tr>

	<tr valign="top">
	    <td align="right"><b><?php  print_string("number", 'block_monitoring') ?>:</b></td>
	    <td align="left">
			<input name="num" type="text" id="num" value="<?php p($rec->num) ?>" size="10" />
			<?php if (isset($err["num"])) formerr($err["num"]); ?>
	    </td>
	</tr>

	<tr valign="top">
	    <td align="right"><b><?php  print_string("description") ?>:</b></td>
	    <td align="left">
			<?php print_textarea(true, 6, 70, 0, 0, "description", $rec->description); ?>
	    </td>
	</tr>

	<tr valign="top">
	    <td align="right"><b><?php  print_string("udostovrdocs","block_mou_att") ?>:</b></td>
		<td align="left">  <?php   choose_from_menu ($choosemenu, 'is_loadfile', $rec->is_loadfile, "", "", "", false); ?>
						  <?php if (isset($err["is_loadfile"])) formerr($err["is_loadfile"]); ?>
		</td>
	</tr>


	</table>
    </div>
    </center>


<?php

    $table = new stdClass();
    $table->head  = array (	get_string('estimate', 'block_mou_att'), get_string('mark', 'block_mou_att'),
                            get_string('maxmark', 'block_mou_att'), get_string('typefield', 'block_mou_att'),
                            get_string('namefield', 'block_mou_att'), get_string('printname', 'block_mou_att'),
                            get_string('action', 'block_monitoring'));
   	$table->align = array ("left", "center", "center", "center", "center", "center", "center");
    $table->class = 'moutable';
    $table->width = '80%';
	$table->size = array ('40%', '10%', '10%', '10%', '10%', '10%', '10%');


     if ($estimates = get_records_select('monit_att_estimates', "criteriaid = $cid", 'mark', 'id, criteriaid, name, mark, maxmark, typefield, namefield, printname'))    {

        $strmonit_att_estimates = '';
        $strshort = '';
        //  $monit_att_estimates = get_records('monit_att_estimates','disciplineid',$did);
        // print_heading($straddperiod, "center", 3);

        $title = get_string('delcrit', 'block_mou_att');

    //$monit_att_estimatess = get_records_sql("SELECT * FROM {$CFG->prefix}monit_att_estimates WHERE schoolid=$sid AND disciplineid={$disc->id}");
        foreach ($estimates as $estimate)   {
            $tabledata = array();
            $tabledata[] = "<input type=text name=name_{$estimate->id} size=90 value=\"$estimate->name\">";
            $tabledata[] = "<input type=text name=mark_{$estimate->id} size=3 value=\"$estimate->mark\">";
            $tabledata[] = "<input type=text name=mmrk_{$estimate->id} size=3 value=\"$estimate->maxmark\">";
         	$choosemenu = array();
	        $choosemenu[''] = '';
	        $choosemenu['text'] = 'текстовое';
            $choosemenu['num'] = 'числовое';
            $choosemenu['text_num'] = 'оба поля';
            // $tabledata[] = "<input type=text name=tfld_{$estimate->id} size=5 value=\"$estimate->typefield\">";
            $tabledata[] = choose_from_menu ($choosemenu, 'tfld_'.$estimate->id, $estimate->typefield, "", "", "", true);

         	$choosemenu2 = array();
	        $choosemenu2[''] = '';
            for ($i=1; $i<=30; $i++)    {
	           $choosemenu2['name'.$i] = 'name'.$i;
            }
            $choosemenu2['number1'] = 'number1';
            // $tabledata[] = "<input type=text name=nfld_{$estimate->id} size=5 value=\"$estimate->namefield\">";
            $tabledata[] = choose_from_menu ($choosemenu2, 'nfld_'.$estimate->id, $estimate->namefield, "", "", "", true);
            $tabledata[] = "<input type=text name=prnm_{$estimate->id} size=5 value=\"$estimate->printname\">";
            $strlinkupdate = "<a title=\"$title\" href=\"delcrit.php?yid=$yid&part=est&id=$estimate->id&stft=$stft\">";
	       	$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
            $tabledata[] = $strlinkupdate;
            $table->data[] = $tabledata;
        }
    }           
  
    $tabledata = array();
    $tabledata[] = "<input type=text name=name_0 size=90 value=''>";
    $tabledata[] = "<input type=text name=mark_0 size=3 value=''>";
    $tabledata[] = "<input type=text name=mmrk_0 size=3 value=''>";
    // $tabledata[] = "<input type=text name=tfld_0 size=5 value=''>";
    $tabledata[] = choose_from_menu ($choosemenu, 'tfld_0', '', "", "", "", true);
    $tabledata[] = choose_from_menu ($choosemenu2, 'nfld_0', '', "", "", "", true);
    $tabledata[] = "<input type=text name=nfld_0 size=5 value=''>";
    $tabledata[] = "<input type=text name=prnm_0 size=5 value=''>";
    $tabledata[] = '';
    $table->data[] = $tabledata;
          
	echo  '<input type="hidden" name="mode" value="' . $mode . '">';
	echo  '<input type="hidden" name="stft" value="' .  $stft  . '">';
	echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
	echo  '<input type="hidden" name="cid" value="' .  $cid . '">';
    
	print_color_table($table);
	echo  '<div align="center">';
	echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"></div>';
	echo  '</form><p>&nbsp;</p>';


	print_footer();


function find_form_discipline_errors(&$rec, &$err, $mode='add') {

    if (empty($rec->name))	{
	    $err["name"] = get_string("missingname");
	}
    if (empty($rec->num))	{
	    $err["num"] = get_string("missingname");
	}

    return count($err);
}


function save_estimates($recs, $yid, $stft, $cid)
{
	global $CFG, $redirlink;

    // echo '<pre>'; print_r($recs); echo '</pre>';
     
    $flag=true;
    $reffldnames = array('name_', 'mark_', 'mmrk_', 'tfld_', 'nfld_', 'prnm_');
    $dbfldnames = array('name', 'mark', 'maxmark', 'typefield', 'namefield', 'printname');
	$estimatesids = array();
    
	foreach($recs as $fieldname => $value)	{
        $mask = substr($fieldname, 0, 5);
        $ids = explode('_', $fieldname);
        
        if (isset($ids[1]))  $eid = $ids[1];
        else $eid = 0;

		if ($value != '')	{
            $key = array_search($mask, $reffldnames); 
            if ($key === false)	continue;
           	$estimatesids[$eid]->{$dbfldnames[$key]} = $value;
        } else if ($eid > 0 && ($mask == 'tfld_' ||  $mask ==  'nfld_' || $mask == 'prnm_'))    {
            $key = array_search($mask, $reffldnames); 
            if ($key === false)	continue;
           	$estimatesids[$eid]->{$dbfldnames[$key]} = $value;
        }  
    }        
           
    // echo '<pre>'; print_r($estimatesids); echo '</pre>';
    if (!empty($estimatesids))  {
        foreach ($estimatesids as $eid => $estimate) {
            if ($eid > 0)   {
                $estimate->id = $eid; 
				if (!update_record('monit_att_estimates', $estimate))	{
				    echo '<pre>'; print_r($estimate); echo '</pre>';
					error(get_string('errorinupdatingcriteria','block_mou_att'), $redirlink);
                }    
            } else {
                $estimate->criteriaid = $cid;
				if (!insert_record('monit_att_estimates', $estimate))	{
				    echo '<pre>'; print_r($estimate); echo '</pre>';
					error(get_string('errorincreatingcriteria','block_mou_att'), $redirlink);
                }    
                
            }
            
        }
    }
 }

?>
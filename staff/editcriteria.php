<?php // $Id: editcriteria.php,v 1.7 2014/06/16 12:00:06 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->libdir.'/uploadlib.php');
	require_once($CFG->libdir.'/filelib.php');    
    require_once('../../monitoring/lib.php');	    
    require_once('../../mou_accredit/lib_accredit.php');
    require_once('../lib_att2.php');

    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $uid = required_param('uid', PARAM_INT);          // User id
    $cid = required_param('cid', PARAM_INT);          // Critetia id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = required_param('stft', PARAM_INT);  	  // Type stafftype id    
    $action   = optional_param('action', '');
    $yid = optional_param('yid', 0, PARAM_INT);  	  // Current year id
    $cyid = optional_param('cyid', 0, PARAM_INT);  	  // Current criteria year id (бывший $YEARID_CRITERIA)

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    $edutype = get_config_typeou($typeou);
    $stafftype = get_record_select('monit_att_stafftype', "id = $stft", 'id, name, att_result');
    $lastcyid = get_last_yearid_in_criteria($stafftype->id);
    if ($cyid == 0 )    {
        $cyid = $lastcyid;
    }

	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
    $view_capability = has_capability('block/mou_att2:viewattestationuser', $context);
    $editownprofile = has_capability('block/mou_att2:editownprofile', $context);
    
	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editattestationuser', $context_rayon);

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');
    $strmarkscriteria = get_string('marks__', 'block_mou_att') . ': '. $stafftype->name;

    $redirlink = "staffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid&stft=$stft";

    $strsql = "SELECT s.id, a.appointment
               FROM mdl_monit_att_staff s INNER JOIN mdl_monit_att_appointment a ON s.id=a.staffid
               WHERE s.userid=$uid and a.stafftypeid=$stft";    
    $staff = get_record_sql($strsql);
    // print_r($staff); exit();
    $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$uid");
    $redirlink2 = "attestation.php?rid=$rid&oid=$oid&yid=$yid&uid=$uid&typeou=$typeou&stft=$stft";
    
   	$strstaffs = $stafftype->name;
	$strappointment = $staff->appointment;
    $straction = get_string('action', 'block_monitoring');
	$stronecriteria = get_string('onecriteria', 'block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $strmarkscriteria, 'link' => $redirlink2, 'type' => 'misc');
    $navlinks[] = array('name' => $stronecriteria, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $stronecriteria, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); 

    if (!$edit_capability && !($editownprofile && $uid == $USER->id))  {
		error(get_string('permission', 'block_mou_school'), $redirlink);
    }    

	// !!!!!!!!!!!	$rayon_operator_is !!!!!!!!!!!!!!!!!!!!
    // if (!$admin_is && !$region_operator_is && !$rayon_operator_is)  {
    if (!$edit_capability_rayon)  {
        $strselect = "staffid = $staff->id AND stafftypeid = $stft AND criteriaid = $cid";
	    if ($att = get_record_select('monit_att_attestation', $strselect, 'id, status')) {
	    	if ($att->status >= 6)	{ // if ($att->status >= 4)	{
		        error(get_string('erroreditcriteria', 'block_mou_att'), $redirlink2);
	    	}
	    }
	}

	/// A form was submitted so process the input
	if ($rec = data_submitted())  {
	    // print_r($rec);  echo '<hr>';
	    // print_r($_FILES);  echo '<hr>';

	   if (!empty($_FILES['newfile']['name']))	{
       		$dir = "0/users/att/$rid/$uid/$cid";
       		$um = new upload_manager('newfile',true,false, 1, false, MAX_SCAN_COPY_SIZE);
       		// print_r($um);  echo '<hr>';
	        if ($um->process_file_uploads($dir))  {
		          // $newfile_name = $um->get_new_filename();
        	      // print_heading(get_string('uploadedfile'), 'center', 4);
          	} else {
	          	  notify(get_string("uploaderror", "assignment")); //submitting not allowed!
       		}
	   }
	   

	   $attestation->yearid = $cyid; // $yid;
       $attestation->staffid = $staff->id;
       $attestation->stafftypeid = $stft;
       $attestation->criteriaid = $cid;
       $attestation->mark = $rec->estimate;
	   
       $is_estim_data = false;
       if ($estimate = get_record('monit_att_estimates', 'criteriaid', $cid, 'mark', $rec->estimate))	{
   		    // print_r($estimate);  echo '<hr>';
	       	$estim_data = $estimate;
	       if (!empty($estim_data->typefield))	{
	   		   // print_r($estim_data);  echo '<hr>';
               $is_estim_data = true;
               switch($estim_data->typefield) {
               		case 'date':
               		   $namefields = split(';', $estim_data->namefield);
               		   foreach ($namefields as $namefield) 	{
	    					if (isset($rec->{$namefield}) && !empty($rec->{$namefield}))	{
						   		if (!is_date($rec->{$namefield})) {
					 	      		$err[$namefield] = get_string('errorinputdate', 'block_mou_att');
					  	     	} else {
					  	     		$rec->{$namefield} = convert_date($rec->{$namefield});
					  	     	}
					   		}
					   }
					break;
               		case 'text':
              		   $namefields = split(';', $estim_data->namefield);
              		break;
               		case 'text_num':
              		   $namefields = split(';', $estim_data->namefield);
               		   $namefield =	$namefields[1];
    				   if (isset($rec->{$namefield}) && !empty($rec->{$namefield}))	{
						   		if (!is_numeric($rec->{$namefield})) {
					 	      		$err[$namefield] = get_string('errorinputnum', 'block_mou_att');
					  	     	} else {
					  	     		 $attestation->mark = $rec->estimate*$rec->{$namefield};
					  	     		 if ($attestation->mark > $estimate->maxmark)	$attestation->mark = $estimate->maxmark;
					  	     	}
			   		   }
					break;
               		case 'num':
	              	   $namefields = split(';', $estim_data->namefield);
               		   $namefield =	$estim_data->namefield;
    				   if (isset($rec->{$namefield}) && !empty($rec->{$namefield}))	{
						   		if (!is_numeric($rec->{$namefield})) {
					 	      		$err[$namefield] = get_string('errorinputnum', 'block_mou_att');
					  	     	} else {
					  	     		 $attestation->mark = $rec->estimate*$rec->{$namefield};
					  	     		 if ($attestation->mark > $estimate->maxmark)	$attestation->mark = $estimate->maxmark;
					  	     	}
			   		   }
					break;
				}
		   }
       }

       if (!isset($err)) {
           
           if ($edit_capability || ($editownprofile && $uid == $USER->id))  {
           
            $strmessagesave = get_string('succesavedata','block_monitoring');

			$attestation->namefield = '';
			$attestation->fielddata = '';


		    if ($att = get_record('monit_att_attestation', 'staffid', $staff->id, 'criteriaid', $cid))	 {
		    	
				$attestation->id = $att->id;
				
				if ($is_estim_data)		{
					$strestimatemark = $strnamesfld = '';
					foreach ($namefields as $namefield) {
						$strnamesfld .= $namefield . ';';
						if (isset($rec->{$namefield}) && !empty($rec->{$namefield}))	{
								$strestimatemark .= $rec->{$namefield} . '$$';
						} else {
								$strestimatemark .=  '0$$';
						}
					}
					$attestation->namefield = $strnamesfld . '#';
					$attestation->fielddata = $strestimatemark . '#';
				}	
				
			    if (!update_record('monit_att_attestation', $attestation))	{
					error(get_string('errorinupdatingcriteria','block_mou_att'), $redirlink2);
				}

	       	} else {
	       	
			   if ($is_estim_data)	{
					$strestimatemark = $strnamesfld = '';
					foreach ($namefields as $namefield) {
						$strnamesfld .= $namefield . ';';
						if (isset($rec->{$namefield}) && !empty($rec->{$namefield}))	{
								$strestimatemark .= $rec->{$namefield} . '$$';
						} else {
								$strestimatemark .=  '0$$';
						}
					}
					$attestation->namefield = $strnamesfld . '#';
					$attestation->fielddata = $strestimatemark . '#';
			   }

		       if (!$new_id = insert_record('monit_att_attestation', $attestation))	{
					error(get_string('errorincreatingcriteria','block_mou_att'), $redirlink2);
			   }
		    }
		  } else {
				$strmessagesave = '';
		  }

          if (isset($rec->copynext))  {
			  	$cid = get_next_id_bycircle ('monit_att_criteria', "stafftypeid=$stft AND yearid=$cyid", $cid, '+');
	            // notice(get_string('succesavedata','block_monitoring'), "editcriteria.php?cat=$category&rid=$rid&sid=$sid&uid=$uid&cid=$cid&num=$num");
                redirect("editcriteria.php?rid=$rid&oid=$oid&yid=$yid&uid=$uid&typeou=$typeou&stft=$stft&cid=$cid&cyid=$cyid", $strmessagesave, 0);
		  } else if (isset($rec->copyprev))  {
			  	$cid = get_next_id_bycircle ('monit_att_criteria', "stafftypeid=$stft AND yearid=$cyid", $cid, '-');
	            // notice(get_string('succesavedata','block_monitoring'), "editcriteria.php?cat=$category&rid=$rid&sid=$sid&uid=$uid&cid=$cid&num=$num");
                redirect("editcriteria.php?rid=$rid&oid=$oid&yid=$yid&uid=$uid&typeou=$typeou&stft=$stft&cid=$cid&cyid=$cyid", $strmessagesave, 0);
		  } else {
	          // $strlinkupdate = "<a title=\"$title\" href=\"editcriteria.php?cat=$category&rid=$rid&sid=$sid&uid=$uid&cid={$criteria->id}&num=$num\">";
	          // notice(get_string('succesavedata','block_monitoring'), "$CFG->wwwroot/blocks/mou_att/staff/attestation.php?cat=$category&rid=$rid&sid=$sid&uid=$uid");
		   	  redirect($redirlink2, $strmessagesave, 0);
		  }
       }

    }
	/// A form was submitted END

   	// print_heading($strmarkscriteria, 'center', 2);
    print_heading(fullname($user) . ', ' . $staff->appointment, 'center', 3);
   	// print_heading('('.$rayon->name.', '.$school->name.')', 'center', 4);
	if (!$criteria =  get_record_select('monit_att_criteria', "id = $cid", 'id, name, num, description, is_loadfile'))	{
		add_to_log(1, 'editcriteria.php', 'not found', 'criteria ', fullname($USER), '', $USER->id);
		error('Criteria not found!', '../index.php'); 
	}

	$estimates = get_records_select('monit_att_estimates', "criteriaid = $cid", 'mark', 'id, criteriaid, name, mark, maxmark, typefield, namefield, printname');

   // print_simple_box($text, 'center', '70%', 'white', 5, 'generalbox', 'intro');

    print_simple_box_start('center', '70%', 'white');

	$text = '<b>'.$stronecriteria.' №'.$criteria->num . '.</b><i><b> ' . $criteria->name . '</b></i>';
    echo $text;

    foreach ($estimates as $estimate) {
        $answerchecked[$estimate->mark] = '';
    }

    $rows = 3;
    $cols = 110;

    $strselect = "staffid = $staff->id AND stafftypeid = $stft AND criteriaid = $cid";
    if ($attestation = get_record_select('monit_att_attestation', $strselect, 'id, mark, status, namefield, fielddata'))   {

        $checkedelement = $attestation->mark;

        foreach ($estimates as $key => $estimate)  {
       	   $estim_data = $estimate;
           if (!empty($estim_data->typefield))	{
               if ($estim_data->typefield == 'text_num')	{
           		   $namefields = split(';', $estim_data->namefield);
           		   // print_r($printnames); echo '<hr>'; print_r($namefields); echo '<hr>';
           		   $namefield =	$namefields[1];
			  	   if ($att_table = get_supplement_data($attestation))	{
			  	  	  if(!empty($att_table[$namefield]))	 {
			  	  	  	 $checkedelement = $estimate->mark;
			  	  	  	 $estimates[$key]->text_num = true;
			  	  	  	 break;
			  	  	  }
		  	   	   }
               }  else if ($estim_data->typefield == 'num')	{
           		   $namefield =	$estim_data->namefield;
			  	   if ($att_table = get_supplement_data($attestation))	{
			  	  	  if(!empty($att_table[$namefield]))	 {
			  	  	  	 $checkedelement = $estimate->mark;
			  	  	  	 $estimates[$key]->text_num = true;
			  	  	  	 break;
			  	  	  }
		  	   	   }
               }
           }
        }

        $answerchecked[$checkedelement] = 'checked="checked"';

    } else {
        foreach ($answerchecked as $key => $value)	{
        	if ($key >= 0)	{
		    	$answerchecked[$key] = 'checked="checked"';
		    	break;
		    }
		}
    	// reset($answerchecked);
    	// list($key, $val) = each($answerchecked);
		// $answerchecked[0] = 'checked="checked"';
    }

    // print_r($attestation); echo '<hr>';
    // print_r($answerchecked);

    echo '<form enctype="multipart/form-data" name=form method=post action=editcriteria.php>';
    echo '<table cellpadding=10 cellspacing=10 align=center valign=top>';

	if (count($estimates) > 1)	{
	    foreach ($estimates as $estimate) {
	
			   $strb =	slovo_ballov($estimate->mark);
			   /*
	    	   switch ($estimate->mark)	{
	    		   	case 0:	 $strb = 'баллов'; break;
	   	 		   	case 1:	 $strb = 'балл'; break;
	  	  	 	  	case 5:
	  	  	 	  	case 10: $strb = 'баллов'; break;
	 	   	  	 	default: $strb = 'балла';
		       }
	 			*/
	           if (isset($estimate->text_num))	{
		 		   $text = "$estimate->name";
		 	   } else {
			 	   $text = "$estimate->name ($estimate->mark $strb)";
		 	   }
	
	           echo "<tr><td>";
	
	           if ($answerchecked[$estimate->mark]) {
	    		    echo " <strong><font color='green'>";
	           }
	
		       echo "<input type=radio name=estimate value=\"".$estimate->mark."\" ".$answerchecked[$estimate->mark]." alt=\"".$text."\" />";
	           echo $text;
	
	           if ($answerchecked[$estimate->mark]) {
	    		    echo "</font></strong>";
	           }
	
	       	   $estim_data = $estimate;
	           if (!empty($estim_data->typefield))	{
	               switch($estim_data->typefield)  {
	               		case 'date':
	               		   $printnames = split(';', $estim_data->printname);
	               		   $namefields = split(';', $estim_data->namefield);
	               		   $i=0;
	               		   foreach ($printnames as $printname) {
		               		  $namefield =	$namefields[$i];
	     	        	      echo '<br>' . $printname.':&nbsp;';
						 	  echo "<INPUT maxLength=10 size=10 name=$namefield ";
						 	  if (isset($err[$namefield])) echo 'style="border-color:#FF0000"';
                              
                              $strselect = "staffid = $staff->id AND stafftypeid = $stft AND criteriaid = $cid";
                              if ($att = get_record_select('monit_att_attestation', $strselect, 'id, mark, status, namefield, fielddata'))   {
							  	   if ($att_table = get_supplement_data($att))	{
							  	  	  if(!empty($att_table[$namefield]))	 {
										echo ' value="' . convert_date($att_table[$namefield], 'en', 'ru') . '"';						  	  	  
									  }
						  	   	   }
							  }
							  echo '>';
							  if (isset($err[$namefield])) formerr($err[$namefield]);
							  $i++;
						   }
	               		break;
	               		case 'text':
	               		   $printnames = split(';', $estim_data->printname);
	               		   $namefields = split(';', $estim_data->namefield);
	               		   $i=0;
	               		   foreach ($printnames as $printname) {
		               		  $namefield =	$namefields[$i];
	     	        	      echo '<br><table cellpadding=0 cellspacing=0 align=center valign=top><tr><td>' . $printname.':&nbsp;';
						 	  // echo "<INPUT maxLength=500 size=100 name=$namefield ";
                              echo '</td><td><textarea id="edit-'. $namefield .'" name="'. $namefield .'" rows="'. $rows .'" cols="'. $cols .'">';
						 	  // if (isset($err[$namefield])) echo 'style="border-color:#FF0000"';

                              $strselect = "staffid = $staff->id AND stafftypeid = $stft AND criteriaid = $cid";
                              if ($att = get_record_select('monit_att_attestation', $strselect, 'id, mark, status, namefield, fielddata'))   {
							  	   if ($att_table = get_supplement_data($att))	{
							  	  	  if(!empty($att_table[$namefield]))	 {
							  	  	  	 $strt = $att_table[$namefield];
							  	  	  	 // echo " value='$strt' ";
                                         echo $strt;
							  	  	  }	 
						  	   	   }
							  }
							  // echo '>';
                              echo '</textarea></td></tr></table>';
							  if (isset($err[$namefield])) formerr($err[$namefield]);
							  $i++;
						   }
	               		break;
	               		case 'text_num':
	               		   $printnames = split(';', $estim_data->printname);
	               		   $namefields = split(';', $estim_data->namefield);
	               		   // print_r($printnames); echo '<hr>'; print_r($namefields); echo '<hr>';
	               		   $i=0;
	               		   $namefield =	$namefields[0];
	    	        	   //echo '<br>' . $printnames[0].':&nbsp;';
                           echo '<br><table cellpadding=0 cellspacing=0 align=center valign=top><tr><td>' . $printnames[0].':&nbsp;';
						   // echo "<INPUT maxLength=500 size=100 name=$namefield ";
                           echo '</td><td><textarea id="edit-'. $namefield .'" name="'. $namefield .'" rows="5" cols="'. $cols .'">';
                           $strselect = "staffid = $staff->id AND stafftypeid = $stft AND criteriaid = $cid";
                           if ($att = get_record_select('monit_att_attestation', $strselect, 'id, mark, status, namefield, fielddata'))   {
							  	   if ($att_table = get_supplement_data($att))	{
							  	  	  if(!empty($att_table[$namefield]))	 {
							  	  	  	 $strt = $att_table[$namefield];
							  	  	  	 // echo " value='$strt' ";
                                         echo $strt;
							  	  	  }	 
						  	   	   }
						   }
						   // echo '>';
                            echo '</textarea></td></tr>';
	               		   $namefield =	$namefields[1];
	    	        	   echo '<br><tr><td>' . $printnames[1].':&nbsp;';
					 	   echo "</td><td><INPUT maxLength=5 size=5 name=$namefield ";
					 	   			  if(!empty($att_table[$namefield]))	 {
							  	  	  	 $strt = $att_table[$namefield];
							  	  	  	 echo " value='$strt' ";
							  	  	  }
	   					   echo '></td></tr></table>';
	               		break;
	               		case 'num':
                           if (!empty($estim_data->maxmark)) {
                        		$strb =	slovo_ballov($estim_data->maxmark);
                        		echo "&nbsp;&nbsp;(максимальное количество - $estimate->maxmark $strb): ";
                           }    
                           
	               		   $namefield =	$estim_data->namefield;
	    	        	   echo '<br>' . $estim_data->printname .':&nbsp;';
						   echo "<INPUT maxLength=5 size=5 name=$namefield ";

                           $strselect = "staffid = $staff->id AND stafftypeid = $stft AND criteriaid = $cid";
                           if ($att = get_record_select('monit_att_attestation', $strselect, 'id, mark, status, namefield, fielddata'))   {
							  	   if ($att_table = get_supplement_data($att))	{
							  	  	  if(!empty($att_table[$namefield]))	 {
							  	  	  	 $strt = $att_table[$namefield];
							  	  	  	 echo " value='$strt' ";
							  	  	  }	 
						  	   	   }
						   }
						   echo '>';
	               		break;
	               }
	
	           }
	           echo '</td></tr>';
	    }
	} else {
		$estimate = end($estimates);
		$strb =	slovo_ballov($estimate->maxmark);
		echo "<tr><td>";
		echo " <strong><font color='green'>";
		echo "&nbsp;&nbsp;$estimate->name (максимальное количество - $estimate->maxmark $strb): ";
        if (!isset($attestation->mark)) {
            $attestation->mark = 0;
        }
		echo "<INPUT maxLength=3 size=3 name=estimate  value='$attestation->mark'>";
		echo "</font></strong>";
		echo '</td></tr>';
	}    
	    
	echo '</table>';

	if ($yid != 2 && $criteria->is_loadfile == 1)	{
		
	echo '<hr>';
/*	
	if (!empty($criteria->description))	{
		// echo '<tr><td align=left>';
		$strudostdoc = '<p><b>'.get_string ('udostovrdocs', 'block_mou_att') . '</b>: <i><b>' . $criteria->description . '</b></i></p>';
		echo $strudostdoc;
		// echo '</td></tr>';	
	}
*/	

	echo '<table cellpadding=10 cellspacing=10 align=center>';
	echo '<tr><td align=center>';
	
    $CFG->maxbytes = MAX_SCAN_COPY_SIZE; 

    $struploadafile = get_string('loadfiledocs', 'block_mou_att');
    $strmaxsize = get_string("maxsize", "", display_size($CFG->maxbytes));

	echo "<p>$struploadafile(<b><i>$strmaxsize</i></b>):";
	helpbutton('uploadmanual', $struploadafile, 'mou');
	echo "</p>";
    upload_print_form_fragment(1,array('newfile'),false,null,0,$CFG->maxbytes,false);

	echo '</td></tr><tr><td align=center>';

	print_string('loadedfiledocs', 'block_mou_att');
	$filearea = "0/users/att/$rid/$uid/$cid";
    $delurlparam = "rid=$rid&oid=$oid&uid=$uid&cid=$cid&stft=$stft&yid=$yid&typeou=$typeou";    
    
    $output = show_upload_files ($filearea, $delurlparam);
//    echo '<div class="files">'.$output.'</div>';
  	echo $output;

	echo '</td></tr></table>';
	}
    
	    
?>
	
	<center>
    <input type="hidden" name="yid" value="<?php echo $yid ?>" />
	<input type="hidden" name="rid" value="<?php echo $rid ?>" />
	<input type="hidden" name="oid" value="<?php echo $oid ?>" />
	<input type="hidden" name="uid" value="<?php echo $uid ?>" />
	<input type="hidden" name="cid" value="<?php echo $cid ?>" />
    <input type="hidden" name="cyid" value="<?php echo $cyid ?>" />
	<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
	<input type="hidden" name="stft" value="<?php echo $stft?>" />                            
	<input type="hidden" name="action" value="copy" />
	<table align="center">
	<tr>
		<td align="center">
		<input type="submit" name=copyprev value="<?php print_string('prevcriteria', 'block_mou_att') ?>" />
		</td>
		<td align="center">
		<input type="submit" name=save value="<?php  print_string('savechanges') ?>" />
		</td>
		<td align="center">
		<input type="submit" name=copynext value="<?php print_string('nextcriteria', 'block_mou_att') ?>" />
		</td>
		</tr>
		</table>
    </center>
    </form>
<?php

    if (!empty($criteria->description))	{
    	$isrc = get_string('comments', 'block_mou_att');
        // <p>&nbsp;</p>
    	echo '<p align=right><i><small><strong>' . $isrc . ': </strong>' . $criteria->description . '</small></i>';
    }


  	print_simple_box_end();

    print_footer();
?>
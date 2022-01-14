<?php // $Id: attestation.php,v 1.12 2014/06/16 12:00:06 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->libdir.'/filelib.php');    
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');    
    require_once('../lib_att2.php');

    $currenttab = 'attestation';
    include('tabsatt.php'); 
   
    if ($output != '')	{
	   $strpred .= ": $output";
    } else {
        $strpred .= ': -';// get_string("predstavlenienull", 'block_mou_att');
        // $strfio .= " <i>($strpred)</i>";
    }	 		    
    print_heading($strpred, 'center', 4);
    
	$strsql = "SELECT Sum(a.mark) AS sum
			   FROM mdl_monit_att_staff as s INNER JOIN mdl_monit_att_attestation as a ON s.id = a.staffid
			   WHERE s.userid = $uid AND a.yearid=$cyid AND a.stafftypeid=$stft"; 
    // print $strsql;                
 	$sum = '-';
    if ($rec = get_record_sql($strsql))  {
		$sum = $rec->sum;
	}

   	$strtotlamark = get_string('total_mark', 'block_mou_att') . ': ' . $sum;
	print_heading($strtotlamark, 'center', 4);
    
    $table = table_attestation($rid, $oid, $uid, $typeou, $stft, $edutype, $cyid, $lastcyid);	
    
    if (isset($table->data))	{
		 
		 print_color_table($table);
		 echo	'<table align="center"><tr>';
	
		 if ($edit_capability_rayon) 	{
	
				 if ($minstatus == 4)  {
			        	$readonly = ' ';
		       	 } else {
		        		$readonly = 'disabled';
		       	 }
	
			    if ($minstatus != 6)   {
				      ?><td>
						<form name="setok" method="post" action="changestatus.php">
						    <div align="center">
                            <input type="hidden" name="cyid" value="<?php echo $cyid ?>" />
						    <input type="hidden" name="yid" value="<?php echo $yid ?>" />
							<input type="hidden" name="rid" value="<?php echo $rid ?>" />
							<input type="hidden" name="oid" value="<?php echo $oid ?>" />
							<input type="hidden" name="uid" value="<?php echo $uid ?>" />
							<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
							<input type="hidden" name="stft" value="<?php echo $stft?>" />                            
                            <input type="hidden" name="status" value="6" />
							<input type="submit" name="btnsetok" <?php echo $readonly ?>  value="<?php print_string('setok', 'block_mou_att')?>">
						    </div>
					  </form>
					  </td>
					<?php
			    }
			    if ($minstatus >= 4)	{
					?><td>
					<form name="setback" method="post" action="changestatus.php">
					    <div align="center">
                            <input type="hidden" name="cyid" value="<?php echo $cyid ?>" />
						    <input type="hidden" name="yid" value="<?php echo $yid ?>" />
							<input type="hidden" name="rid" value="<?php echo $rid ?>" />
							<input type="hidden" name="oid" value="<?php echo $oid ?>" />
							<input type="hidden" name="uid" value="<?php echo $uid ?>" />
							<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
							<input type="hidden" name="stft" value="<?php echo $stft?>" />
                            <input type="hidden" name="status" value="3" />                            
        					<input type="submit" name="btnsetback"   value="<?php print_string('setback', 'block_mou_att')?>">
					    </div>
				  </form>
				  </td>
			 	 <?php
			    }
	     } else  {
	        	if ($minstatus == 1) {
		        	$readonly = 'disabled';
	        	} else {
	        		$readonly = ' ';
	        	}
	        	if ($minstatus < 4)	{
					?> <td>
						<form name="sendtocoordination" method="post" action="changestatus.php">
						    <div align="center">
                            <input type="hidden" name="cyid" value="<?php echo $cyid ?>" />
						    <input type="hidden" name="yid" value="<?php echo $yid ?>" />
							<input type="hidden" name="rid" value="<?php echo $rid ?>" />
							<input type="hidden" name="oid" value="<?php echo $oid ?>" />
							<input type="hidden" name="uid" value="<?php echo $uid ?>" />
							<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
							<input type="hidden" name="stft" value="<?php echo $stft?>" />                            
			                 <input type="hidden" name="status" value="4" />
							<input type="submit" name="sendtook" <?php echo $readonly ?> value="<?php print_string('sendtocoordination', 'block_mou_att')?>">
						    </div>
					  </form>
					  </td>
					<?php
				}
	      }
	?>
				<td>
				<form name="download" method="post" action="attestation.php">
				    <div align="center">
                            <input type="hidden" name="cyid" value="<?php echo $cyid ?>" />
						    <input type="hidden" name="yid" value="<?php echo $yid ?>" />
							<input type="hidden" name="rid" value="<?php echo $rid ?>" />
							<input type="hidden" name="oid" value="<?php echo $oid ?>" />
							<input type="hidden" name="uid" value="<?php echo $uid ?>" />
							<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
							<input type="hidden" name="stft" value="<?php echo $stft?>" />                            
					        <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>">
		                    <input type="hidden" name="action" value="excel" />
					        <input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">
				    </div>
			  </form>
			  </td>
				<td>
				<form name="download" method="post" action="attestation.php">
				    <div align="center">
                            <input type="hidden" name="cyid" value="<?php echo $cyid ?>" />
						    <input type="hidden" name="yid" value="<?php echo $yid ?>" />
							<input type="hidden" name="rid" value="<?php echo $rid ?>" />
							<input type="hidden" name="oid" value="<?php echo $oid ?>" />
							<input type="hidden" name="uid" value="<?php echo $uid ?>" />
							<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
							<input type="hidden" name="stft" value="<?php echo $stft?>" />                            
					        <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>">
					<input type="hidden" name="action" value="word" />
					<input type="submit" name="downloadexcel" value="<?php print_string('downloadword', 'block_mou_att')?>">
				    </div>
			  </form>
			  </td>
				<td>
				<form name="download" method="post" action="attestation.php">
				    <div align="center">
                            <input type="hidden" name="cyid" value="<?php echo $cyid ?>" />
						    <input type="hidden" name="yid" value="<?php echo $yid ?>" />
							<input type="hidden" name="rid" value="<?php echo $rid ?>" />
							<input type="hidden" name="oid" value="<?php echo $oid ?>" />
							<input type="hidden" name="uid" value="<?php echo $uid ?>" />
							<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
							<input type="hidden" name="stft" value="<?php echo $stft?>" />                            
					        <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>">
					<input type="hidden" name="action" value="writer" />
					<input type="submit" name="downloadexcel" value="<?php print_string('downloadwriter', 'block_mou_att')?>">
				    </div>
			  </form>
			  </td>
			  
	<?php
		echo '</tr></table><p><p><p>';

	    print_simple_box_start('center', '50%', 'white');
	   	print_heading($strtotlamark, 'center', 4);
	   	echo $stafftype->att_result; 
	    print_simple_box_end();

	} 	else {
			notice('Критерии не найдены!');
	}

    print_footer();



function table_attestation($rid, $oid, $uid, $typeou, $stft, $edutype, $cyid, $lastcyid, $what = 'attestation')
{
    global $CFG, $minstatus, $staff, $sum, $user, $strmarkscriteria,
           $edit_capability_rayon, $edit_capability, $view_capability;

    $strudostovrdocs = get_string('udostovrdocs', 'block_mou_att');
    
    if (empty($sum))    {
    	$strsql = "SELECT Sum(a.mark) AS sum
    			   FROM mdl_monit_att_staff as s INNER JOIN mdl_monit_att_attestation as a ON s.id = a.staffid
    			   WHERE s.userid = $uid AND a.yearid=$cyid AND a.stafftypeid=$stft";
        // print $strsql;
     	$sum = '-';
        if ($rec = get_record_sql($strsql))  {
    		$sum = $rec->sum;
    	}
    }
    
    $table = new stdClass();
    $table->head  = array ('№', get_string('criteria', 'block_mou_att'),
	    						get_string('mark', 'block_mou_att'),
	    						get_string('action', 'block_monitoring'));
    $table->align = array ('right', 'left', 'center', 'center');
    $table->class = 'moutable';
    $table->columnwidth = array (2, 75, 8, 15);
   	$table->titles = array();
   	
   	$table->titles[] = $strmarkscriteria;// get_string('marks_'.$category, 'block_mou_att');
   	$table->titles[] = fullname($user) . ', ' . $staff->appointment;
   	$table->titles[] = get_string('total_mark', 'block_mou_att') . ': ' . $sum;
   	$table->titlesrows = array(30, 30, 30);
    $table->downloadfilename = 'attestation_'.$oid.'_'.$uid;
	$table->worksheetname = $table->downloadfilename;

     //id, yearid, stafftypeid, name, num, description, is_loadfile
	$strsql= " SELECT id, name, num, description, is_loadfile FROM {$CFG->prefix}monit_att_criteria
			 	WHERE stafftypeid = $stft AND yearid=$cyid
				ORDER BY num";
	// echo $strsql; 			
    if($criterions =  get_records_sql($strsql)) {
          // print_r ($criterions); echo '<hr>';

          $minstatus = 6;
	    	$list_cid_id = array();
		    if ($criterions)	{
		    	foreach($criterions as $criterion)	{
		 	   		 $list_cid_id[$criterion->id] = $criterion->num;
	  	  		}
	    		// print_r ($list_cid); echo '<hr>';
		   	 }
          foreach ($criterions as $criteria) {
				$links = array();
                $strestimate = ' ';
                
                $strselect = "staffid = $staff->id AND stafftypeid = $stft AND criteriaid = $criteria->id";

				if ($att = get_record_select('monit_att_attestation', $strselect, 'id, mark, status, namefield, fielddata' ))	 {
					$mark = $att->mark;
					$currstatus = $att->status;
					if ($minstatus > $currstatus) 	{
					    $minstatus = $currstatus;
					}

					$estimatemark = $mark;
			       	$estimates = get_records_select('monit_att_estimates', "criteriaid = $criteria->id" , '', 'id, name, mark, maxmark, typefield, namefield, printname');
			        foreach ($estimates as $key => $estimate)  {
			        	$estim_data = $estimate;
			           if (!empty($estim_data->typefield))	{
			               if ($estim_data->typefield == 'text_num') { // } || $estim_data->typefield == 'num')	{
			           		   $namefields = split(';', $estim_data->namefield);
			           		   // print_r($printnames); echo '<hr>'; print_r($namefields); echo '<hr>';
			           		   if ($estim_data->typefield == 'text_num')	$namefield = $namefields[1];
			           		   else $namefield = $estim_data->namefield;
						  	   if ($att_table = get_supplement_data($att))	{
						  	  	  if(!empty($att_table[$namefield]))	 {
						  	  	  	 $estimatemark =  $estimate->mark;
						  	  	  	 break;
						  	  	  }
					  	   	   }
			               } else if ($estim_data->typefield == 'num')	{
                                $estimatemark =  $estimate->mark;
			               }  
			           }
			        }
                    
                    // echo $criteria->name . ': ' . $estimatemark . '<br>';

				    if ($estimate = get_record('monit_att_estimates', 'criteriaid', $criteria->id, 'mark', $estimatemark))	{
				       $strestimate = '&raquo; ' . $estimate->name;
			           if (!empty($estimate->typefield))	{
	  						if ($att_dop_table = get_supplement_data($att))   {
	  							$strestimate .= ': ';
	                            $strempty = ' (пусто)';
	  							$comma = 0;
	  							foreach ($att_dop_table as $namefield => $value)	{
	  								if ($namefield == 'id' || $namefield == 'attid') continue;

	  								if (isset($value) && !empty($value))	{
	  									 if ($comma > 0) $strestimate .= '; ';
	  									 $strestimate .= $value;
	  									 $strempty = '';
	  									 $comma++;
	  								}
	  							}
	  							$strestimate .= $strempty;
	  						}
	  					}
				    }
				} else {
					$mark = '-';
					$currstatus = 1;
		            $minstatus = 1;
				}

				$strcolor = get_string('status'.$currstatus.'color', 'block_monitoring');

	            $num = $list_cid_id[$criteria->id];

                $links = array();

                if (($cyid == $lastcyid && $currstatus < 4))  { //  || $edit_capability_rayon
                        $links['edit'] = new stdClass();					   
			 			$links['edit']->title = get_string('editcriteria','block_mou_att');
				 		$links['edit']->url = "editcriteria.php?rid=$rid&oid=$oid&uid=$uid&cid={$criteria->id}&num=$num&stft=$stft&typeou=$typeou&cyid=$cyid";
				 		$links['edit']->pixpath = "{$CFG->pixpath}/i/edit.gif";
			 	}

 	
/*
				if ($currstatus != 1 && $currstatus < 4)  {
			 		$links['status4']->url = "changestatus.php?rid=$rid&sid=$sid&nm=$nm&yid=$yid&sn=$rkp&status=4&fid=";
			 		$links['status4']->title = get_string('sendtocoordination', 'block_monitoring');
			 		$links['status4']->pixpath = "{$CFG->pixpath}/s/yes.gif";
		        }
*/

			    $strlinkupdate = '';
			    foreach ($links as $key => $link)	{

					$strlinkupdate .= "<a title=\"$link->title\" href=\"$link->url\">";
					$strlinkupdate .= "<img src=\"{$link->pixpath}\" alt=\"$link->title\" /></a>&nbsp;";
			    }

				$filearea = "0/users/att/$rid/$uid/{$criteria->id}";
				$basedir = $CFG->dataroot . '/' . $filearea;
		        if ($files = get_directory_list($basedir)) {
		            $output = '';
		            foreach ($files as $key => $file) {
		                $icon = mimeinfo('icon', $file);
		                if ($CFG->slasharguments) {
		                    $ffurl = "$CFG->wwwroot/file.php/$filearea/$file";
		                } else {
		                    $ffurl = "$CFG->wwwroot/file.php?file=/$filearea/$file";
		                }
		
		                $output .=  '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
		                        '<a href="'.$ffurl.'" >'.$strudostovrdocs.'</a>';
		            }
		        } else {
		        	$output = '' ;
		        }

	 		    if ($strestimate != ' ')	{
  		 		    $strestimate = $criteria->name . '<p><b>' . $strestimate; // . '</b></p>';
	   	 		    if ($output != '')	{
	  		 		    $strestimate .= " ($output)";
		 		    } 	 		    
		 		    $strestimate .= '</b></p>';
	 		    } else 	{
  		 		    $strestimate = $criteria->name;
                    if ($output != '')  {
                        $strestimate .= "<p><b>&raquo; $output</b></p>";
		 		    } 	 		    
	 		    }
	 		    
	 		    
       			$table->data[] = array ($num, $strestimate, $mark, $strlinkupdate);
				$table->bgcolor[] = array ($strcolor);

				unset($links);
          }
          	
	} 
	return $table;
}


?>


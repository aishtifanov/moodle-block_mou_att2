<?php // $Id: attcriteria.php,v 1.5 2014/06/16 12:00:07 shtifanov Exp $

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

	$currenttab = 'attcriteria';
    include('tabs.php');

  	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_stafftype("attcriteria.php?stft=", $stft);
    echo '</table>'; 
	// echo $typeou;

    if ($stft != 0) {
         print_tabs_criteria("attcriteria.php?stft=$stft&yid=", $stft, $yid, true);
      	 
         $table =  table_attcriteria($stft, $yid);
         print_color_table($table);

         echo '<div align=center>';
         if ($edit_capability)  {  
		      $options = array('mode' => 'new', 'yid' => $yid,  'stft' => $stft, 'sesskey' => $USER->sesskey);
	          print_single_button("editrefcrit.php", $options, get_string('addcriteria','block_mou_att'));
         }      
         echo '</div>';
         
         // $strtotlamark = get_string('total_mark', 'block_mou_att'); 
        $stafftype = get_record_select('monit_att_stafftype', "id = $stft", 'id, name, att_result');
        print_simple_box_start('center', '50%', 'white');
        // print_heading($strtotlamark, 'center', 4);
        echo $stafftype->att_result; 
        print_simple_box_end();
         
    }

    print_footer();
    
    


function table_attcriteria($stafftypeif, $yid)
{
	global $CFG, $edit_capability, $curryear;
    
    $stryes = get_string('yes');
    $strno = get_string('no');
    
    $table = new stdClass();
	$table->head  = array (get_string('symbolnumber', 'block_monitoring'),	get_string('name', 'block_mou_school'), 
                           get_string('udostovrdocs', 'block_mou_att'),  get_string('description'),
                           get_string('action', 'block_mou_school'));
    $table->align = array ("left",  "left", "center", "center", "center");
    $table->class = 'moutable';
  	$table->width = '90%';
    $table->size = array ('5%', '70%', '10%', '20%', '10%');

	$strsql = "SELECT id, num, name, description, is_loadfile FROM {$CFG->prefix}monit_att_criteria
               WHERE yearid=$yid and stafftypeid=$stafftypeif
               ORDER BY num";

	if ($criterias = get_records_sql ($strsql))	{
			foreach ($criterias as $criteria) {
			     $strlinkupdate = '';
                /*
				$title = get_string('deleteitem','block_mou_att');
			    $strlinkupdate  = "<a title=\"$title\" href=\"deleteitem.php?part=st\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
                */
                $stryesno = ($criteria->is_loadfile == 1) ? $stryes : $strno;
                
                if ($estimates = get_records_select('monit_att_estimates', "criteriaid = $criteria->id", 'mark', 'id, criteriaid, name, mark, maxmark, typefield, namefield, printname'))    {
                    $html = '';
                    /*
                    $html = '<table border = 2>'; 
                    foreach ($estimates as $estimate)   {
                       $html .= '<tr><td>'.$estimate->name . '</td>';
                       $html .= '<td>'.$estimate->mark . '</td>';
                       $html .= '<td>'.$estimate->maxmark . '</td>';
                       $html .= '<td>'.$estimate->typefield . '</td>';
                       $html .= '<td>'.$estimate->namefield . '</td></tr>';
                    }
                    $html .= '</table>';
                    */
                    foreach ($estimates as $estimate)   {
                        $strb =	slovo_ballov($estimate->mark);
           	           if (isset($estimate->text_num))	{
        		 		   $text = "$estimate->name";
        		 	   } else {
        			 	   $text = "$estimate->name ($estimate->mark $strb)";
        		 	   }
                        $html .= "<input type=radio name=estimate value=\"".$estimate->mark."\" alt=\"".$text."\" />";
                        $html .= $text . '<br>';
            	       	   $estim_data = $estimate;
            	           if (!empty($estim_data->typefield))	{
            	               switch($estim_data->typefield)  {
            	               		case 'date':
            	               		   $printnames = split(';', $estim_data->printname);
            	               		   $namefields = split(';', $estim_data->namefield);
            	               		   $i=0;
            	               		   foreach ($printnames as $printname) {
            		               		  $namefield =	$namefields[$i];
            	     	        	      $html .= '<br>' . $printname.':&nbsp;';
            						 	  $html .="<INPUT maxLength=10 size=10 name=$namefield><br>";
            							  $i++;
            						   }
            	               		break;
            	               		case 'text':
            	               		   $printnames = split(';', $estim_data->printname);
            	               		   $namefields = split(';', $estim_data->namefield);
            	               		   $i=0;
            	               		   foreach ($printnames as $printname) {
            		               		  $namefield =	$namefields[$i];
            	     	        	      $html .= '<br>' . $printname.':&nbsp;';
            						 	  $html .= "<INPUT maxLength=500 size=100 name=$namefield><br>";
            							  $i++;
            						   }
            	               		break;
            	               		case 'text_num':
            	               		   $printnames = split(';', $estim_data->printname);
            	               		   $namefields = split(';', $estim_data->namefield);
            	               		   $i=0;
            	               		   $namefield =	$namefields[0];
            	    	        	   $html .= '<br>' . $printnames[0].':&nbsp;';
            						   $html .= "<INPUT maxLength=500 size=100 name=$namefield><br>";
                                       if (isset($namefields[1]))   {
            	               		      $namefield =	$namefields[1];
                                       } else {
                                          $namefield = '';	  
                                       }   
                                       if (isset($printnames[1]))   {
            	    	        	     $html .= '<br>' . $printnames[1].':&nbsp;';
                                       } else {
                                         $html .= '<br>:&nbsp;';
                                       }  
            					 	   $html .= "<INPUT maxLength=5 size=5 name=$namefield><br>";
            	               		break;
            	               		case 'num':
            	               		   $namefield =	$estim_data->namefield;
            	    	        	   $html .= '<br>' . $estim_data->printname .':&nbsp;';
            						   $html .= "<INPUT maxLength=5 size=5 name=$namefield><br>";
            	               		break;
            	               }
            	
            	           }
                    }    
                }
                
                
                $strname = '<b>'.$criteria->name . '</b><br>' . $html;
                
                if ($edit_capability && $yid >= $curryear)   { // !!!!!!!!!!!!!!!!!!!! Заменить $curryear+1 на $curryear В НОВОМ УЧЕБНОМ ГОДУ
                // if ($edit_capability && $yid == $curryear)   { 
               		$title = get_string('editcrit', 'block_mou_att');		
            		$strlinkupdate .= "<a title=\"$title\" href=\"editrefcrit.php?mode=edit&stft=$stafftypeif&yid=$yid&cid=$criteria->id\">";
            		$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";
                    $title = get_string('delcrit', 'block_mou_att');
    			    $strlinkupdate .= "<a title=\"$title\" href=\"delcrit.php?part=crit&id=$criteria->id&stft=$stafftypeif&yid=$yid\">";
    				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
                }    


				$table->data[] = array ($criteria->num.'.', $strname, $stryesno, $criteria->description, $strlinkupdate);
			}
	}

    return $table;
}	

?>
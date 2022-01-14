<?php // $Id: importcriteria.php,v 1.2 2012/08/20 05:35:13 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');
    //require_once('../lib_att.php');
	require_once($CFG->dirroot.'/lib/uploadlib.php');
    
    $yid = optional_param('yid', 6, PARAM_INT);  	  //
    $stft = optional_param('stft', 0, PARAM_INT);  	  // Type staff id
    $analys = optional_param('analys', ''); 
        
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

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

	$currenttab = 'importcriteria';
    include('tabs.php');
    
    $csv_delimiter = ';';
    
  	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_stafftype("importcriteria.php?stft=", $stft);
    echo '</table>'; 
 
    if ($stft != 0) {

    	if ($frm = data_submitted()) {
    		
    		$um = new upload_manager('userfile',false,false,null,false,0);
    		$f = 0;
    		if ($um->preprocess_files()) {
    		 // echo '111';
    			$filename = $um->files['userfile']['tmp_name'];
    
    			$text = file($filename);
    			if($text == FALSE){
    				error(get_string('errorfile', 'block_mou_att'), "importcriteria.php");
    			}
    			$size = sizeof($text);
    
    			$textlib = textlib_get_instance();
      			for($i=0; $i < $size; $i++)  {
    				$text[$i] = $textlib->convert($text[$i], 'win1251');
                }
                unset ($textlib);
     			$required = array( "number" => 1, "criteria" => 1,"description" => 1,"maxmark" => 1, 
                                    "ocenka1" => 1, "mark1" => 1, "type_ocenka1" => 1,
                                    "ocenka2" => 1, "mark2" => 1, "type_ocenka2" => 1,
                                    "ocenka3" => 1, "mark3" => 1, "type_ocenka3" => 1,
                                    "ocenka4" => 1, "mark4" => 1, "type_ocenka4" => 1,
                                    "ocenka5" => 1, "mark5" => 1, "type_ocenka5" => 1);
                // --- get and check header (field names) ---
                $header = split($csv_delimiter, $text[0]);
                translate_header_att($header);
                // check for valid field names
                foreach ($header as $i => $h) {
                  //  echo $i.'<br>';
                    $h = trim($h);
                    $header[$i] = $h;
                    if (!isset($required[$h])) {
                        //echo '111';
                        error(get_string('invalidfieldname', 'error', $h), "importcriteria.php");
                    }
                    
                    if (isset($required[$h])) {
                        $required[$h] = 0;
                    }
                }
                
                if ($analys != '') { 
                    echo '<table align=left border=1>';
                }
                    
      			for($i=1; $i < $size; $i++)  {
    	            $line = split($csv_delimiter, $text[$i]);
                    $record = array();
     	  	        foreach ($line as $key => $value) {
      	                $record[$header[$key]] = trim($value);
       	 	        }	 
                    unset($form1);
                    $record['criteria'] = str_replace(':', ':<br>', $record['criteria']);
                    $record['criteria'] = str_replace('#', ';<br>', $record['criteria']);
                    
                    $form1->yearid = $yid;
                    $form1->stafftypeid = $stft;
                    $form1->is_loadfile = 1;  
                                                         
    				if (!empty($record['number'])&& $record['number']!='-') 
                        $form1->num = $record['number'];
                        
    				if (!empty($record['criteria'])&& $record['criteria']!='-') 
                        $form1->name = $record['criteria'];
                        
    				if (!empty($record['description'])&& $record['description']!='-') 
                        $form1->description = $record['description'];
                        
                    if (!empty($record['maxmark'])&& $record['maxmark']!='-') 
                        $form1->maxmark = $record['maxmark'];
                    else $form1->maxmark = 0;    
                        
                  //  $form2->mark = 0;
                  	if ($analys == '') {
        				if (record_exists_select("monit_att_criteria", "stafftypeid = $stft and num = '{$form1->num}' and yearid = $yid")){
        					 notify(get_string('criteriaexists','block_mou_att', $form1->name). '!!!');
        				} else if($idform1 = insert_record("monit_att_criteria", $form1))	{
        				   
                           for ($e = 1; $e<=5; $e++)    {
                                insert_estimates($e, $idform1, $form1->maxmark);
                           }     
        
        				 	notify(get_string('recordadded','block_mou_att', $form1->name), 'green', 'center');
                        } 
                    } else {
                       // print_object($form1);

                       echo "<tr><td>$form1->num</td><td>$form1->name</td>";
                       for ($e = 1; $e<=5; $e++)    {
                            insert_estimates($e, 0, $form1->maxmark, true);
                       }
                       echo '</tr>';     
                    }    
      	         }
                 if ($analys != '') { 
                    echo '</table>';
                 }    
             } 
    		//	redirect("$CFG->wwwroot/blocks/meta/documents/viewdocuments.php?prid=$prid", get_string('completeimport', 'block_meta'), 15);
    	}  
    		 	
        $maxuploadsize = get_max_upload_file_size();
    	$strchoose = ''; // get_string("choose"). ':';
        $stranalys = 'Анализ файла импорта без загрузки критериев';
    
        echo '<center>';
        echo '<form method="post" enctype="multipart/form-data" action="importcriteria.php">'.
             $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
    	     '<input type="hidden" name="yid" value="'.$yid.'">'.
    	     '<input type="hidden" name="stft" value="'.$stft.'">'.
    		 '<input type="file" name="userfile" size="100">'.
             '<p><input type="submit" name="analys" value="'.$stranalys.'">'.
    		 '<input type="submit" name="load" value="'.get_string('importcriteria', 'block_mou_att').'">';
    	     '</form>';
    	echo '</center>';

    }
    print_footer();


function translate_header_att(&$header)
{
    $string_rus[] = 'Номер';
    $string_rus[] = 'Критерий';
    $string_rus[] = 'Описание';
    $string_rus[] = 'Максимальный балл';
    $string_rus[] = 'Оценка 1';
    $string_rus[] = 'Балл 1';
    $string_rus[] = 'Тип оценки 1';
    $string_rus[] = 'Оценка 2';
    $string_rus[] = 'Балл 2';
    $string_rus[] = 'Тип оценки 2';
    $string_rus[] = 'Оценка 3';
    $string_rus[] = 'Балл 3';
    $string_rus[] = 'Тип оценки 3';
    $string_rus[] = 'Оценка 4';
    $string_rus[] = 'Балл 4';
    $string_rus[] = 'Тип оценки 4';
    $string_rus[] = 'Оценка 5';
    $string_rus[] = 'Балл 5';
    $string_rus[] = 'Тип оценки 5';

    $string_lat[] = 'number';
    $string_lat[] = 'criteria';
    $string_lat[] = 'description';
    $string_lat[] = 'maxmark';
    $string_lat[] = 'ocenka1';
    $string_lat[] = 'mark1';
    $string_lat[] = 'type_ocenka1';
    $string_lat[] = 'ocenka2';
    $string_lat[] = 'mark2';
    $string_lat[] = 'type_ocenka2';
    $string_lat[] = 'ocenka3';
    $string_lat[] = 'mark3';
    $string_lat[] = 'type_ocenka3';
    $string_lat[] = 'ocenka4';
    $string_lat[] = 'mark4';
    $string_lat[] = 'type_ocenka4';
    $string_lat[] = 'ocenka5';
    $string_lat[] = 'mark5';
    $string_lat[] = 'type_ocenka5';

    foreach ($header as $i => $h) {
		$h = trim($h);
		$flag = true;
		foreach ($string_rus as $j => $strrus) {
       		if ($strrus == $h)  {
       			$header[$i] = $string_lat[$j];
				$flag = false;
       			break;
       		}
       	}
       	if ($flag)  {
			 error(get_string('errorinnamefield', 'block_mou_att', $header[$i]), "importcriteria.php");
       	}
    }
}


function insert_estimates($e, $idform1, $maxmark=0, $debug=false)
{
    global $record;
    
	if ($record['ocenka'.$e] != '-' &&  $record['ocenka'.$e] != '' ){

	    $form2->criteriaid = $idform1;
	    // $form2->name = $record['ocenka'.$e];
        $form2->name = str_replace('#', ';', $record['ocenka'.$e]);
        $form2->mark = $record['mark'.$e];         
        $form2->typefield = $record['type_ocenka'.$e];
        
        switch($form2->typefield)   {
            case '-':    $form2->typefield = '';
            break;                        
            case 'text': $form2->namefield = 'name'.$record['mark'.$e];
                         $form2->printname = '>>';
            break;
            case 'num':  $form2->namefield = 'number'.$record['mark'.$e];
                         $form2->printname = '>';
                         if ($maxmark != '-')   {
                            $form2->maxmark   = $maxmark;
                         }    
            break;
        }
        if ($debug) {
		      // print_object($form2);
              echo "<td>$form2->name</td><td>$form2->mark</td><td>$form2->typefield</td>";
        } else {
            if(record_exists_select("monit_att_estimates", "criteriaid = $idform1 and name = '{$form2->name}' and mark = {$form2->mark}")){
		      notify(get_string('record_exists'), 'green', 'left');
            } else    {
		      insert_record("monit_att_estimates", $form2);
          	}
        }       
	} 			
}    

?>
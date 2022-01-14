<?php // $Id: lib_local.php,v 1.6 2014/06/16 12:00:06 shtifanov Exp $


function table_attestation($rid, $oid, $uid, $typeou, $stft, $edutype, $cyid, $lastcyid, $what = 'shortinfo')
{
    global $CFG, $edit_capability_rayon, $edit_capability,$view_capability, $staff, $user;
            // $minstatus, ,$strmarkscriteria;

    $strudostovrdocs = get_string('udostovrdocs', 'block_mou_att');
    $table = new stdClass();
    if ($what == 'shortinfo')   {    
        $table->head  = array ('№', get_string('criteria', 'block_mou_att'), get_string('action', 'block_monitoring'));
    } else {
        $table->head  = array ('№', get_string('criteria', 'block_mou_att'), get_string('mark', 'block_mou_att'));
    }    
    
    $table->align = array ('right', 'left', 'center');
    $table->class = 'moutable';
    $table->columnwidth = array (2, 75, 15);
    $table->width = '70%'; 
   	$table->titles = array();
   	
    /*
   	$table->titles[] = $strmarkscriteria;// get_string('marks_'.$category, 'block_mou_att');
   	$table->titles[] = fullname($user) . ', ' . $staff->appointment;
   	$table->titlesrows = array(30, 30);
    $table->downloadfilename = 'attestation_'.$oid.'_'.$uid;
	$table->worksheetname = $table->downloadfilename;
    */
    
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
            
                if ($what == 'expertzakl')   {
                    $criteria->name = str_ireplace('<br>', ' ', $criteria->name);
                }     
            
				$links = array();
                $strestimate = ' ';

			    $strlinkupdate = "<input type=checkbox size=1 name=\"a_{$criteria->id}\" value='ok'>";
                                
                $strselect = "staffid = $staff->id AND stafftypeid = $stft AND criteriaid = $criteria->id";

				if ($att = get_record_select('monit_att_attestation', $strselect, 'id, mark, status, namefield, fielddata, isprint' ))	 {
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
				       $strb = slovo_ballov($estimate->mark);
				       $strestimate = '&raquo; ' .  $estimate->name;
                       $strb = $estimate->mark . ' '.$strb; 
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
                                if ($what == 'shortinfo')   {
                                    $strestimate .= ' (' . $strb . ')';
                                }    
	  						}
	  					}
				    }
                    
                    if ($att->isprint)  {
 			            $strlinkupdate = "<input type=checkbox checked size=1 name=\"a_{$criteria->id}\" value='ok'>";
                    }    
				} else {
					$mark = '-';
					$currstatus = 1;
		            $minstatus = 1;
				}

				$strcolor = get_string('status'.$currstatus.'color', 'block_monitoring');

	            $num = $list_cid_id[$criteria->id];

                $output = '' ;
    /*
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
*/
                    
	 		    if ($strestimate != ' ')	{
  		 		    $strestimate = '<b>* ' . $criteria->name . '</b><br>' . $strestimate; // . '</b></p>';
	   	 		    if ($output != '')	{
	  		 		    $strestimate .= " ($output)";
		 		    } 	 		    
		 		    // $strestimate .= '</p>';
	 		    } else 	{
  		 		    $strestimate = $criteria->name;
	 		    }
                
                
	 		    
	 		    if ($what == 'expertzakl')   {
		            $table->data[] = array ($num.'.', $strestimate, $mark);
                } else {
                    $table->data['a_'.$criteria->id] = array ($num.'.', $strestimate, $strlinkupdate);  
                }  
				// $table->bgcolor[] = array ($strcolor);

				unset($links);
          }
          	
	} 
    
    if ($what == 'expertzakl')   {
        
    	$strsql = "SELECT Sum(a.mark) AS sum
			   FROM mdl_monit_att_staff as s INNER JOIN mdl_monit_att_attestation as a ON s.id = a.staffid
			   WHERE s.userid = $uid AND a.yearid=$cyid AND a.stafftypeid=$stft";
 	    $table->sum = '-';
        if ($rec = get_record_sql($strsql))  {
    		$table->sum = $rec->sum;
    	}
    }
        
	return $table;
}


function dativecase($lastname, $firstname, $secondname, $sex=-1) 
{
    global $user;
    
	$lastname = trim($lastname);
	$firstname = trim($firstname);
	$secondname = trim($secondname);

	if (!empty($lastname) && !empty($firstname) && !empty($secondname)) {
		if($sex == -1) {
			$user->sex = 0;
			if (mb_substr($secondname, -1, 1, 'UTF-8') == 'ч')	{
				$user->sex = 1;
			}
		}
		if ($user->sex == 1)	{
# Склонение фамилии мужчины:
			switch (mb_substr($lastname, -2, 2, 'UTF-8'))	{
				case 'ха':
					$lastname = mb_substr($lastname, 0, -2, 'UTF-8').'хи';
				break;
				default:
					switch (mb_substr($lastname, -1, 1, 'UTF-8')) {
						case 'е': case 'о': case 'и': case 'я': case 'а':
						break;
						case 'й':
							$lastname = mb_substr($lastname, 0, -1, 'UTF-8').'ому';
						break;
						case 'ь':
							$lastname = mb_substr($lastname, 0, -1, 'UTF-8').'ю';
						break;
						default:
							$lastname = $lastname.'у';
						break;
					}
				break;
			}

# Склонение мужского имени:
			switch (mb_substr($firstname, -1, 1, 'UTF-8')) {
				case 'л':
					$firstname = mb_substr($firstname, 0, -1, 'UTF-8').'лу';
				break;
				case 'а': case 'я':
					if (mb_substr($firstname, -2, 1, 'UTF-8') == 'и') {
						$firstname = mb_substr($firstname, 0, -1, 'UTF-8').'и';
					} else {
						$firstname = mb_substr($firstname, 0, -1, 'UTF-8').'е';
					}
				break;
				case 'й': case 'ь':
					$firstname = mb_substr($firstname, 0, -1, 'UTF-8').'ю';
				break;
				default:
					$firstname = $firstname.'у';
				break;
			}
# Склонение отчества
			$secondname = $secondname.'у';
		} else {
# Склоенение женской фамилии
			switch (mb_substr($lastname, -1, 1, 'UTF-8'))	{
				case 'о': case 'и': case 'б': case 'в': case 'г':
				case 'д': case 'ж': case 'з': case 'к': case 'л':
				case 'м': case 'н': case 'п': case 'р': case 'с':
				case 'т': case 'ф': case 'х': case 'ц': case 'ч':
				case 'ш': case 'щ': case 'ь':
				break;
				case 'я':
					$lastname = mb_substr($lastname, 0, -1, 'UTF-8').'ой';
				default:
					$lastname = mb_substr($lastname, 0, -1, 'UTF-8').'ой';
				break;
			}
# Склонение женского имени:
			switch (mb_substr($firstname, -1, 1, 'UTF-8')) {
				case 'а': case 'я':
					if (mb_substr($firstname, -2, 1, 'UTF-8') == 'и') {
						$firstname = mb_substr($firstname, 0, -1, 'UTF-8').'и';
					} else {
						$firstname = mb_substr($firstname, 0, -1, 'UTF-8').'е';
					}
				break;
				case 'ь':
					$firstname = mb_substr($firstname, 0, -1, 'UTF-8').'и';
				break;
			}
# Склонение женского отчества
			$secondname = mb_substr($secondname, 0, -1, 'UTF-8').'е';
		}
		return "$lastname $firstname $secondname";
	}
}
     
function roditelcase($lastname, $firstname, $secondname) 
{
    global $user;
    
	$lastname = trim($lastname);
	$firstname = trim($firstname);
	$secondname = trim($secondname);
    
    $roditelname = "$lastname $firstname $secondname";

	if (!empty($lastname) && !empty($firstname) && !empty($secondname)) {
     
        $textlib = textlib_get_instance();
        $ln = $textlib->convert($lastname, 'utf-8', 'win1251');
        $fn = $textlib->convert($firstname, 'utf-8', 'win1251');
        $sn = $textlib->convert($secondname, 'utf-8', 'win1251');      
    
        $a = new RussianNameProcessor($ln, $fn, $sn);      // годится обычная форма
        // echo $a->lastName($a->gcaseRod);
        // echo '<pre>'; print_r($a); echo '</pre>'; 
        $roditelname = $a->fullName($a->gcaseRod);
    
        $roditelname = $textlib->convert($roditelname, 'win1251');
   
        unset ($textlib);
   }
   
   return $roditelname;     
} 

/*
function slovo_ballov($count)
{
	switch ($count%10) {
		case '1': return 'балл'; 
		break;
		
		case '2': 
		case '3': 
		case '4': return 'балла'; 
		break;
		
		case '0': 		
		case '5': 
		case '6': 
		case '7': 
		case '8': 
		case '9': return 'баллов'; 
		break;
	}
}
*/    
     
?>
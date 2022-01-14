<?php // $Id: lib_att2.php,v 1.25 2014/06/16 12:00:07 shtifanov Exp $

/*  SQL!!!
SELECT * FROM mdl_monit_att_attestation where yearid=6 and criteriaid < 1510;
update  mdl_monit_att_attestation set yearid=3 where criteriaid >= 1001 and criteriaid <=1024;

*/

define("MAX_SCAN_COPY_SIZE", 3145728); //1048576 2097152 3145728 4194304 8388608);

function listbox_rayons_att($scriptname, &$rid)
{
	global $CFG, $USER;//, $admin_is, $region_operator_is;

	$ret = false;	
  	$listrayons = '';

	 $strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path  
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	 // echo $strsql . '<hr>';
	 if ($ctxs = get_records_sql($strsql))	{
	 		// echo '<pre>'; print_r($ctxs); echo '</pre>'; 
			foreach($ctxs as $ctx1)	{
				switch ($ctx1->contextlevel)	{
					case CONTEXT_SYSTEM: if ($ctx1->roleid == 1)	{ 
											$listrayons = -1;
										 }
										 break;
					case CONTEXT_REGION_ATT:					 					 	
										 if ($ctx1->roleid == 8 || $ctx1->roleid == 10)	{
										 	$listrayons = -1;
										 }
										 break;
					case CONTEXT_RAYON_COLLEGE:
					case CONTEXT_RAYON_UDOD:
					case CONTEXT_RAYON_DOU:					 		
    				case CONTEXT_RAYON:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10 || $ctx1->roleid == 18) {
    										$listrayons .= 	$ctx1->instanceid . ',';
										 }
								 		 break;
					case CONTEXT_SCHOOL: 
					case CONTEXT_COLLEGE:
					case CONTEXT_UDOD:
					case CONTEXT_DOU:   $contexts = explode('/', $ctx1->path);
										// print_r($contexts);
										$ctxrayon = get_record('context', 'id', $contexts[3]);
										$listrayons .= $ctxrayon->instanceid . ',';
					break;
					case CONTEXT_QUEUE_SCHOOL: 
					case CONTEXT_QUEUE_UDOD:
					case CONTEXT_QUEUE_DOU:   $contexts = explode('/', $ctx1->path);
										// print_r($contexts);
										$ctxrayon = get_record('context', 'id', $contexts[3]);
										$listrayons .= $ctxrayon->instanceid . ',';
					break;
                    default:
                        if ($ctx1->contextlevel > 1000) {
                            // notify( get_string('notdefinedcontext', 'block_mou_att', $ctx1->contextlevel));
                            // debugging(get_string('notdefinedcontext', 'block_mou_att', $ctx1->contextlevel));
                        }     
	 			}
	 			
	 			if 	($listrayons == -1) break;
			}
	 }		 

     // echo $listrayons;
	 if ($listrayons == '') 	{
	 	return false;
	 } else if 	($listrayons == -1) 	{
	 	$strsql = "SELECT id, name FROM {$CFG->prefix}monit_rayon ORDER BY number";
	 } else {	
	 	$listrayons .= '0';
	 	$strsql = "SELECT id, name FROM {$CFG->prefix}monit_rayon WHERE id in ($listrayons)ORDER BY number";
	 }
 	
 	
	// echo $strsql . '<hr>';
  	if($allrayons = get_records_sql($strsql))   {
  		// print_r($allrayons);
  		if (count($allrayons) > 1) {
  		    $rayonmenu = array();
  			$rayonmenu[0] = get_string('selectarayon', 'block_monitoring').'...';
	 		 foreach ($allrayons as $rayon) 	{
	 		    // $rid = $rayon->id;
	      		$rayonmenu[$rayon->id] = $rayon->name;
	  	 	 }
		    $ret =  '<tr> <td>'.get_string('rayon', 'block_monitoring').': </td><td>';
		    $ret .= popup_form($scriptname, $rayonmenu, 'switchrayon', $rid, '', '', '', true);
		  	$ret .= '</td></tr>';
	  	 	 
  		} else {
  			$rayon = current($allrayons);
  			// $rayonmenu[$rayon->id] = $rayon->name;
  			$rid = $rayon->id;
  			$ret =  '<tr> <td>'.get_string('rayon', 'block_monitoring').': </td><td>';
		    $ret .= "<b>".$rayon->name."</b>";
		  	$ret .= '</td></tr>';
  		}
  	}
   	
	return $ret;
}


function listbox_typeou_att($scriptname, &$rid, &$typeou, $isqueue=false, $isspo=false)
{
	global $CFG, $yid, $USER;//, $admin_is, $region_operator_is;


    if ($isqueue) {
        $listedutypeids = 'AND id in (18, 1, 17, 15, 16)';
    } else if ($isspo) {
        $listedutypeids = 'AND id in (6, 7, 13, 14)';
    } else {
        $listedutypeids = '';
    }
    
	$ret = false;
    $is_high_context = false;	
  	$listtypeou = "'-'";
    $listous = array ('monit_school' => '', 'monit_college' => '', 'monit_udod' => '', 'monit_education' => '');

	$strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path  
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	//  echo $strsql . '<hr>';
	 if ($ctxs = get_records_sql($strsql))	{
	 		// print_r($ctxs); echo '<hr>';
			foreach($ctxs as $ctx1)	{
				switch ($ctx1->contextlevel)	{
					case CONTEXT_SYSTEM: if ($ctx1->roleid == 1)	{ 
											$listtypeou = -1;
                                            $is_high_context = true;                                            
										 }
										 break;
					case CONTEXT_REGION_ATT:					 					 	
										 if ($ctx1->roleid == 8 || $ctx1->roleid == 10)	{
										 	$listtypeou = -1;
                                            $is_high_context = true;                                            
										 }
										 break;
					case CONTEXT_RAYON_COLLEGE:
					case CONTEXT_RAYON_UDOD:
					case CONTEXT_RAYON_DOU:					 		
    				case CONTEXT_RAYON:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10 || $ctx1->roleid == 18 ) {
    										// $listtypeou .= 	',' . $ctx1->instanceid;
                                            $listtypeou = -1;
                                            $is_high_context = true;                                            
										 }
								 		 break;
					case CONTEXT_SCHOOL: $listtypeou .=	",'monit_school'";
                                         $listous['monit_school'] .= $ctx1->instanceid . ',';
										 break;
					case CONTEXT_COLLEGE:$listtypeou .=	",'monit_college'";
                                         $listous['monit_college'] .= $ctx1->instanceid . ',';
										 break;
					case CONTEXT_UDOD:   $listtypeou .=	",'monit_udod'";
                                         $listous['monit_udod'] .= $ctx1->instanceid . ',';
										 break;
					case CONTEXT_DOU:    $listtypeou .=	",'monit_education'";
                                         $listous['monit_education'] .= $ctx1->instanceid . ',';                    
										 break;   
					case CONTEXT_QUEUE_SCHOOL: $listtypeou .=	",'monit_school'";
                                         $contexts = explode('/', $ctx1->path);
										 $ctxou = get_record('context', 'id', $contexts[4]);
                                         $listous['monit_school'] .= $ctxou->instanceid . ',';
										 break;                    
					case CONTEXT_QUEUE_UDOD: $listtypeou .=	",'monit_udod'";
                                         $contexts = explode('/', $ctx1->path);
										 $ctxou = get_record('context', 'id', $contexts[4]);
                                         $listous['monit_udod'] .= $ctxou->instanceid . ',';
										 break;                                         
					case CONTEXT_QUEUE_DOU:   $listtypeou .=	",'monit_education'";
                                         $contexts = explode('/', $ctx1->path);
										 $ctxou = get_record('context', 'id', $contexts[4]);
                                         $listous['monit_education'] .= $ctxou->instanceid . ',';
										 break;                                         
                    default:
                        if ($ctx1->contextlevel > 1000) {
                            // debugging(get_string('notdefinedcontext', 'block_mou_att', $ctx1->contextlevel));
                            // notify( get_string('notdefinedcontext', 'block_mou_att', $ctx1->contextlevel));
                        }     
	 			}
	 			
	 			if 	($listtypeou == -1) break;
			}
	 }		 


	 // echo $listtypeou . '<hr>';

	 if ($listtypeou == "'-'") 	{
	 	return false;
	 } else if 	($listtypeou == -1) 	{
	 	$strsql = "SELECT id, name, cod, tblname FROM {$CFG->prefix}monit_school_type WHERE is_att_type = 1 $listedutypeids ORDER BY id";
	 } else {	
	 	$strsql = "SELECT id, name, cod, tblname FROM {$CFG->prefix}monit_school_type WHERE is_att_type = 1 AND tblname in ($listtypeou)ORDER BY id";
	 }
 	
 	$typeoumenu = array();
	
	// echo $strsql . '<hr>';
    
  	
 	if($alltypeou = get_records_sql($strsql))   {

		// echo '<pre>'; print_r($alltypeou); echo '</pre>';
  		if (count($alltypeou) > 1 && $is_high_context) {
  		    // echo '<hr>'. $listous . '<hr>';
  			$typeoumenu['-'] = get_string('selecttypeou', 'block_mou_att').'...';
	 		 foreach ($alltypeou as $typeou1) 	{
	      		$typeoumenu[$typeou1->cod] = $typeou1->name;
	  	 	 }
		    $ret =  '<tr> <td>'.get_string('typeou', 'block_mou_att').': </td><td>';
		    $ret .= popup_form($scriptname, $typeoumenu, 'switchtypeou', $typeou, '', '', '', true);
		  	$ret .= '</td></tr>';

  		} else {
  		    
            // echo '<pre>'; print_r($listous); echo '<pre>';
            $typeoumenu['-'] = get_string('selecttypeou', 'block_mou_att').'...';
            foreach ($listous  as $tblname => $listou)  {
                if ($listous == '') continue;
                set_typeoumenu($alltypeou, $tblname, $listou, $typeoumenu); 	
            } 
            
            switch (count($typeoumenu)) {
                case 1:
                   		$typeou1 = current($alltypeou);
                		$ret =  '<tr> <td>'.get_string('typeou', 'block_mou_att').': </td><td>';
                	    $ret .= "<b>".$typeou1->name."</b>";
                	  	$ret .= '</td></tr>';
                	  	$typeou = $typeou1->cod;
                break;
                
                case 2: foreach ($typeoumenu as $cod => $name)  {
                            if ($cod == '-') continue;
                  			$ret =  '<tr> <td>'.get_string('typeou', 'block_mou_att').': </td><td>';
                		    $ret .= "<b>".$name."</b>";
                		  	$ret .= '</td></tr>';
                		  	$typeou = $cod;
                            break;
                        } 
                break;
                
                default:
               		    $ret =  '<tr> <td>'.get_string('typeou', 'block_mou_att').': </td><td>';
               		    $ret .= popup_form($scriptname, $typeoumenu, 'switchtypeou', $typeou, '', '', '', true);
               		  	$ret .= '</td></tr>';
            } 
       }   
  	}

	return $ret;
}



function set_typeoumenu($alltypeou, $tblname, $listou, &$typeoumenu)
{
    global $CFG, $rid, $yid;
    
    $listou .= '0';    
 	$strsql = "SELECT id, uniqueconstcode, name, typeinstitution FROM {$CFG->prefix}$tblname
    		   WHERE id in ($listou)";
     // echo $strsql . '<hr>';
     // echo $listous . '<hr>';
     $ucc = array();             
     if ($schools =  get_records_sql($strsql))	{
        foreach ($schools as $school)   {
            $ucc[] = $school->uniqueconstcode; 
        }
     }   
     $ucc = array_unique($ucc);
     // var_dump($ucc);
     
    $arrtypeou = array();
    foreach ($ucc as $uccou)   {
        $ou = get_record_select ($tblname, "uniqueconstcode=$uccou AND yearid=$yid", 'id, rayonid, typeinstitution');
        if ($ou->rayonid != $rid) continue;
        $arrtypeou[] = $ou->typeinstitution; 
    }    
    $arrtypeou = array_unique($arrtypeou);
    // var_dump($arrtypeou);

    foreach ($arrtypeou as $atypeou) 	{
        $typeoumenu[$alltypeou[$atypeou]->cod] = $alltypeou[$atypeou]->name;
    }
}    


function listbox_ou_att($scriptname, &$rid, &$typeou, &$oid, $yid, $cluster=0)
{
	global $CFG, $USER;

	$ret = false;
  
 	if ($rid == 0)  return false;
   
  	$listous = '';
   	
  	// $outype = get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
    $outype = get_config_typeou($typeou);
	  
	$strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path 
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	// echo $strsql . '<hr>';
	if ($ctxs = get_records_sql($strsql))	{
	 		// echo '<pre>'; print_r($ctxs);  echo '</pre><hr>';
			foreach($ctxs as $ctx1)	{
				switch ($ctx1->contextlevel)	{
					case CONTEXT_SYSTEM: if ($ctx1->roleid == 1)	{ 
											$listous = -1;
										 }
										 break;	
										 			
					case CONTEXT_REGION_ATT:					 					 	
										 if ($ctx1->roleid == 8 || $ctx1->roleid == 10)	{
										 	$listous = -1;
										 }
										 break;
					case CONTEXT_RAYON_COLLEGE:
					case CONTEXT_RAYON_UDOD:
					case CONTEXT_RAYON_DOU:					 		
    				case CONTEXT_RAYON:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10 || $ctx1->roleid == 18) {
    										$listous = -1;
										 }
								 		 break;
					case CONTEXT_QUEUE_SCHOOL: 
                                         $contexts = explode('/', $ctx1->path);
										 $ctxou = get_record('context', 'id', $contexts[4]);
                                         $listous .= $ctxou->instanceid . ',';
										 break;                    
					case CONTEXT_QUEUE_UDOD: 
                                         $contexts = explode('/', $ctx1->path);
										 $ctxou = get_record('context', 'id', $contexts[4]);
                                         $listous .= $ctxou->instanceid . ',';
					case CONTEXT_QUEUE_DOU:   
                                         $contexts = explode('/', $ctx1->path);
										 $ctxou = get_record('context', 'id', $contexts[4]);
                                         $listous .= $ctxou->instanceid . ',';
                    default:
                        // notify( get_string('notdefinedcontext', 'block_mou_att', $ctx1->contextlevel)); 
	 			}
	 			
	 			if 	($listous == -1) break;
	 			
	 			if ($ctx1->contextlevel == $outype->context) {
	 				$listous .= $ctx1->instanceid . ',';
	 			}	
			}
	 }
     $selectcluster = '';		  
	 if ($typeou == '03' && $cluster > 0) {
	    $selectcluster = ' AND clusterdou =' . $cluster;
	 }
	 // echo $listous . '<hr>';
	 if ($listous == '') 	{
	 	return false;
	 } else if 	($listous == -1) 	{
	 	$strsql = "SELECT id, rayonid, name  FROM {$CFG->prefix}{$outype->tblname}
					WHERE rayonid = $rid AND isclosing=0 AND yearid=$yid AND typeinstitution=$outype->id {$outype->where} $selectcluster
 					ORDER BY number";
	 } else {	
	 	$listous .= '0';
	 	$strsql = "SELECT id, rayonid, name FROM {$CFG->prefix}{$outype->tblname}
		 			 WHERE rayonid = $rid AND isclosing=0 AND yearid=$yid AND typeinstitution=$outype->id AND id in ($listous) {$outype->where}
   					 ORDER BY number";
	 }
 
 

	$schoolmenu = array();
	// echo $strsql . '<hr>';
    if ($arr_schools =  get_records_sql($strsql))	{
    	if (count($arr_schools) > 1) {
	   		$schoolmenu[0] = $outype->strselect;
	  		foreach ($arr_schools as $school) {
				$len = strlen ($school->name);
				if ($len > 300)  {
					// $school->name = substr($school->name, 0, 200) . ' ...';
					$school->name = substr($school->name,0,strrpos(substr($school->name,0, 310),' ')) . ' ...';
				}
				$schoolmenu[$school->id] =$school->name;
			}
		 	$ret =  '<tr><td>'.$outype->strtitle.':</td><td>';
  			$ret .=  popup_form($scriptname, $schoolmenu, 'switchou', $oid, '', '', '', true);
  			$ret .= '</td></tr>';
		} else {
  			$school = current($arr_schools);
  			// $schoolmenu[$school->id] = $school->name;
  			$oid = $school->id;
  			$rid = $school->rayonid;
		 	$ret =  '<tr><td>'.$outype->strtitle.':</td><td>';
  			$ret .=  "<b>$school->name</b>";
  			$ret .= '</td></tr>';
		} 
  	} else {
  	    $schoolmenu[0] = get_string('selectou', 'block_mou_att').' ...';;
	 	$ret =  '<tr><td>'.$outype->strtitle.':</td><td>';
		$ret .=  popup_form($scriptname, $schoolmenu, 'switchou', $oid, '', '', '', true);
		$ret .= '</td></tr>';
  		// $ret = false;
  	}
	
	  
  return $ret;
}


function get_constants_ou($typeou, &$CONTEXT_OU, &$tablename, &$strtitle, &$strselect, &$where)
{
    
    $outype = get_record_select('monit_school_type', "cod = '$typeou'", 'id, name, cod, tblname, idfieldname');
  	$CONTEXT_OU = CONTEXT_SCHOOL;
  	$strselect = get_string('selectaschool','block_monitoring').' ...';
	$strtitle = get_string('school', 'block_monitoring');  	
  	$tablename = $outype->tblname;
  	$where = '';
  	switch ($tablename)	{
  		case 'monit_school': $CONTEXT_OU = CONTEXT_SCHOOL;
  					$tablename = 'monit_school';
  					$strselect = get_string('selectaschool','block_monitoring').' ...';
  					$strtitle = get_string('school', 'block_monitoring');
  		break;
  		case 'monit_college': $CONTEXT_OU = CONTEXT_COLLEGE;
  					$tablename = 'monit_college'; 
					$strselect = get_string('selectacollege','block_monitoring').' ...';
					$strtitle = get_string('college', 'block_monitoring'); 		
  		break;
  		case 'monit_udod': $CONTEXT_OU = CONTEXT_UDOD;
					$tablename = 'monit_udod';
					$strselect = get_string('selectaudod','block_monitoring').' ...';
					$strtitle = get_string('udod', 'block_monitoring');
  		break;
  		case 'monit_education': $CONTEXT_OU = CONTEXT_DOU;
					$tablename = 'monit_education';  		
					$strselect = get_string('selectadou','block_mou_att').' ...';
					$strtitle = get_string('dou', 'block_mou_att');
					$where = 'AND typeeducation=1';					
  		break;
  	}	
    
    return $outype;
} 		


function get_config_typeou($typeou)
{
    
    $outype = get_record_select('monit_school_type', "cod = '$typeou'", 'id, name, cod, tblname, category');
  	$outype->context = CONTEXT_SCHOOL;
    $outype->contextqueue = CONTEXT_QUEUE_SCHOOL;
  	$outype->strselect = get_string('selectaschool','block_monitoring').' ...';
	$outype->strtitle = get_string('school', 'block_monitoring');  	
  	$outype->where = '';
  	switch ($outype->tblname)	{
  		case 'monit_school': 
                    $outype->context = CONTEXT_SCHOOL;
                    $outype->contextqueue = CONTEXT_QUEUE_SCHOOL;
  					$outype->strselect = get_string('selectaschool','block_monitoring').' ...';
  					$outype->strtitle = get_string('school', 'block_monitoring');
                    $outype->idname = 'schoolid';
  		break;
  		case 'monit_college': 
                    $outype->context = CONTEXT_COLLEGE;
                    $outype->contextqueue = CONTEXT_COLLEGE;
					$outype->strselect = get_string('selectacollege','block_monitoring').' ...';
					$outype->strtitle = get_string('college', 'block_monitoring');
                    $outype->idname = 'collegeid'; 		
  		break;
  		case 'monit_udod': 
                    $outype->context = CONTEXT_UDOD;
                    $outype->contextqueue = CONTEXT_QUEUE_UDOD;
					$outype->strselect = get_string('selectaudod','block_monitoring').' ...';
					$outype->strtitle = get_string('udod', 'block_monitoring');
                    $outype->idname = 'udodid';
  		break;
  		case 'monit_education': 
                    $outype->context = CONTEXT_DOU;
                    $outype->contextqueue = CONTEXT_QUEUE_DOU;
					$outype->strselect = get_string('selectadou','block_mou_att').' ...';
					$outype->strtitle = get_string('dou', 'block_mou_att');
					$outype->where = 'AND typeeducation=1';
                    $outype->idname = 'douid';					
  		break;
  	}	
    
    return $outype;
} 		


function get_constants_rayon($typeou, &$CONTEXT_RAYON, &$tablename)
{
    $outype = get_record_select('monit_school_type', "cod = $typeou", 'id, name, cod, tblname');
  	$CONTEXT_RAYON = CONTEXT_RAYON;
  	$tablename = $outype->tblname;
  	switch ($tablename)	{
  		case 'monit_school': $CONTEXT_RAYON = CONTEXT_RAYON;
  		break;
  		case 'monit_college': // $CONTEXT_RAYON = CONTEXT_RAYON_COLLEGE;
                             $CONTEXT_RAYON = CONTEXT_RAYON;
  		break;
  		case 'monit_udod':   $CONTEXT_RAYON = CONTEXT_RAYON_UDOD;
 		break;
  		case 'monit_education': $CONTEXT_RAYON = CONTEXT_RAYON_DOU;
  		break;
  	}	
} 		


// Display list staff type as popup_form
/*
function listbox_stafftype($scriptname, $stft, $edutypeid = 0)
{
  global $CFG;

  if ($edutypeid == 0) {
      $stftmenu = array();
      $stftmenu[0] = get_string('selectastft', 'block_mou_att').' ...';

        $strsql = "SELECT @i:=@i+1 as id, aas.id as mainid, aas.name, mst.name as outypename, mst.id as outypeid 
                    FROM (select @i:=0) as z, mdl_monit_att_stst stst INNER JOIN mdl_monit_att_stafftype aas ON stst.stafftypeid=aas.id 
                    inner join mdl_monit_school_type mst on stst.edutypeid=mst.id 
                    where mst.name <> '...' order by mst.id, aas.name";
        // print $strsql . '<br>'; 
        // $strsql = "SELECT id, name  FROM {$CFG->prefix}monit_att_stafftype ORDER BY id";
        if ($arr_stfts =  get_records_sql($strsql))	{
            // print_object($arr_stfts);
                $outypeid = 0; 
          		foreach ($arr_stfts as $astf) 	{
          		    if ($outypeid <> $astf->outypeid)   {
          		        $stftmenu[$astf->outypeid*100] = '--'.$astf->outypename;
                        $outypeid = $astf->outypeid;
          		    } else {
          		        // $stftmenu[$astf->mainid] = $astf->name;
                        $stftmenu[$astf->mainid] =  " $astf->name ($astf->mainid)"; 
                    }    
        		}
        }
        // print_object($stftmenu);
  } else  {
          $stftmenu = array();
          $stftmenu[-1] = get_string('allstafftype', 'block_mou_att');
          $stftmenu[0] = get_string('selectastft', 'block_mou_att').' ...';

          $strsql = "SELECT b.id, b.name FROM {$CFG->prefix}monit_att_stst a INNER JOIN {$CFG->prefix}monit_att_stafftype b ON a.stafftypeid=b.id
                   WHERE edutypeid=$edutypeid order by name";
          if ($arr_stfts =  get_records_sql($strsql))	{
          		foreach ($arr_stfts as $astf) 	{
          		    $stftmenu[$astf->id] = $astf->name;
          		    if ($edutypeid == 0) {
        			   $stftmenu[$astf->id] =  $astf->id . '. ' . $stftmenu[$astf->id]; 
                    }     
        		}
          }
                   
  }

  echo '<tr><td>'.get_string('staffapointment', 'block_mou_att').':</td><td>';
  popup_form($scriptname, $stftmenu, 'switchstft', $stft, '', '', '', false);
  echo '</td></tr>';
  return 1;
}
*/

function listbox_stafftype($scriptname, $stft, $edutypeid = 0)
{
  global $CFG;

  $stftmenu = array();
  $stftmenu[-1] = get_string('allstafftype', 'block_mou_att');
  $stftmenu[0] = get_string('selectastft', 'block_mou_att').' ...';

  if ($edutypeid == 0) {
        $strsql = "SELECT id, name  FROM {$CFG->prefix}monit_att_stafftype ORDER BY id";
  } else  {
        $strsql = "SELECT b.id, b.name FROM {$CFG->prefix}monit_att_stst a INNER JOIN {$CFG->prefix}monit_att_stafftype b ON a.stafftypeid=b.id
                   WHERE edutypeid=$edutypeid order by name";
  }
  if ($arr_stfts =  get_records_sql($strsql))	{
  		foreach ($arr_stfts as $astf) 	{
  		    $stftmenu[$astf->id] = $astf->name;
  		    if ($edutypeid == 0) {
			   $stftmenu[$astf->id] =  $astf->id . '. ' . $stftmenu[$astf->id]; 
            }     
		}
  }

  echo '<tr><td>'.get_string('staffapointment', 'block_mou_att').':</td><td>';
  popup_form($scriptname, $stftmenu, 'switchstft', $stft, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


function print_tabs_criteria($scriptname, $stafftypeif, &$yid, $isaddnewyear = false)
{
    global $CFG;
        
    $strcriteria = get_string ('criterions', 'block_mou_att');    
    // id, yearid, stafftypeid, name, num, description, is_loadfile    
    $strsql = "SELECT distinct yearid FROM {$CFG->prefix}monit_att_criteria
                WHERE stafftypeid=$stafftypeif
			    ORDER BY yearid";
    if ($years = get_records_sql($strsql))  {
        $allyearids = array();
        $toprow = array();
        foreach ($years as $year)   {
            if ($yearname = get_record_select ('monit_years', "id = $year->yearid", 'id, name'))    {
                $toprow[] = new tabobject($year->yearid, $scriptname.  $year->yearid, $strcriteria . ' ' . $yearname->name);
                $allyearids[] = $year->yearid;
            } else {
                // $toprow[] = new tabobject(6, $scriptname.  6, $strcriteria . ' 2012/2013');
                // $allyearids[] = 6;
            }
        }
        
        if ($isaddnewyear)  {
            $lastyid = get_field_sql('SELECT max(id) as maxid FROM mdl_monit_years');
            $twoyears = get_field_sql("SELECT name FROM mdl_monit_years where id=$lastyid");
            $lastyid++;
            list($currname, $nextname) = explode('/', $twoyears);
            $toprow[] = new tabobject($lastyid, $scriptname.$lastyid, $strcriteria . ' ' . ++$currname . '/' . ++$nextname);
            $allyearids[] = $lastyid;
            
        }        
        
        if (!in_array($yid, $allyearids))   {
            $yid = end($allyearids);
        }
        
       	$tabs = array($toprow);
        print_tabs($tabs, $yid, NULL, NULL);
    }   else {
        $lastyid = get_field_sql('SELECT max(id) as maxid FROM mdl_monit_years');
        if ($years = get_records_select ('monit_years', "id>($lastyid-1)"))    {
            foreach ($years as $year)   {
                $toprow[] = new tabobject($year->id, $scriptname.  $year->id, $strcriteria . ' ' . $year->name);
            }
            
            $twoyears = get_field_sql("SELECT name FROM mdl_monit_years where id=$lastyid");
            $lastyid++;
            list($currname, $nextname) = explode('/', $twoyears);
            $toprow[] = new tabobject($lastyid, $scriptname.$lastyid, $strcriteria . ' ' . ++$currname . '/' . ++$nextname);
        }    
       	$tabs = array($toprow);
        print_tabs($tabs, $yid, NULL, NULL);
    }		
}


function get_last_yearid_in_criteria($stafftypeid)
{
    global $CFG;
        
    // id, yearid, stafftypeid, name, num, description, is_loadfile    
    $strsql = "SELECT distinct yearid FROM {$CFG->prefix}monit_att_criteria
               WHERE stafftypeid=$stafftypeid
			   ORDER BY yearid";
    if ($years = get_records_sql($strsql))  {
        $year = end($years);
        $yid  = $year->yearid; 
    } else {
        $yid  = 6;
    }
    // if ($yid == 8) $yid--; /// в новом учебном году удалить !!!!!!!!!!!!!!!!!!!!!!!!!!!!
    
    return $yid;
}


function get_array_yearid_in_criteria()
{
    global $CFG;
        
    $yearids = array();
    $stafftypes = get_records_sql("SELECT id, name  FROM {$CFG->prefix}monit_att_stafftype ORDER BY id");
    
    foreach ($stafftypes as $stafftype) {     
        // id, yearid, stafftypeid, name, num, description, is_loadfile    
        $strsql = "SELECT distinct yearid FROM {$CFG->prefix}monit_att_criteria
                   WHERE stafftypeid=$stafftype->id and yearid<6
    			   ORDER BY yearid";
        if ($years = get_records_sql($strsql))  {
            // print_object($years);
            $year = end($years);
            $yid  = $year->yearid; 
        } else {
            $yid  = 4;
        }
        $yearids[$stafftype->id] = $yid;        
    }    
    
    return $yearids;
}


function get_criteria_yearid_by_date_ak($date_ak)
{

    $sql = "datestart < '$date_ak' AND '$date_ak' < dateend";
    //  print $sql . '<br />';
    if ($date_ak > '2013-10-01' && $date_ak < '2014-08-01') {
        $cyid = 7;
    } else if (!$cyid = get_field_select('monit_years', 'id', $sql))   {
        // $sql = 'SELECT max(id) as maxid FROM mdl_monit_years';
        $sql = 'SELECT max(yearid) FROM mdl_monit_att_criteria';
        $cyid = get_field_sql($sql);
    }

    /*
    $cyid = 0;
    if ($date_ak > '2015-09-01')  {
        $cyid = 9; // !!!!!!!!!!!!!!!!!!
    } else if ($date_ak > '2014-09-01')  {
        $cyid = 8; // !!!!!!!!!!!!!!!!!!  
    } else if ($date_ak > '2013-10-01')  {
        $cyid = 7; // !!!!!!!!!!!!!!!!!!  
    } else if ($date_ak > '2012-09-01')  {
        $cyid = 6; // !!!!!!!!!!!!!!!!!!  
    }
    */

    return $cyid;
}


function add_staff($teacher, $redirlink = '../index.php')
{

    if (record_exists('monit_att_staff', 'userid', $teacher->userid))	 {
    	$u = get_record_select('user', "id = $teacher->userid", 'id, lastname, firstname');
		notify(get_string('existstaff', 'block_mou_att', fullname($u)));
		return false;
	}

    if (record_exists('user', 'id', $teacher->userid))	 {
		if ($newid = insert_record('monit_att_staff', $teacher))	{
		  // add_to_log(1, 'monitoring', 'operator added', '/blocks/monitoring/users/operators.php?level=$levelmonit&sid=$sid&rid=$rid', $USER->lastname.' '.$USER->firstname);
		  $rec->staffid = $newid;
          $rec->stafftypeid = $teacher->stafftypeid;
          if (!insert_record('monit_att_appointment', $rec))	{
                error(get_string('errorinaddingteacher','block_mou_att'), $redirlink);
          }  
		} else  {
			error(get_string('errorinaddingteacher','block_mou_att'), $redirlink);
		}
    }

    return true;
}



function listbox_staff($scriptname, $rid, $oid, $uid, $yid) 
{
  global $CFG, $edutype;

  $strtitle = get_string('selectsotr', 'block_mou_att') . '...';
  $teachermenu = array();

  $teachermenu[0] = $strtitle;

  if ($rid != 0 && $oid != 0)  {

        $teachersql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.picture, u.lastaccess,
        			   s.id as staffid, s.birthday, s.graduate
                      FROM {$CFG->prefix}user u
    	              LEFT JOIN {$CFG->prefix}monit_att_staff s ON s.userid = u.id
     	              WHERE s.{$edutype->idname}=$oid AND u.deleted = 0 AND u.confirmed = 1";
    	$teachersql .= ' ORDER BY u.lastname, firstname';

		$teachers = get_records_sql($teachersql);

		if(!empty($teachers)) {
            foreach ($teachers as $teacher) 	{
				$teachermenu[$teacher->id] = fullname($teacher);
			}
			// natsort($groupmenu);
        }
  }

  echo '<tr><td>'.get_string('sotr','block_mou_att').':</td><td>';
  popup_form($scriptname, $teachermenu, "switchteacher", $uid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}


function get_supplement_data($att)
{
	if (empty($att->namefield))	{
		return false;
	}

	if (empty($att->fielddata))	{
		return false;
	}	
		
    $enames = explode(';', $att->namefield);
    $edatas = explode('$$', $att->fielddata);
    foreach ($enames as $key => $ename)	{
    	if($ename != '#')	{
   			$att_table[$ename] = $edatas[$key];
		}    
    }
    
	return $att_table;
}

function gen_psw($input, $kol = 6)
{
  $p = '';
  $Sum = 0;
  $Num = strlen($input);

  for ($i=0; $i<$Num; $i++) {
  	$Sum += ord($input[$i]);
  }

  $p .= $Num;
  $p .= $Sum;

  for ($i=1; $i<=9; $i++) {
     $p .= ($Sum % (10-$i));
  }

  return substr($p, 0, $kol);

}


function show_upload_files ($filearea, $delurlparam)
{
    global $CFG;
    
   	$strdelete = get_string('delete');
    if ($basedir = make_upload_directory($filearea))   {
        if ($files = get_directory_list($basedir)) {
            $output = '';
            foreach ($files as $key => $file) {
                $icon = mimeinfo('icon', $file);
                if ($CFG->slasharguments) {
                    $ffurl = "$CFG->wwwroot/file.php/$filearea/$file";
                } else {
                    $ffurl = "$CFG->wwwroot/file.php?file=/$filearea/$file";
                }

                $output .= '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
                        '<a href="'.$ffurl.'" >'.$file.'</a>';
                        
                $delurl  = "deldoc.php?$delurlparam&file=$file";
                $output .= '<a href="'.$delurl.'">&nbsp;' .'<img title="'.$strdelete.'" src="'.$CFG->pixpath.'/t/delete.gif" class="iconsmall" alt="" /></a><br /> ';
                        
            }
        } else {
        	$output = '<i>' . get_string('isabscent', 'block_mou_att') . '</i>' ;
        }
    }
    return $output;
}



function get_items_menu (&$items, &$icons)
{
    global $CFG, $USER;
    
	$yid = 8;// $yearmonit;  !!!!!!!!!!!!!!!!!!!!1        
	$rid=$sid=0;

	$index_items = array();
	
	$admin_is = isadmin();
	// $region_operator_is = ismonitoperator('region');
	// if  ($admin_is || $region_operator_is) 	{
	if  ($admin_is) 	{  
		$index_items = array(1,2,3,4,5,6,7,8,9);
	}	

	$strsql = "SELECT a.id, roleid, contextid, contextlevel, userid  
			   FROM mdl_role_assignments a	JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	if ($ctxs = get_records_sql($strsql))	{
		// echo '<pre>'; print_r($ctxs); echo '<pre>';
		foreach($ctxs as $ctx1)	{
			switch ($ctx1->contextlevel)	{
				// case CONTEXT_REGION:
                case CONTEXT_REGION_ATT:
                                    if ($ctx1->roleid == 8 || $ctx1->roleid == 10)	{
									 	$idx_rayon = array(1,2,3,4,5,6,7,8,9);
    								 	$index_items = array_merge ($idx_rayon, $index_items);
    								 }	
       						         break;
				case CONTEXT_RAYON_COLLEGE:
				case CONTEXT_RAYON_UDOD:
				case CONTEXT_RAYON_DOU:					 		
				case CONTEXT_RAYON:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10) {
									 	$idx_rayon = array(1, 2,3,4,5,7, 8);
      								 	$index_items = array_merge ($idx_rayon, $index_items);
									 }
							 		 break;
				case CONTEXT_SCHOOL: 
				case CONTEXT_COLLEGE:
				case CONTEXT_UDOD:
				case CONTEXT_DOU:   if ($ctx1->roleid < 13)	{
								 		$idx_school = array(2,3,4,8);
								 	} else {
								 		$idx_school = array(2,3,8);
								 	}	
    								 $index_items = array_merge ($idx_school, $index_items);
				break;
			}
		}
		
		$index_items = array_unique($index_items);
		sort($index_items);
	}		 

    $items[1] = '<a href="'.$CFG->wwwroot."/blocks/mou_att2/gak/meeting.php\">".get_string('meetingcc', 'block_mou_att').'</a>';
    $icons[1] = '<img src="'.$CFG->pixpath.'/i/users.gif" height="16" width="16" alt="" />';

    $items[2] = '<a href="'.$CFG->wwwroot."/blocks/mou_att2/ou/ou.php?rid=$rid\">".get_string('ou', 'block_mou_att').'</a>';
    $icons[2] = '<img src="'.$CFG->pixpath.'/i/group.gif" height="16" width="16" alt="" />';

	$items[3] = '<a href="'.$CFG->wwwroot."/blocks/mou_att2/staff/staffs.php?rid=$rid\">".get_string('title', 'block_mou_att').'</a>';
	$icons[3] = '<img src="'.$CFG->pixpath.'/i/group.gif" height="16" width="16" alt="" />';

	$items[4] = '<a href="'.$CFG->wwwroot."/blocks/mou_att2/staff/importstaffs.php?rid=$rid\">".get_string('importstaff', 'block_mou_att').'</a>';
	$icons[4] = '<img src="'.$CFG->pixpath.'/i/restore.gif" height="16" width="16" alt="" />';

	$items[5] = '<a href="'.$CFG->wwwroot.'/blocks/mou_att2/staff/searchstaff.php">'.get_string('searchstaff', 'block_mou_att').'</a>';
	$icons[5] = '<img src="'.$CFG->pixpath.'/i/search.gif" height="16" width="16" alt="" />';

	$items[6] = '<a href="'.$CFG->wwwroot.'/blocks/mou_att2/ref/refschooltype.php">'.get_string('references', 'block_mou_att').'</a>';
	$icons[6] = '<img src="'.$CFG->pixpath.'/i/search.gif" height="16" width="16" alt="" />';

    $items[7] = '<a href="'.$CFG->wwwroot.'/blocks/mou_att2/roles/operators.php">'.get_string('operators', 'block_monitoring').'</a>';
    $icons[7] = '<img src="'.$CFG->pixpath.'/i/guest.gif" height="16" width="16" alt="" />';;

	$items[8] = '<a href="'.$CFG->wwwroot.'/file.php/1/instruction_mo_att2.pdf">'.get_string('instruction', 'block_mou_att').'</a>';
	$icons[8] = '<img src="'.$CFG->pixpath.'/i/info.gif" height="16" width="16" alt="" />';

    $items[9] = '<a href="'.$CFG->wwwroot."/blocks/mou_att2/gak/setquizdate.php\">".get_string('setquizdate', 'block_mou_att').'</a>';
    $icons[9] = '<img src="'.$CFG->pixpath.'/i/settings.gif" height="16" width="16" alt="" />';

    
    return $index_items;    
}

function get_edit_capability_region_rayon($rid, &$edit_capability_region, &$edit_capability_rayon)    
{
    $context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    if (!$edit_capability_region = has_capability('block/mou_att2:editou', $context_region))    {
       if ($rid == 0)   {
            $edit_capability_rayon = false;
            return false;
       } 
       $context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	   if (!$edit_capability_rayon = has_capability('block/mou_att2:editou', $context_rayon))  {
            $context_rayon = get_context_instance(CONTEXT_RAYON_COLLEGE, $rid);
	        if (!$edit_capability_rayon = has_capability('block/mou_att2:editou', $context_rayon))  {
                 $context_rayon = get_context_instance(CONTEXT_RAYON_UDOD, $rid);
          	     if (!$edit_capability_rayon = has_capability('block/mou_att2:editou', $context_rayon))  {
       	               $context_rayon = get_context_instance(CONTEXT_RAYON_DOU, $rid);
	                   if (!$edit_capability_rayon = has_capability('block/mou_att2:editou', $context_rayon))  {
	                       return false;
                       }
                 }
            }                
	   }
    }
}


function listbox_type_operators($scriptname, $level) 
{
  global $CFG;

  $strtitle = get_string('selectsotr', 'block_mou_att') . '...';
  $teachermenu = array();

  $teachermenu[0] = $strtitle;
  $teachermenu['college'] = get_string('collegeopers', 'block_mou_att');
  $teachermenu['udod'] = get_string('udodopers', 'block_mou_att');
  $teachermenu['dou'] = get_string('douopers', 'block_mou_att');
  $teachermenu['school'] = get_string('schoolopers', 'block_mou_att');
      
  echo '<tr><td>'.get_string('operators','block_monitoring').':</td><td>';
  popup_form($scriptname, $teachermenu, "switchoper", $level, "", "", "", false);
  echo '</td></tr>';
  return 1;
}



function listbox_group_ou($scriptname, $level) 
{
  global $CFG;

  $strtitle = get_string('selectgroup_ou', 'block_mou_att') . '...';
  $teachermenu = array();

  $teachermenu['-'] = $strtitle;
  $teachermenu['school'] = get_string('schoolgr', 'block_mou_att');
  $teachermenu['college'] = get_string('collegegr', 'block_mou_att');
  $teachermenu['udod'] = get_string('udodgr', 'block_mou_att');
  $teachermenu['dou'] = get_string('dougr', 'block_mou_att');

      
  echo '<tr><td>'.get_string('group_ou','block_mou_att').':</td><td>';
  popup_form($scriptname, $teachermenu, "switchoper", $level, "", "", "", false);
  echo '</td></tr>';
  return 1;
}


function get_first_rid()
{
	global $CFG, $USER;//, $admin_is, $region_operator_is;

	$ret = false;	
  	$listrayons = '';

	 $strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path  
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	  // echo $strsql . '<hr>';
	 if ($ctxs = get_records_sql($strsql))	{
	 		// print_r($ctxs);
			foreach($ctxs as $ctx1)	{
				switch ($ctx1->contextlevel)	{
					case CONTEXT_SYSTEM: if ($ctx1->roleid == 1)	{ 
											$listrayons = -1;
										 }
										 break;
					case CONTEXT_REGION_ATT:					 					 	
										 if ($ctx1->roleid == 8 || $ctx1->roleid == 10)	{
										 	$listrayons = -1;
										 }
										 break;
					case CONTEXT_RAYON_COLLEGE:
					case CONTEXT_RAYON_UDOD:
					case CONTEXT_RAYON_DOU:					 		
    				case CONTEXT_RAYON:  if ($ctx1->roleid == 8 || $ctx1->roleid == 10) {
    										$listrayons .= 	$ctx1->instanceid . ',';
										 }
								 		 break;
					case CONTEXT_SCHOOL: 
					case CONTEXT_COLLEGE:
					case CONTEXT_UDOD:
					case CONTEXT_DOU:   $contexts = explode('/', $ctx1->path);
										// print_r($contexts);
										$ctxrayon = get_record('context', 'id', $contexts[3]);
										$listrayons .= $ctxrayon->instanceid . ',';
					break;
	 			}
	 			
	 			if 	($listrayons == -1) break;
			}
	 }		 


	 if ($listrayons == '') 	{
	 	return false;
	 } else if 	($listrayons == -1) 	{
	 	$strsql = "SELECT id, name FROM {$CFG->prefix}monit_rayon ORDER BY number";
	 } else {	
	 	$listrayons .= '0';
	 	$strsql = "SELECT id, name FROM {$CFG->prefix}monit_rayon WHERE id in ($listrayons)ORDER BY number";
	 }
 	
 	
  	if($allrayons = get_records_sql($strsql))   {
  		if (count($allrayons) > 1) {
	 		 foreach ($allrayons as $rayon) 	{
	 		    $rid = $rayon->id;
                break;
	  	 	 }
  		} else {
  			$rayon = current($allrayons);
  			$rid = $rayon->id;
  		}
  	}
   	
	return $rid;
}    


function listbox_years($scriptname, $nyear)
{	
	global $CFG;
	

 	$yearmenu = array();
 	$yearmenu[0] = get_string('selectyear', 'block_mou_school') . '...';
    $lastid=0; $lastyear = 0;
    if ($years = get_records('monit_years'))  {
    	foreach ($years as $year)	{
	        $yearmenu[$year->id] = $year->name.get_string('g','block_mou_school');
            $lastid = $year->id;
            list($prevyear, $lastyear) = explode ('/', $year->name);
	    }
	}	    
    
    $yearmenu[$lastid+1] = $lastyear . '/' . ($lastyear + 1) . get_string('g','block_mou_school');
	
	echo '<tr><td>'.get_string('studyyear', 'block_mou_school').':</td><td>';
	popup_form($scriptname, $yearmenu, "switchyear", $nyear, "", "", "", false);
	echo '</td></tr>';	
	return 1;
}

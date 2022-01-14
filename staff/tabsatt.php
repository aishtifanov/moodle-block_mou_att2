<?php  // $Id: tabsatt.php,v 1.9 2014/06/16 12:00:06 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set

    $rid = required_param('rid', PARAM_INT);  // Rayon id
    $oid = required_param('oid', PARAM_INT);  // School id
    $uid = required_param('uid', PARAM_INT);          // User id
    $typeou = required_param('typeou', PARAM_TEXT);       // Type OU
    $stft = required_param('stft', PARAM_INT);  	  // Type stafftype id    
    $action   = optional_param('action', '');
    $yid = optional_param('yid', 0, PARAM_INT);  	  // Current year id
    $cyid = optional_param('cyid', 0, PARAM_INT);  	  // Current criteria year id (бывший $YEARID_CRITERIA)

	$curryear = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $curryear - 1; 
    }

    $edutype = get_config_typeou($typeou);
    $stafftype = get_record_select('monit_att_stafftype', "id = $stft", 'id, name, att_result');
    $lastcyid = get_last_yearid_in_criteria($stafftype->id);
    if ($cyid == 0 )    {
        $cyid = $lastcyid;
    }
    
	$context = get_context_instance($edutype->context, $oid);
    $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
    $editownprofile = has_capability('block/mou_att2:editownprofile', $context);
    $view_capability = has_capability('block/mou_att2:viewattestationuser', $context);
    
	$context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editattestationuser', $context_rayon);
    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);

	$strtitle = get_string('title2','block_mou_att');
	$strscript = get_string('shtatstaffs','block_mou_att');
    $strmarkscriteria = get_string('marks__', 'block_mou_att') . ': '. $stafftype->name;

    $redirlink = "staffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid&stft=$stft";
    
    if (!$edit_capability && !($editownprofile && $uid == $USER->id) && !$view_capability && !$edit_capability_region && !$edit_capability_rayon)  {
		error(get_string('permission', 'block_mou_school'), $redirlink);
    }    
    
    $strsql = "SELECT s.id, a.id as appointmentid, a.appointment, a.standing_this, a.qualify, a.qualify_date, a.qualifynow
               FROM mdl_monit_att_staff s INNER JOIN mdl_monit_att_appointment a ON s.id=a.staffid
               WHERE s.userid=$uid and a.stafftypeid=$stft";    
    $staff = get_record_sql($strsql);
    
    // $appointment = get_record_select ('monit_att_appointment', "staffid={$staff->id} and stafftypeid=$stft", 'standing_this, qualify, qualify_date, qualifynow');
    // print_r($staff); exit();
    $user = get_record_sql("SELECT id, lastname, firstname FROM {$CFG->prefix}user WHERE id=$uid");

    $rayon = get_record_select('monit_rayon', "id=$rid", 'id, name');

    $ou = get_record_select($edutype->tblname, "id=$oid", 'id, name');
    
    // print_r($table);
    switch ($action)    {
        case 'excel2008': $table = table_attestation2008($rid, $oid, $uid, $typeou, $stft, $edutype);
                      print_table_to_excel($table, 1);        
                      exit();
                      
	    case 'word2008':  $table = table_attestation2008($rid, $oid, $uid, $typeou, $stft, $edutype);
                      print_table_to_word($table, 1);        
                      exit();
                      
	    case 'writer2008':$table = table_attestation2008($rid, $oid, $uid, $typeou, $stft, $edutype);
                      print_table_to_word($table, 1, 'odt');        
                      exit();

        case 'excel': $table = table_attestation($rid, $oid, $uid, $typeou, $stft, $edutype, $cyid, $lastcyid);
                      print_table_to_excel($table, 1);        
                      exit();
                      
	    case 'word':  $table = table_attestation($rid, $oid, $uid, $typeou, $stft, $edutype, $cyid, $lastcyid);
                      print_table_to_word($table, 1);        
                      exit();
                      
	    case 'writer':$table = table_attestation($rid, $oid, $uid, $typeou, $stft, $edutype, $cyid, $lastcyid);
                      print_table_to_word($table, 1, 'odt');        
                      exit();
        
        case 'wordshortinfo':   
                      $table = table_attestation($rid, $oid, $uid, $typeou, $stft, $edutype, $cyid, $lastcyid, 'shortinfo');
                      $frm = data_submitted();
                      print_shortinfo_to_word($table, $frm);
                      exit();
                      
        case 'wordexpertzakl':
                      $table = table_attestation($rid, $oid, $uid, $typeou, $stft, $edutype, $cyid, $lastcyid, 'expertzakl');
                      //print_object($table);
                      print_expertzakl_to_word($table, $uid);
                      exit();
        
    }      

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $strmarkscriteria, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strmarkscriteria, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); 


    if (empty($currenttab) or empty($user)) {
        error('You cannot call this script in that way');
    }
    if ($currenttab == 'attestation') {
        //     Аттестационные данные по старым критериям можно просмотреть 
        // (без возможности редактирования) на вкладке «Аттестационные данные (2009-2012)».
        $msg = '<font color=red><b>ВНИМАНИЕ! С 2017/2018 учебного года начинают действовать новые 
                        критерии аттестации по всем должностям. 
                        Тем сотрудникам ОУ, у которых аттестация запланирована на 2017/2018 уч. год и 
                        которые уже заполнили аттестационные данные по критериям 2016/2017, необходимо 
                        заполнить аттестационные данные по новым критериям. Т.к. многие критерии совпадают, 
                        то можно скопировать данные из старых критериев в новые. </b></font>';

        // $msg = '<font color="#a52a2a" size="6"><b>ВНИМАНИЕ! Доступ к заполнению критериев закрыт <br> с 29 мая 2017 года до 9 июня 2017 года.</font>';

        print_heading($msg, 'center', 5);

    }
    
    // $yearids = get_array_yearid_in_criteria();
    // $PRED_YEARID_CRITERIA = $yearids[$stft];// get_last_yearid_in_criteria($appointment->stafftypeid);

    $urlparam = "rid=$rid&oid=$oid&yid=$yid&uid=$uid&typeou=$typeou&stft=$stft";

    $toprow = array();
    $toprow[] = new tabobject('attestation',   "attestation.php?$urlparam",   get_string('attestation', 'block_mou_att'));
    $toprow[] = new tabobject('staffattempts', "staffattempts.php?$urlparam", get_string('staffattempts', 'block_mou_att'));
    $toprow[] = new tabobject('remark', "remark.php?$urlparam", get_string('remarks', 'block_monitoring'));
    $toprow[] = new tabobject('shortinfo', "shortinfo.php?$urlparam", get_string('shortinfo', 'block_mou_att'));
    $toprow[] = new tabobject('expertzakl', "expertzakl.php?$urlparam", get_string('expertzakl', 'block_mou_att'));
    $tabs = array($toprow);
    print_tabs($tabs, $currenttab, NULL, NULL);
    
    
    if ($currenttab == 'attestation' || $currenttab == 'expertzakl' ||  $currenttab == 'shortinfo')   {
        $toprow2 = array();
        
        $strsql = "SELECT  c.yearid, y.name FROM mdl_monit_att_criteria c
                    left join mdl_monit_years y on y.id=c.yearid
                    WHERE c.stafftypeid=$stafftype->id
                    group by c.yearid
                    ORDER BY yearid";
                   
        if ($cyears = get_records_sql_menu($strsql))  {
            foreach ($cyears as $cyearid => $cyearname) {
                if ($cyearid == 2)  {
                    $strsql = "SELECT yearid, staffid  FROM mdl_monit_att_attestation 
                               WHERE  staffid=$staff->id AND yearid=$cyearid";
                    if (record_exists_sql($strsql))  {
                        $toprow2[] = new tabobject('attestation2', "{$currenttab}2008.php?$urlparam", 'Критерии 2008');
                    } 
                } else {
                    if (empty($cyearname))  {
                        $curry = date('Y');
                        $cyearname = $curry . '/' . ($curry+1); 
                    }
                    $toprow2[] = new tabobject('attestation'.$cyearid, "{$currenttab}.php?$urlparam&cyid=$cyearid", 'Критерии ' . $cyearname);
                }
            }
        } else {
            $cyid  = 1;
        }
        
        $tabs2 = array($toprow2);
        print_tabs($tabs2, 'attestation'.$cyid, NULL, NULL);
    }    
        
    
    $strpred = get_string("predstavlenie", 'block_mou_att');
    $filearea = "0/users/att/$rid/$uid/_".$staff->appointmentid;
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
                    '<a href="'.$ffurl.'" >Документ</a>';
        }
    } else {
    	$output = '' ;
    }

    switch ($currenttab)    {
        case 'shortinfo':
                        $strinfo = fullname($user) . ' <br>' . $staff->appointment . ' <br>' ;
                        $strinfo .=  $ou->name . '<br>(' . $rayon->name. ')<br> '; 
                        print_heading($strinfo, 'center', 4);
        break;
                        
        case 'expertzakl':
        break;
        
        default:
                        print_heading($rayon->name.'<br> '.$ou->name, 'center', 4);
                     
                        $strfio = fullname($user) . ' <br>' . $staff->appointment;
                        print_heading($strfio, 'center', 3);
    }    
    
    $strpred = get_string ('predstavlenie', 'block_mou_att');

?>
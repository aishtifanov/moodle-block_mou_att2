<?php // $Id: lib_dean.php,v 1.1 2012/02/27 06:46:34 shtifanov Exp $

/*

SELECT * FROM `moodle`.`mdl_role_assignments`
where userid in (SELECT id FROM `moodle`.`mdl_user` where deleted=1);

DELETE FROM `moodle`.`mdl_role_assignments`
where userid in (SELECT id FROM `moodle`.`mdl_user` where deleted=1);
*/

require_once("$CFG->libdir/excel/Worksheet.php");
require_once("$CFG->libdir/excel/Workbook.php");

define("MAX_SYMBOLS_LISTBOX", 120);

// Display list faculty as popup_form
function listbox_faculty($scriptname, $fid)
{
  global $CFG;

  $facultymenu = array();
  $facultymenu[0] = get_string('selectafaculty', 'block_dean').'...';

  if($allfacs = get_records_sql("SELECT * FROM {$CFG->prefix}dean_faculty ORDER BY number"))   {
		foreach ($allfacs as $facultyI) 	{
			$facultymenu[$facultyI->id] =$facultyI->name;
		}
  }

  echo '<tr> <td>'.get_string('ffaculty', 'block_dean').': </td><td>';
  popup_form($scriptname, $facultymenu, 'switchfac', $fid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

// Display list speciality as popup_form
function listbox_speciality($scriptname, $fid, $sid)
{
  $specialitymenu = array();
  $specialitymenu[0] = get_string('selectaspeciality','block_dean').' ...';

  if ($fid != 0)  {
	if($arr_specs =  get_records('dean_speciality', 'facultyid', $fid, 'name'))	{
		foreach ($arr_specs as $spec) {
			$len = strlen ($spec->name);

			if ($len > MAX_SYMBOLS_LISTBOX)  {
				$spec->name = mb_substr($spec->name, 0, MAX_SYMBOLS_LISTBOX, "UTF-8") . ' ...';
			}

			$specialitymenu[$spec->id] =$spec->name;
		}
	}
  }

  echo '<tr><td>'.get_string('sspeciality', 'block_dean').':</td><td>';
  popup_form($scriptname, $specialitymenu, 'switchspec', $sid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


// Display list curriculum as popup_form
function listbox_curriculum($scriptname, $fid, $sid,  $cid)
{
  global $CFG;

  $currmenu = array();
  $currmenu[0] = get_string('selectacurr', 'block_dean') . ' ...';

  if ($fid != 0 && $sid != 0)   {

	 $arr_currs =  get_records_sql ("SELECT id, name FROM  {$CFG->prefix}dean_curriculum
  								     WHERE facultyid=$fid AND specialityid=$sid ORDER BY name");
  	 if ($arr_currs)		{
		foreach ($arr_currs as $cr) {
			$len = strlen ($cr->name);
			if ($len > MAX_SYMBOLS_LISTBOX)  {
				$cr->name = mb_substr($cr->name, 0, MAX_SYMBOLS_LISTBOX, "UTF-8") . ' ...';
			}

			$currmenu[$cr->id] = $cr->name;
		}
	 }
  }

  echo '<tr><td>'.get_string('curriculum', 'block_dean') . ':</td><td>';
  popup_form($scriptname, $currmenu, 'switchcurr', $cid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

// Display list group as popup_form
function listbox_group($scriptname, $fid, $sid, $cid, $gid, $idotd = 0, $alllistgroup = 1)
{
  global $CFG;

  if ($idotd == 0) {
     $idotdelenie = '';
  } else {
     $idotdelenie = " AND idotdelenie in ($idotd)";
  }

  if ($cid == 0)   {
     $curriculum = '';
  } else {
     $curriculum = " AND curriculumid = $cid ";
  }

  $groupmenu = array();
  $groupmenu[0] = get_string('selectagroup', 'block_dean') . ' ...';

  if($sid == 0) {
  	$specialityid = '';
  } else {
	$specialityid = " AND specialityid=$sid";
  }

  if ($fid != 0)   {
  	$grup = '';
	if($alllistgroup == 0) {
		$strsql = "SELECT DISTINCT(grup) FROM {$CFG->prefix}bsu_students";
		$sqls = get_records_sql ($strsql);

		foreach($sqls as $sql) {
   			$array[] = $sql->grup;
		}
		$grup = implode(',', $array);
		$grup = " AND name IN($grup)";
	}

	  $strsql = "SELECT id, name  FROM {$CFG->prefix}dean_academygroups
		         WHERE facultyid=$fid $specialityid $curriculum $idotdelenie $grup
				 ORDER BY name DESC";

      if ($arr_group = get_records_sql ($strsql)) 	{
    		foreach ($arr_group as $gr) {
    			$groupmenu[$gr->id] =$gr->name;
    		}
      }
 }

  echo '<tr><td>'.get_string('group').':</td><td>';
  popup_form($scriptname, $groupmenu, 'switchgroup', $gid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

function listbox_group_allfaculty($scriptname, $fid, $gid, $is_all = true, $startyear = 0)
{
  global $CFG;

  $groupmenu = array();
  $groupmenu[0] = get_string('selectagroup', 'block_dean') . ' ...';

  $strselect = '';

  if ($startyear != 0)  {
        $groupmenu[-1] = 'Все группы';
        $strselect = "AND startyear = $startyear";
  }

  if ($fid != 0)   {
	$arr_group = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}dean_academygroups
		 								  WHERE facultyid=$fid $strselect
		 								  ORDER BY name");
  }

  if (isset($arr_group)) 	{
		foreach ($arr_group as $gr) {
			if ($is_all)	{
				$groupmenu[$gr->id] =$gr->name;
			} else {
				$namegroup = trim($gr->name);
				$len = strlen ($namegroup);
				$numgroup = substr($namegroup, -2);
				if ($len == 6 && $numgroup < 50)	{
					$groupmenu[$gr->id] =$gr->name;
				}
			}
		}
  }

  echo '<tr><td>'.get_string('group').':</td><td>';
  popup_form($scriptname, $groupmenu, 'switchgroup', $gid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


// Display list all group as popup_form
function listbox_all_group($scriptname, $gid)
{
  global $CFG, $USER;

  $methodist_is = ismethodist();

  if ($methodist_is)	{
    	$methodist = get_record('dean_methodist', 'userid', $USER->id);
    	$strsql = "SELECT id, name  FROM {$CFG->prefix}dean_academygroups
    			   WHERE facultyid = {$methodist->facultyid}
				   ORDER BY name";
  } else {
    	$strsql = "SELECT id, name  FROM {$CFG->prefix}dean_academygroups
				  ORDER BY name";
  }

  $groupmenu = array();
  $groupmenu[0] = get_string('selectagroup', 'block_dean') . ' ...';
  $arr_group = get_records_sql($strsql);

  if (isset($arr_group)) 	{
		foreach ($arr_group as $gr) {
			$groupmenu[$gr->id] =$gr->name;
		}
  }

  echo '<div align=center>'.get_string('group').':';
  popup_form($scriptname, $groupmenu, 'switchgroup', $gid, '', '', '', false);
  echo '</div><br><br>';

  return 1;
}


// Display list disciplines as popup_form
function listbox_discipline($scriptname, $fid, $sid, $cid, $gid, $did)
{
  global $CFG;

  $strtitle = get_string('selectadiscipline', 'block_dean') . ' ...';
  $groupmenu = array();

  $disciplinemenu[0] = $strtitle;

  if ($gid !=0)	{

	  if ($cid == 0)   {
		  $agroup = get_record ('dean_academygroups', 'id', $gid);
	      $curriculum = get_record('dean_curriculum', 'id', $agroup->curriculumid);

	  } else {
		  $curriculum = get_record('dean_curriculum', 'id', $cid);
	  }

	  $arr_discipline = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}dean_discipline
		 								  WHERE curriculumid={$curriculum->id}
										  ORDER BY name");

	  if ($arr_discipline) 	{
			foreach ($arr_discipline as $ds) {
				$disciplinemenu[$ds->id] =$ds->name;
			}
	  }
  }

  echo '<tr><td>'.get_string('discipline', 'block_dean').':</td><td>';
  popup_form($scriptname, $disciplinemenu, 'switchdiscipline', $did, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

// Display list student of group
function listbox_student($scriptname, $fid, $sid, $cid, $gid, $uid)
{
  global $CFG;

  $strtitle = get_string("selectastudent","block_dean")."...";
  $studentmenu = array();

  $studentmenu[0] = $strtitle;

  if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0)  {
		$studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
							  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
                              u.lastaccess, m.academygroupid
                            FROM {$CFG->prefix}user u
	                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";

	    $studentsql .= 'WHERE academygroupid = '.$gid.' AND u.deleted = 0 AND u.confirmed = 1 ';
	    $studentsql .= 'ORDER BY u.lastname';
        $students = get_records_sql($studentsql);

		if(!empty($students)) {
            foreach ($students as $student) 	{
				$studentmenu[$student->id] = fullname($student);
			}
			// natsort($groupmenu);
        }
  }

  echo '<tr><td>'.get_string('student','block_dean').':</td><td>';
  popup_form($scriptname, $studentmenu, "switchstudent", $uid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}

// Return number of kurs
function get_kurs($enrolyear)
{
 $start->year = substr($enrolyear, 0, 4);
 if ($start->year < 2000)  return 0;
 $start->month = 9;
 $start->day = 1;
 $startime = make_timestamp($start->year, $start->month, $start->day);
 $nowtime = time();
 $days = floor(($nowtime -  $startime)/DAYSECS);
 if ($days < 0)  {
 	$kurs = 0;
 }
 else {
 	$kurs = floor($days/356) + 1;
 }
 return $kurs;
}

// Return start date of term
function get_startdateweek($enrolyear, $term, $numweek, $retstring=true)
{
 $start->year = substr($enrolyear, 0, 4);
 $start->year = $start->year + floor($term/2);

 if ($term%2 == 0)  {
    $start->month = 2;
    $start->day = 1;
 }
 else {
   $start->month = 9;
   $start->day = 1;
 }

 $date = make_timestamp($start->year, $start->month, $start->day, 12);
 $date = $date + (($numweek-1)*7*DAYSECS);

 if ($retstring == true)	{
	 $monthstring = get_string('lm_'.date('n',$date), 'block_dean');
	 $datestring =  str_replace(' 0', '', gmstrftime(' %d', $date))." ".$monthstring." ".date('Y', $date);
 }
 else 	{
	 $datestring =  $date;
 }

 return $datestring;
}

// Print tabs semestr
function print_tabs_semestr($term, $link)
{
 	    for ($i=1; $i<=12; $i++)   {
   	       $toprow3[] = new tabobject($i, $link.$i, $i);
    	}
        $currenttab = $term;
        $tabs3 = array($toprow3);

		print_heading(get_string('terms','block_dean'), 'center', 4);
 		print_tabs($tabs3, $currenttab, NULL, NULL);
}

// Print tabs weeks
function print_tabs_weeks($week, $totalweeks, $startdate, $denominator,  $link)
{
		$toprow4=array();
		$numdayinweek = date('w',$startdate);
		if ($numdayinweek == 0) 	{
			$startdate = $startdate + DAYSECS;
			$toprow4[] = new tabobject(1, $link."1&ds=$startdate&d=$denominator", 1);
		} else  if ($numdayinweek == 1) 	{
			$toprow4[] = new tabobject(1, $link."1&ds=$startdate&d=$denominator", 1);
		} else  {
			$toprow4[] = new tabobject(1, $link."1&ds=$startdate&d=$denominator", 1);
			$startdate = $startdate - ($numdayinweek-1)*DAYSECS;
		}

 	    for ($i=2; $i<=$totalweeks; $i++)   {
		   $ds  = $startdate + (($i-1)*7*DAYSECS);
		   if ($i%2 == 0) $cz = 1 - $denominator;
		   else $cz = $denominator;
  	       $toprow4[] = new tabobject($i, $link.$i."&ds=$ds&d=$cz", $i);
    	}
        $currenttab = $week;
        $tabs4 = array($toprow4);

		print_heading(get_string('week','block_dean'), 'center', 4);
 		print_tabs($tabs4, $currenttab, NULL, NULL);
}

// Print tabs days in week
function print_tabs_dayinweek($numweek, $dayinweek, $datestart, $link)
{
		if ($numweek == 1)	{
			$numdayinweek = date('w',$datestart);
			if ($numdayinweek == 0) 	{
				$datestart = $datestart + DAYSECS;
				$numdayinweek++;
			}
			// $dayinweek = $numdayinweek;
		} else 	{
			$numdayinweek = 1;
		}

 	    for ($i=$numdayinweek, $j=1; $i<=6; $i++, $j++)   {
		   $ds  = $datestart + (($j-1)*DAYSECS);
		   $monthstring = get_string('lm_'.date('n',$ds),'block_dean');
	 	   $startdate =  str_replace(' 0', '', gmstrftime(' %d', $ds))." ".$monthstring;
   	       $toprow5[] = new tabobject($i, $link.$i, get_string('wd_'.$i, 'block_dean').', '.$startdate);
    	}
		//echo '---------'.$dayinweek.'----------<br>';
		//print_r($toprow5);
        $tabs5 = array($toprow5);

		print_heading(get_string('daysweek','block_dean'), 'center', 4);
 		print_tabs($tabs5, $dayinweek, NULL, NULL);
}


// Get shortest name from discipline full name
function abbreviation ($disname)
{
	$shortname = '';
	$words = explode (' ', $disname);
	$cnt = count ($words);
	switch ($cnt)	{
		case 1:	$shortname = substr($disname, 0, 5);
		break;
		case 2:	$shortname = $shortname. substr($words[0], 0, 2);
				$shortname = $shortname. substr($words[1], 0, 2);
		break;
		default: $i=0;
		 		do {
				if (strlen($words[$i]) > 1) 	{
					$shortname = $shortname.$words[$i][0];
				}
					$i++;
				} while ($i<$cnt && $i<5);
				$shortname = strtoupper($shortname);
	}
	return $shortname;
}


// Determines if a user is group curator
function iscurator ($userid=0) {
    global $USER;

    if (empty($USER->id)) {
        return false;
    }

    if (empty($userid))  {
        $userid = $USER->id;
    }

//    if (isadmin($userid)) {  // admins can do anything
//        return true;
//    }

    $ret = false; // id, academygroupid, userid
    if ($f_oper = get_record_select('dean_curators', "userid = $userid" , 'id, academygroupid'))  {
	       $ret = $f_oper->academygroupid;
	}

    return $ret;
}


// Determines if a user is dean Methodist
function ismethodist($userid=0)
{
    global $USER;

    if (empty($USER->id)) {
        return false;
    }

    if (empty($userid))  {
        $userid = $USER->id;
    }

//    if (isadmin($userid)) {  // admins can do anything
//        return true;
//    }

    // ACCESS DENIED !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // return false;
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $ret = false;
    if ($f_oper = get_record_select('dean_methodist', "userid = $userid" , 'id, facultyid'))  {
	       $ret = $f_oper->facultyid;
	}

    return $ret;
}

// Determines if a user is dean Dean
function isdean($userid=0)
{
    global $USER;

    if (empty($USER->id)) {
        return false;
    }

    if (empty($userid))  {
        $userid = $USER->id;
    }

//    if (isadmin($userid)) {  // admins can do anything
//        return true;
//    }

    // ACCESS DENIED !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    // return false;
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $ret = false;
    if ($f_oper = get_record_select('dean_faculty', "deanid = $userid" , 'id'))  {
	       $ret = $f_oper->id;
	}

    return $ret;
}

function get_rus_format_date($d, $format='short')
{
 $arrdate = usergetdate($d);

 if (strlen($arrdate['mday']) == 1) $arrdate['mday'] = '0' . $arrdate['mday'];
 if (strlen($arrdate['mon']) == 1)  $arrdate['mon'] = '0' . $arrdate['mon'];

 $str = $arrdate['mday'];
 if ($format == 'short')	{
	$str .= '.' . $arrdate['mon'] .  '.';
 } else if ($format == 'full') {
	 $str .= ' ' . get_string('lm_'.$arrdate['mon'], 'block_dean') . ' ';
 }
 $str .= $arrdate['year']; // . ' г.';
 return $str;
}


function xfgetcsv($f='', $x='', $s=';')
{
  	if($str=fgets($f))  {
  			$data=split($s, trim($str));
  			return $data;
  	}
  	else	{
  		return FALSE;
  	}
}


function gen_psw($input)
{
  $p = '';
  $Sum = 0;
  $Pr = 1;
  $Num = strlen($input);

  for ($i=0; $i<$Num; $i++) {
  	$Sum += ord($input[$i]);
  }

  for ($i=0; $i<$Num; $i++) {
  	if ($input[$i] != '0') 	{
	  	$Pr *= $input[$i];
	}
  }

  $Sum *= $Num;
  $Sum *= $Pr;

  $p .= $Num;
  $p .= $Pr;
  $p .= $Sum;


  for ($i=1; $i<=9; $i++) {
     $p .= ($Sum % (10-$i));
  }


  return substr($p, $input[$Num-1], 6);
}


// Function for delete tags from glossary
function strip_tags_from_field($tablename, $fieldname)
{
  global $CFG, $db;

  $fulltablename = $CFG->prefix . $tablename;

  if ($metatables = $db->MetaTables()) 	{
   	 // print_r($metatables);
   	 if (in_array($fulltablename, $metatables))	 {
   	 	echo $fulltablename;
	    if ($metacolumns = $db->MetaColumns($fulltablename))   {
 	  	 	 // print_r($metacolumns);
 	  	 	 $isfield = false;
	         foreach($metacolumns as $metacolumn) {
 	        	if ($metacolumn->name == $fieldname) {  $isfield = true; break; }
	         }
	         if ($isfield == false) 	{
	    	 	 error("Field  '$fieldname' not  find!");
	    	 }
    	}
   	 } else {
   	 	error("Table '$fulltablename' not  find!");
   	 }
  }

  // SELECT id, concept FROM `moodle`.`mdl_glossary_entries` where concept LIKE '%</%'
  $recs = get_records_sql("SELECT * FROM $fulltablename WHERE $fieldname LIKE '%</%'");
  // print_r($recs);

  foreach ($recs as $rec) 	{
  	$rec->{$fieldname} = strip_tags ($rec->{$fieldname}, '<sup><sub><img>');

  	if (update_record ($tablename, $rec))	{
	  	print_r($rec);
  		echo '<br>'.$rec->{$fieldname}.'<br><hr>';
  	} else {
	  	notify("Record {$rec->id} not update!");
	  	continue;
  	}
  }
}


function get_users_dean($get=true, $search='', $confirmed=false, $exceptions='', $sort='firstname ASC',
                   $firstinitial='', $lastinitial='', $page=0, $recordsperpage=99999, $fields='*',
                   $fullcoincidence='off', $fio='fio') {

    global $CFG;

    $limit     = sql_paging_limit($page, $recordsperpage);
    $LIKE      = sql_ilike();
    $fullname  = sql_fullname();

    $select = 'username <> \'guest\' AND deleted = 0';

    if (!empty($search)){
        $search = trim($search);
//        $select .= " AND ($fullname $LIKE '%$search%' OR email $LIKE '%$search%') ";
        if($fullcoincidence == 'ON') {
            switch ($fio) {
                case 'fio':
                    $select .= " AND (firstname $LIKE '$search' OR lastname $LIKE '$search' OR email $LIKE '$search')";
                break;
                case 'ln':
                    $select .= " AND (lastname $LIKE '$search' OR email $LIKE '$search')";
                break;
                case 'fn':
                    $select .= " AND (firstname $LIKE '$search' OR email $LIKE '$search')";
                break;
            }
        } else {
            switch ($fio) {
                case 'fio':
                    $select .= " AND (firstname $LIKE '%$search%' OR lastname $LIKE '%$search%' OR email $LIKE '%$search%')";
                break;
                case 'ln':
                    $select .= " AND (lastname $LIKE '$search%' OR email $LIKE '%$search%')";
                break;
                case 'fn':
                    $select .= " AND (firstname $LIKE '$search%' OR email $LIKE '%$search%')";
                break;
            }
        }
    }

    if ($confirmed) {
        $select .= ' AND confirmed = \'1\' ';
    }

    if ($exceptions) {
        $select .= ' AND id NOT IN ('. $exceptions .') ';
    }

    if ($firstinitial) {
        $select .= ' AND firstname '. $LIKE .' \''. $firstinitial .'%\'';
    }
    if ($lastinitial) {
        $select .= ' AND lastname '. $LIKE .' \''. $lastinitial .'%\'';
    }

    if ($sort and $get) {
        $sort = ' ORDER BY '. $sort .' ';
    } else {
        $sort = '';
    }

    if ($get) {
        return get_records_select('user', $select .' '. $sort .' '. $limit, '', $fields);
    } else {
        return count_records_select('user', $select .' '. $sort .' '. $limit);
    }
}

function is_date($strdate, $format='ru')
{
   if (empty($strdate)) return false;

   $rez = false;
   if ($format == 'ru')	{
	   if (!strpos($strdate, '.')) return false;
	   $strdate .= '..';
	   $day = $month = $year = 0;
	   list($day, $month, $year) = explode(".", $strdate);
	   $rez = checkdate($month, $day, $year);
   } else if ($format == 'en')	{
	   if (!strpos($strdate, '-')) return false;
	   $strdate .= '--';
	   $day = $month = $year = 0;
	   list($year, $month, $day) = explode("-", $strdate);
	   $rez = checkdate($month, $day, $year);
   }
   return $rez;
}

function convert_date($strdate, $from='ru', $to='en')
{
   if ($from=='ru' && $to=='en')  {
   	   if (!is_date($strdate, 'ru')) {
   	   	  $newfdate = $strdate;
   	   } else {
		   list($day, $month, $year) = explode(".", $strdate);
		   $newfdate = $year.'-'.$month.'-'.$day;
	   	   if (!is_date($newfdate, 'en')) {
 	  	   	  $newfdate = $strdate;
  	 	   }
	   }
   } else if ($from=='en' && $to=='ru')  {
   	   if (!is_date($strdate, 'en')) {
   	   	  $newfdate = $strdate;
   	   } else {
		  list($year, $month, $day) = explode("-", $strdate);
	 	  $newfdate = $day.'.'.$month.'.'.$year;
	   	   if (!is_date($newfdate, 'ru')) {
 	  	   	  $newfdate = $strdate;
  	 	   }
	   }
   }
   return $newfdate;
}


function listbox_courses_TOZ($scriptname, $courseid, $category = 54, $category2 = 64)
{
  global $CFG;

    $courseids = array(1700, 1711);

	$courseTOZ = get_courses ($category2); // , 'c.fullname'); // 64
    foreach ($courseTOZ as $ct)	{
    	$courseids[] = 	$ct->id;
    }

	$courseTOZ = get_courses ($category); // 54
    foreach ($courseTOZ as $ct)	{
    	$courseids[] = 	$ct->id;
    }

  	$coursemenu = array();
  	$coursemenu[0] = get_string('selectadiscipline', 'block_dean') . ' ...';

	foreach ($courseids as $crsid) 	{
		if($course = get_record_sql("SELECT id, fullname FROM {$CFG->prefix}course WHERE id = $crsid"))   {
			$coursemenu[$crsid] = $course->fullname;
		}
	}

	echo '<tr><td>'.get_string('discipline', 'block_dean').':</td><td>';
	popup_form($scriptname, $coursemenu, 'switchcoursestoz', $courseid, '', '', '', false);
	echo '</td></tr>';
	return 1;
}


/**
 * Print a nicely formatted table to EXCEL.
 *
 * @param array $table is an object with several properties.
 *     <ul<li>$table->head - An array of heading names.
 *     <li>$table->align - An array of column alignments
 *     <li>$table->size  - An array of column sizes
 *     <li>$table->wrap - An array of "nowrap"s or nothing
 *     <li>$table->data[] - An array of arrays containing the data.
 *     <li>$table->width  - A percentage of the page
 *     <li>$table->tablealign  - Align the whole table
 *     <li>$table->cellpadding  - Padding on each cell
 *     <li>$table->cellspacing  - Spacing between cells
 *****************  NEW (added by shtifanov) **********************
 *     <li>$table->downloadfilename - .XLS file name (new)
 *     <li>$table->worksheetname - Name of sheet in work book  (new)
 *     <li>$table->titles  - An array of titles names in firsts rows. (new)
 *     <li>$table->titlesrows  - Height of titles rows (new)
 *     <li>$table->columnwidth  - An array of columns width in Excel table (new)
 * </ul>
 * @param bool $return whether to return an output string or echo now
 * @return boolean or $string
 * @todo Finish documenting this function
 */

function print_table_to_excel($table, $lastcols = 0, $table2 = '')
{
    global $CFG;

    $downloadfilename = $table->downloadfilename;

    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"{$downloadfilename}.xls\"");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    $workbook = new Workbook("-");
    $txtl = new textlib();

	$strwin1251 =  $txtl->convert($table->worksheetname, 'utf-8', 'windows-1251');
    $myxls =&$workbook->add_worksheet($strwin1251);

	$numcolumn = count ($table->columnwidth) - $lastcols;
    $i=0;
    foreach ($table->columnwidth as $width)	{
		$myxls->set_column($i, $i, $width);
		$i++;
	}

	$formath1 =& $workbook->add_format();
	$formath1->set_size(12);
    $formath1->set_align('center');
    $formath1->set_align('vcenter');
	$formath1->set_color('black');
	$formath1->set_bold(1);
	// $formath1->set_italic();
	$formath1->set_text_wrap();
	// $formath1->set_border(2);

    $i = $ii = 0;

    foreach ($table->titles as $key => $title)	{
		$myxls->set_row($i, $table->titlesrows[$key]);
		$strwin1251 =  $txtl->convert($title, 'utf-8', 'windows-1251');
	    $myxls->write_string($i, 0, $strwin1251, $formath1);
		$myxls->merge_cells($i, 0, $i, $numcolumn-1);
		$i++;
    }

	$formath2 =& $workbook->add_format();
	$formath2->set_size(11);
    $formath2->set_align('center');
    $formath2->set_align('vcenter');
	$formath2->set_color('black');
	$formath2->set_bold(1);
	//$formath2->set_italic();
	$formath2->set_border(2);
	$formath2->set_text_wrap();

    if (!empty($table->head)) {
    	$formatp = array();
    	$numcolumn = count ($table->head) - $lastcols;
        foreach ($table->head as $key => $heading) {
        	if ($key >= $numcolumn) continue;
	   		$strwin1251 =  $txtl->convert(strip_tags($heading), 'utf-8', 'windows-1251');
	        $myxls->write_string($i, $key,  $strwin1251, $formath2);

			$formatp[$key] =& $workbook->add_format();
			$formatp[$key]->set_size(10);
		    $formatp[$key]->set_align($table->align[$key]);
		    $formatp[$key]->set_align('vcenter');
			$formatp[$key]->set_color('black');
			$formatp[$key]->set_bold(0);
			$formatp[$key]->set_border(1);
			$formatp[$key]->set_text_wrap();
        }
        $i++;
        $ii = $i;
    }

    if (isset($table->data)) foreach ($table->data as $keyrow => $row) {
      	$numcolumn = count ($row) - $lastcols;
        foreach ($row as $keycol => $item) 	{
           	if ($keycol >= $numcolumn) continue;
        	$clearitem = strip_tags($item);
        	switch ($clearitem)	{
        		case '&raquo;': $clearitem = '>>'; break;
        		case '&laquo;': $clearitem = '<<'; break;
        	}
 			$strwin1251 =  $txtl->convert($clearitem, 'utf-8', 'windows-1251');

			//$len = strlen ($strwin1251);
			$specsym = substr($strwin1251, 0, 1);
			$number1 = substr($strwin1251, 1);
			$number2 = substr($strwin1251, 2);

			if ($specsym == "'" && (is_number($number1) || is_number($number2)))	{
				$myxls->write_string($i + $keyrow, $keycol,  $number1, $formatp[$keycol]);
			} else {
				$myxls->write($i + $keyrow, $keycol,  $strwin1251, $formatp[$keycol]);
			}
			$ii = $i + $keyrow;
		}
    }

    if (!empty($table2)) {
    	$i = $ii + 2;

    	$formatp = array();
    	$numcolumn = count ($table2->head) - $lastcols;
        foreach ($table2->head as $key => $heading) {
        	if ($key >= $numcolumn) continue;
	   		$strwin1251 =  $txtl->convert(strip_tags($heading), 'utf-8', 'windows-1251');
	        $myxls->write_string($i, $key,  $strwin1251, $formath2);

			$formatp[$key] =& $workbook->add_format();
			$formatp[$key]->set_size(10);
		    $formatp[$key]->set_align($table2->align[$key]);
		    $formatp[$key]->set_align('vcenter');
			$formatp[$key]->set_color('black');
			$formatp[$key]->set_bold(0);
			$formatp[$key]->set_border(1);
			$formatp[$key]->set_text_wrap();
        }
        $i++;
        if (isset($table2->data)) foreach ($table2->data as $keyrow => $row) {
          	$numcolumn = count ($row) - $lastcols;
            foreach ($row as $keycol => $item) 	{
               	if ($keycol >= $numcolumn) continue;
            	$clearitem = strip_tags($item);
            	switch ($clearitem)	{
            		case '&raquo;': $clearitem = '>>'; break;
            		case '&laquo;': $clearitem = '<<'; break;
            	}
     			$strwin1251 =  $txtl->convert($clearitem, 'utf-8', 'windows-1251');
    			$myxls->write($i + $keyrow, $keycol,  $strwin1251, $formatp[$keycol]);
    			$ii = $i + $keyrow;
    		}
        }
    }


    $workbook->close();
}

  /**
 * Print a nicely formatted table to EXCEL.
 *
 * @param array $table is an object with several properties.
 *     <ul<li>$table->head - An array of heading names.
 *     <li>$table->align - An array of column alignments
 *     <li>$table->size  - An array of column sizes
 *     <li>$table->wrap - An array of "nowrap"s or nothing
 *     <li>$table->data[] - An array of arrays containing the data.
 *     <li>$table->width  - A percentage of the page
 *     <li>$table->tablealign  - Align the whole table
 *     <li>$table->cellpadding  - Padding on each cell
 *     <li>$table->cellspacing  - Spacing between cells
 *****************  NEW (added by shtifanov) **********************
 *     <li>$table->downloadfilename  - .XLS file name (new)
 *     <li>$table->titles  - An array of titles names in firsts rows. (new)
 *     <li>$table->titlesrows  - Height of titles rows (new)
 *     <li>$table->columnwidth  - An array of columns width in Excel table (new)
 * </ul>
 * @param bool $return whether to return an output string or echo now
 * @return boolean or $string
 * @todo Finish documenting this function
 */

function print_table_to_excel_old($table, $lastcols = 0)
{
    global $CFG;

    $downloadfilename = $table->downloadfilename;

    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"{$downloadfilename}.xls\"");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    $workbook = new Workbook("-");

    $myxls = &$workbook->add_worksheet($table->worksheetname);

	$numcolumn = count ($table->columnwidth) - $lastcols;
    $i=0;
    foreach ($table->columnwidth as $width)	{
		$myxls->set_column($i, $i, $width);
		$i++;
	}

	$formath1 =& $workbook->add_format();
	$formath1->set_size(12);
    $formath1->set_align('center');
    $formath1->set_align('vcenter');
	$formath1->set_color('black');
	$formath1->set_bold(1);
	$formath1->set_italic();
	// $formath1->set_border(2);

    $txtl = new textlib();
    $i = 0;
    foreach ($table->titles as $key => $title)	{
		$myxls->set_row($i, $table->titlesrows[$key]);
		$strwin1251 =  $txtl->convert($title, 'utf-8', 'windows-1251');
	    $myxls->write_string($i, 0, $strwin1251, $formath1);
		$myxls->merge_cells($i, 0, $i, $numcolumn-1);
		$i++;
    }

	$formath2 =& $workbook->add_format();
	$formath2->set_size(11);
    $formath2->set_align('center');
    $formath2->set_align('vcenter');
	$formath2->set_color('black');
	$formath2->set_bold(1);
	//$formath2->set_italic();
	$formath2->set_border(2);
	$formath2->set_text_wrap();

    if (!empty($table->head)) {
    	$formatp = array();
    	$numcolumn = count ($table->head) - $lastcols;
        foreach ($table->head as $key => $heading) {
        	if ($key >= $numcolumn) continue;
	   		$strwin1251 =  $txtl->convert(strip_tags($heading), 'utf-8', 'windows-1251');
	        $myxls->write_string($i, $key,  $strwin1251, $formath2);

			$formatp[$key] =& $workbook->add_format();
			$formatp[$key]->set_size(10);
		    $formatp[$key]->set_align($table->align[$key]);
		    $formatp[$key]->set_align('vcenter');
			$formatp[$key]->set_color('black');
			$formatp[$key]->set_bold(0);
			$formatp[$key]->set_border(1);
			$formatp[$key]->set_text_wrap();
        }
        $i++;
    }

    // $workbook->close(); return 0;

    foreach ($table->data as $keyrow => $row) {
      	$numcolumn = count ($row) - $lastcols;
        foreach ($row as $keycol => $item) 	{
           	if ($keycol >= $numcolumn) continue;
        	$clearitem = strip_tags($item);
 			$strwin1251 =  $txtl->convert($clearitem, 'utf-8', 'windows-1251');
			$myxls->write($i + $keyrow, $keycol,  $strwin1251, $formatp[$keycol]);
		}
    }

    $workbook->close();
}

function print_dean_box_start($align='', $width='', $color='', $padding=5, $class='generalbox', $id='') {

    if ($color) {
        $color = 'bgcolor="'. $color .'"';
    }
    if ($align) {
        $align = 'align="'. $align .'"';
    }
    if ($width) {
        $width = 'width="'. $width .'"';
    }
    if ($id) {
        $id = 'id="'. $id .'"';
    }
    // echo "<table $align $width $id  border=\"0\" cellpadding=\"$padding\" cellspacing=\"0\">";
    echo "<table $align $width cellspacing=\"0\" cellpadding=\"$padding\"  align=\"center\" class=\"generaltable generalbox\">";
    echo "<tr><td  "."content\">";
}

/**
 * Print the end portion of a standard themed box.
 */
function print_dean_box_end() {
    echo '</td></tr></table>';
}


/**
 * FAST ANALOG enrol_student
 * This function makes a role-assignment (a role for a user or group in a particular context)
 * @param $roleid - the role of the id
 * @param $userid - userid
 * @param $groupid - group id
 * @param $contextid - id of the context
 * @param $timestart - time this assignment becomes effective
 * @param $timeend - time this assignemnt ceases to be effective
 * @uses $USER
 * @return id - new id of the assigment
 */
function role_assign_dean($roleid, $userid, $groupid, $contextid, $timestart=0, $timeend=0, $hidden=0, $enrol='manual',$timemodified='') {
    global $USER, $CFG;

    if (!$timemodified) {
        $timemodified = time();
    }

/// Check for existing entry
    if ($userid) {
        $ra = get_record('role_assignments', 'roleid', $roleid, 'contextid', $contextid, 'userid', $userid);
    } else {
    	return false;
        // $ra = get_record('role_assignments', 'roleid', $roleid, 'contextid', $context->id, 'groupid', $groupid);
    }

    if (empty($ra)) {             // Create a new entry
        $ra = new object();
        $ra->roleid = $roleid;
        $ra->contextid = $contextid;
        $ra->userid = $userid;
        $ra->hidden = $hidden;
        $ra->enrol = $enrol;
    /// Always round timestart downto 100 secs to help DBs to use their own caching algorithms
    /// by repeating queries with the same exact parameters in a 100 secs time window
        $ra->timestart = round($timestart, -2);
        $ra->timeend = $timeend;
        $ra->timemodified = $timemodified;
        $ra->modifierid = empty($USER->id) ? 0 : $USER->id;

        if (!$ra->id = insert_record('role_assignments', $ra)) {
            return false;
        }

    } else {                      // We already have one, just update it
        $ra->id = $ra->id;
        $ra->hidden = $hidden;
        $ra->enrol = $enrol;
    /// Always round timestart downto 100 secs to help DBs to use their own caching algorithms
    /// by repeating queries with the same exact parameters in a 100 secs time window
        $ra->timestart = round($timestart, -2);
        $ra->timeend = $timeend;
        $ra->timemodified = $timemodified;
        $ra->modifierid = empty($USER->id) ? 0 : $USER->id;

        if (!update_record('role_assignments', $ra)) {
            return false;
        }
    }

    return true;
}


/**
 * FAST ANALOG unenrol_student
 * Deletes one or more role assignments.   You must specify at least one parameter.
 * @param $roleid
 * @param $userid
 * @param $groupid
 * @param $contextid
 * @param $enrol unassign only if enrolment type matches, NULL means anything
 * @return boolean - success or failure
 */
function role_unassign_dean($roleid, $userid, $contextid, $courseid) {
    global $USER, $CFG;

	if (!$success = delete_records_select('role_assignments', "roleid=$roleid AND userid=$userid AND contextid= $contextid"))	{
		notify('!!!> Not deleted role_assignments.');
	}

    $usersql = "AND userid = $userid";
    $groupssql = "SELECT id FROM {$CFG->prefix}groups g WHERE g.courseid = $courseid";
    if (!$success = delete_records_select('groups_members', "groupid IN ($groupssql) $usersql"))	{
		notify('!!!> Not deleted groups_members.');
    }

	// delete lastaccess records
	if (!$success = delete_records_select('user_lastaccess', "userid=$userid AND courseid=$courseid"))	{
		notify('!!!> Not deleted user_lastaccess.');
	}

    return $success;
}

/**
 * Enrols (or re-enrols) a student in a given course
 *
 * NOTE: Defaults to 'manual' enrolment - enrolment plugins
 * must set it explicitly.
 *
 * @uses $CFG
 * @param int $userid The id of the user that is being tested against. Set this to 0 if you would just like to test against the currently logged in user.
 * @param int $courseid The id of the course that is being viewed
 * @param int $timestart ?
 * @param int $timeend ?
 * @param string $enrol ?
 * @return bool
 */
function enrol_student_dean($userid, $courseid) {

    global $CFG;

    if (!$context = get_context_instance(CONTEXT_COURSE, $courseid)) {
        return false;
    }

    $res = role_assign_dean(5, $userid, 0, $context->id, 0, 0, 0, 'manual');

    return $res;
}


/**
 * Unenrols a student from a given course
 *
 * @param int $courseid The id of the course that is being viewed, if any
 * @param int $userid The id of the user that is being tested against.
 * @return bool
 */
function unenrol_student_dean($userid, $courseid=0) {
    global $CFG;

    $status = true;

    if ($courseid) {
        /// First delete any crucial stuff that might still send mail
        if ($forums = get_records('forum', 'course', $courseid)) {
            foreach ($forums as $forum) {
                delete_records('forum_subscriptions', 'forum', $forum->id, 'userid', $userid);
            }
        }
        /// remove from all legacy student roles
        if ($courseid == SITEID) {
            $context = get_context_instance(CONTEXT_SYSTEM);
        } else if (!$context = get_context_instance(CONTEXT_COURSE, $courseid)) {
            return false;
        }

        $status = role_unassign_dean(5, $userid, $context->id, $courseid) and $status;
        //  print_r($status); echo '<hr>';

    } else {
        // recursivelly unenroll student from all courses
        //if ($courses = get_records('course', '', '', '', 'id')) {
        if ($coursesids = get_records_sql("SELECT c.id FROM {$CFG->prefix}course c
                    JOIN {$CFG->prefix}context ctx
                      ON (c.id=ctx.instanceid AND ctx.contextlevel=".CONTEXT_COURSE.")
                    JOIN {$CFG->prefix}role_assignments ra
                      ON (ra.contextid=ctx.id AND ra.userid=$userid)"))	{
			// print_r($coursesids);

            foreach($coursesids as $course) {
                $status = unenrol_student_dean($userid, $course->id) and $status;
            }

        }
    }
    return $status;
}

function delete_user_dean($user) {
    global $CFG;
    require_once($CFG->libdir.'/grouplib.php');
    require_once($CFG->libdir.'/gradelib.php');
    require_once($CFG->dirroot.'/message/lib.php');

    begin_sql();

    // delete all grades - backup is kept in grade_grades_history table
    if ($grades = grade_grade::fetch_all(array('userid'=>$user->id))) {
        foreach ($grades as $grade) {
            $grade->delete('userdelete');
        }
    }

    //move unread messages from this user to read
    message_move_userfrom_unread2read($user->id);

    // remove from all groups
    delete_records('groups_members', 'userid', $user->id);

    // unenrol from all roles in all contexts
    // role_unassign(0, $user->id); // this might be slow but it is really needed - modules might do some extra cleanup!

    unenrol_student_dean($user->id);

    // now do a final accesslib cleanup - removes all role assingments in user context and context itself
    delete_context(CONTEXT_USER, $user->id);

    require_once($CFG->dirroot.'/tag/lib.php');
    tag_set('user', $user->id, array());

    // workaround for bulk deletes of users with the same email address
    $delname = addslashes("$user->email.".time());
    while (record_exists('user', 'username', $delname)) { // no need to use mnethostid here
        $delname++;
    }

    // mark internal user record as "deleted"
    $updateuser = new object();
    $updateuser->id           = $user->id;
    $updateuser->deleted      = 1;
    $updateuser->username     = $delname;         // Remember it just in case
    $updateuser->email        = '';               // Clear this field to free it up
    $updateuser->idnumber     = '';               // Clear this field to free it up
    $updateuser->timemodified = time();

    if (update_record('user', $updateuser)) {
        commit_sql();
        // notify auth plugin - do not block the delete even when plugin fails
        $authplugin = get_auth_plugin($user->auth);
        $authplugin->user_delete($user);

        // events_trigger('user_deleted', $user);
        return true;

    } else {
        rollback_sql();
        return false;
    }
}

// Display list faculty as popup_form
function listbox_date($scriptname, $uts)
{
  global $CFG;

  $utsmenu = array();
  $utsmenu[0] = get_string('selectadate', 'block_dean') . '...';
  $utsmenu[1] = get_string('allperiod', 'block_dean');

  $time1 = make_timestamp(2011, 04, 11);

  for ($i=0; $i<=95; $i++)	{
	$utsmenu[$time1] = date("d.m.Y", $time1);
	$time1 += DAYSECS;
  }

  echo '<tr> <td>'.get_string('dateofgive', 'block_dean').': </td><td>';
  popup_form($scriptname, $utsmenu, 'switchdate', $uts, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


function print_row($left, $right, $aright ='right', $aleft = 'left')
{
    echo "\n<tr><td align=\"$aright\" valign=\"top\" class=\"label c0\" nowrap=\"nowrap\">$left</td>
                <td align=\"$aleft\"  valign=\"top\" class=\"info c1\">$right</td></tr>\n";
}


function get_count_print($uid, $fid=0, $oid=0)
{
	if($fid == 0) {
		$fid = get_record_select('dean_methodist', "userid=$uid", 'facultyid, otdelenie');
		$oid = $fid->otdelenie;
		$fid = $fid->facultyid;
	}

    $academygroups = get_records_select('dean_academygroups', "facultyid=$fid AND idotdelenie in ($oid)", '', 'id, name');
    $groupids[] = 0;
    if($academygroups) {
	    foreach ($academygroups as $academygroup)   {
	        $groupids[] = $academygroup->id;
	    }
    }
    $listgroupids = implode(',', $groupids);

    $count = get_record_select('dean_service', "groupid in ($listgroupids) AND printedtime=0", 'sum(count) as count');
    if($count) {
        return $count->count;
    } else {
        return 0;
    }
}

function get_data_pupils($fid, $oid)
{
//    $facultyid = get_record_select('dean_methodist', "userid=$uid", 'facultyid, otdelenie');
    $academygroups = get_records_select('dean_academygroups', "facultyid=$fid AND idotdelenie in ($oid)", '', 'id, name');
    $groupids[] = 0;
    if($academygroups) {
	    foreach ($academygroups as $academygroup)   {
	        $groupids[] = $academygroup->id;
	    }
    }
    $listgroupids = implode(',', $groupids);

    $count = get_records_select('dean_service', "groupid in ($listgroupids) AND printedtime=0", '', 'id, groupid, userid, serviceid, count, predyavlenie, stip, sessionid, fic, did, idotdelenie');
    if($count) {
        return $count;
    } else {
        return 0;
    }
}

function get_numberser($facultyid, $serviceid, $id=0, $jobid, $ido=0)
{
	GLOBAL $USER;
	if($serviceid == 2)  {
		$count = get_record_select('dean_service_number', "facultyid=$facultyid AND serviceid=$serviceid AND idotdelenie=$ido", 'count(id) as count');
	} else {
		$count = get_record_select('dean_service_number', "facultyid=$facultyid", 'count(id) as count');
	}

	$count = $count->count;
	$count++;

	$new->facultyid = $facultyid;
	$new->serviceid = $serviceid;
	$new->userprintedid = $USER->id;
	$new->number = $count;
	$new->jobid = $jobid;
	$new->idotdelenie = $ido;
	$new->keyid = $id;
	$new->timemodified = time();
	insert_record('dean_service_number', $new);

	return $count;
}

function savetoserver($filename, $somecontent) {
	if(file_exists($filename)) unlink($filename);

//	$textlib = textlib_get_instance();
//	$somecontent = $textlib->convert($somecontent, 'win1251');
	if(!$handle = fopen($filename, 'x')) {
//		echo "Ошибка открытия файла! ($filename)";
//		echo '<br /><br /><a href="index.php">На главную</a>';
		exit();
	}
	if(!fwrite($handle, $somecontent)) {
//		echo "Ошибка записи в файл ($filename)";
//		echo '<br /><br /><a href="index.php">На главную</a>';
		exit();
	}

	fclose($handle);
}

function print_date_monitoring($day, $month, $year, $currenttime=0, $howold=10, $disabled=false) {

    if (!$currenttime) {
        // $currenttime = time();
        $days[0] = '-';
        $months[0] = '-';
        $years[0] = '-';
        $currentdate['mday'] = 0;
        $currentdate['mon'] = 0;
        $currentdate['year'] = 0;

    }  else {
	    $currentdate = usergetdate($currenttime);
	}

    for ($i=1; $i<=31; $i++) {
        $days[$i] = $i;
    }
    for ($i=1; $i<=9; $i++) {
        $months[$i] = get_string('lm_0'.$i, 'block_dean'); // userdate(gmmktime(12,0,0,$i,1,2000), "%B");
    }
    for ($i=10; $i<=12; $i++) {
        $months[$i] = get_string('lm_'.$i, 'block_dean');
    }
    $curryear = date("Y");
    for ($i=($curryear-$howold); $i<=($curryear+10); $i++) {
        $years[$i] = $i;
    }
    choose_from_menu($days,   $day,   $currentdate['mday'], '', '', 0, false, $disabled);
    choose_from_menu($months, $month, $currentdate['mon'],  '', '', 0, false, $disabled);
    choose_from_menu($years,  $year,  $currentdate['year'], '', '', 0, false, $disabled);
}


// Create date with month and date
function get_timestamp_from_date($d, $m, $y)
{
    $t = make_timestamp($y, $m, $d, 12);
    return $t;
}

// Display list disciplines as popup_form
function listbox_bsu_discipline($scriptname, $bdid)
{
  global $CFG;

  $strtitle = get_string('selectadiscipline', 'block_dean') . ' ...';
  $disciplinemenu = array($strtitle);
/*
  $arr_discipline = get_records_sql ("SELECT DISTINCT a.DisciplineNameId, b.Name FROM mdl_dean_schedule a
                                      INNER JOIN mdl_bsu_ref_disciplinename b ON  a.DisciplineNameId=b.Id
                                      ORDER BY b.Name;");
*/
  $arr_discipline = get_records_sql ("SELECT Id, Name FROM mdl_bsu_ref_disciplinename
                                      ORDER BY Name;");

  if ($arr_discipline) 	{
		foreach ($arr_discipline as $ds) {
			//$disciplinemenu[$ds->DisciplineNameId] =$ds->Name;
            $disciplinemenu[$ds->Id] =$ds->Name;
		}
  }


  echo '<tr><td>'.get_string('discipline', 'block_dean').':</td><td>';
  popup_form($scriptname, $disciplinemenu, 'switchbsudiscipline', $bdid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

function get_listgroup_id_faculty($fid, $oid = '') {
	if($oid == '') {
		$groupsids = get_records_select('dean_academygroups', "facultyid=$fid", '', 'id');
	} else {
		$groupsids = get_records_select('dean_academygroups', "facultyid=$fid AND idotdelenie IN ($oid)", '', 'id');
	}

	if($groupsids) {
		foreach($groupsids as $groupsid) {
			$id[] = $groupsid->id;
		}
	}
	$id = implode(',', $id);
    return $id;
}

function get_listgroup_id_speciality($sid) {
	$groupsids = get_records_select('dean_academygroups', "specialityid=$sid", '', 'id');

	if($groupsids) {
		foreach($groupsids as $groupsid) {
			$id[] = $groupsid->id;
		}
	}
	$id = implode(',', $id);
    return $id;
}


function get_items_menu (&$items, &$icons)
{
    global $CFG, $USER;

	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();

	if(!$teacher_is) {
		$teacher_is = isteacher_dean();
	}

	$methodist_is =  ismethodist();
	$dean_is = isdean();
    //$student_is = ispupil();

    if ($USER->id == 59682 || $USER->id == 1835) {
        $admin_is = true;
    }

    $student_is = isstudentinanycourse();

    $items['faculty'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/faculty/faculty.php">'.get_string('faculty', 'block_dean').'</a>';
    $icons['faculty'] = '<img src="'.$CFG->pixpath.'/i/db.gif" height="16" width="16" alt="" />';

    $items['speciality'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/speciality/speciality.php?id=0">'.get_string('speciality', 'block_dean').'</a>';
    $icons['speciality'] = '<img src="'.$CFG->pixpath.'/i/news.gif" height="16" width="16" alt="" />';

	$items['curriculum'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/curriculum/curriculum.php?mode=1&fid=0&sid=0">'.get_string('curriculums','block_dean').'</a>';
	$icons['curriculum'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/wikiicon.gif" height="16" width="16" alt="" />';

	$items['academygroup'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/groups/academygroup.php?mode=1&fid=0&sid=0&cid=0">'.get_string('groups').'</a>';
    $icons['academygroup'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/groups.gif" height="16" width="16" alt="" />';

	$items['lstgroupmember'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/gruppa/lstgroupmember.php?mode=1&fid=0&sid=0&cid=0&gid=0">'.get_string('group').'</a>';
    $icons['lstgroupmember'] = '<img src="'.$CFG->pixpath.'/i/group.gif" height="16" width="16" alt="" />';

    $items['student'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/student/student.php?mode=1&fid=0&sid=0&cid=0&gid=0">'.get_string('student','block_dean').'</a>';
    $icons['student'] = '<img src="'.$CFG->pixpath.'/i/guest.gif" height="16" width="16" alt="" />';

/*
    $items['journalgroup'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/journal/journalgroup.php">'.get_string('journalgroup','block_dean').'</a>';
    $icons['journalgroup'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/journal.gif" height="16" width="16" alt="" />';
*/
	$items['quiz_index'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/quiz/index.php">'.get_string('schedule', 'block_dean')."</a>";
	$icons['quiz_index'] = '<img src="'.$CFG->pixpath.'/i/course.gif" height="16" width="16" alt="" />';

	$text = '';
    if($methodist_is) {
		$count = get_count_print($USER->id);
		if($count) $text = "<b>($count)</b>";
    }

    $items['service_index'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/service/index.php">'.get_string('service', 'block_cdoservice')."$text</a>";
	$icons['service_index'] = '<img src="'.$CFG->pixpath.'/i/outcomes.gif" height="16" width="16" alt="" />';

    $items['rolls'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/rolls/rolls.php?mode=1&fid=0&sid=0&cid=0&gid=0&did=0">'.get_string('rolls','block_dean').'</a>';
    $icons['rolls'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/journal.gif" height="16" width="16" alt="" />';

    $items['zachbooks'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/student/zachbooks.php?mode=1&fid=0&sid=0&cid=0&gid=0&did=0&uid=0">'.get_string('zachbook','block_dean').'</a>';
    $icons['zachbooks'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/journal.gif" height="16" width="16" alt="" />';

    $items['curatorsgroups'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/journal/curatorsgroups.php">'.get_string('curatorsgroups','block_dean').'</a>';
    $icons['curatorsgroups'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/curators.gif" height="16" width="16" alt="" />';

    $items['reports'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/reports/reports.php?mode=1&fid=0&gid=0">'.get_string('reports','block_dean').'</a>';
    $icons['reports'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/journal.gif" height="16" width="16" alt="" />';

    $items['searchgroup'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/groups/searchgroup.php">'.get_string('searchgroup', 'block_dean').'</a>';
    $icons['searchgroup'] = '<img src="'.$CFG->pixpath.'/i/search.gif" height="16" width="16" alt="" />';

    $items['searchstudent'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/student/searchstudent.php">'.get_string('searchstudent', 'block_dean').'</a>';
    $icons['searchstudent'] = '<img src="'.$CFG->pixpath.'/i/search.gif" height="16" width="16" alt="" />';

    $items['ldapsync'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/synchronization/ldapsync.php">'.get_string('ldapsynchronization','block_dean').'</a>';
    $icons['ldapsync'] = '<img src="'.$CFG->pixpath.'/i/switch.gif" height="16" width="16" alt="" />';

    $items['methodists'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/faculty/methodists.php">'.get_string('methodists','block_dean').'</a>';
    $icons['methodists'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/curators.gif" height="16" width="16" alt="" />';

    $items['import_ct'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/import_ct.php">'.get_string('import_ct','block_dean').'</a>';
    $icons['import_ct'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/journal.gif" height="16" width="16" alt="" />';

    $items['examtoexec'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/reports/examtoexec.php">'.get_string('examtoexec','block_dean').'</a>';
    $icons['examtoexec'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/journal.gif" height="16" width="16" alt="" />';

    $items['clearpegas'] = '<a href="'.$CFG->wwwroot.'/blocks/dean/clear/clearpegas.php">'.get_string('clearpegas','block_dean').'</a>';
    $icons['clearpegas'] = '<img src="'.$CFG->wwwroot.'/blocks/dean/i/risk_xss.gif" height="16" width="16" alt="" />';


    // echo $admin_is . 'a<hr>' . $creator_is . 'c<hr>' .  $methodist_is . 'm<hr>' . $dean_is . 'd<hr>' . $teacher_is . 't<hr>';
    $index_items = array();

    if (isloggedin())   {
        $idx = array('service_index', 'quiz_index');
        $index_items = array_merge ($index_items, $idx);
    }

    if ($student_is)    {
        $idx = array('service_index', 'quiz_index');
        $index_items = array_merge ($idx, $index_items);
    }

    if ($teacher_is)  {
        $idx = array('faculty', 'speciality', 'curriculum', 'academygroup',
                    'lstgroupmember', 'student', 'quiz_index',
                    'service_index');
       $index_items = array_merge ($idx, $index_items);

	}
    if ($dean_is) {
        $idx = array('faculty', 'speciality', 'curriculum', 'academygroup',
                            'lstgroupmember', 'student', 'quiz_index',
                            'service_index', 'rolls', 'zachbooks');
        $index_items = array_merge ($idx, $index_items);
    }

    if ($methodist_is)  {
        $idx = array('faculty', 'speciality', 'curriculum', 'academygroup',
                            'lstgroupmember', 'student', 'quiz_index',
                            'service_index', 'rolls', 'zachbooks', 'curatorsgroups', 'reports',
                            'searchgroup', 'searchstudent', 'ldapsync');
       $index_items = array_merge ($idx, $index_items);
    }

    if ($creator_is)  {
        $idx = array('faculty', 'speciality', 'curriculum', 'academygroup',
                            'lstgroupmember', 'student', 'quiz_index',
                            'service_index', 'rolls', 'zachbooks', 'curatorsgroups', 'reports',
                            'searchgroup', 'searchstudent', 'ldapsync');
       $index_items = array_merge ($idx, $index_items);
    }

    if ($admin_is)	 {
        $idx = array('faculty', 'speciality', 'curriculum', 'academygroup',
                            'lstgroupmember', 'student', 'quiz_index',
                            'service_index', 'rolls', 'zachbooks', 'curatorsgroups', 'reports',
                            'searchgroup', 'searchstudent', 'methodists',
                            'ldapsync', 'import_ct', 'examtoexec', 'clearpegas');
       $index_items = array_merge ($idx, $index_items);
    }

    $index_items = array_unique($index_items);

    return $index_items;
}

function isteacher_dean ($userid=0) {
    global $USER;

    if (empty($USER->id)) {
        return false;
    }

    if (empty($userid))  {
        $userid = $USER->id;
    }

    $ret = false; // id, academygroupid, userid
    if ($user = get_record_sql("SELECT id, auth FROM mdl_user WHERE id=$userid"))   {
         // print_r ($USER);
        if($user->auth === 'ldap2') $ret = true;
    }

    return $ret;
}

function enrol_academygroup_to_course($agroupname, $courseid)
{
   global $CFG;

   if ($academygroup = get_record_select('dean_academygroups', "name = '$agroupname'", 'id, name')) 	{

        if ($newgrp = get_record_select('groups', "name = '$academygroup->name' and courseid = $courseid", 'id'))	{
	    	notify (get_string('groupalreadyenroll', 'block_dean', $academygroup->name) . $courseid, 'black');
	    	return;
        }  else {
   	        $newgroup->name = $academygroup->name;
       	    $newgroup->courseid = $courseid;
			$newgroup->description = '';
			$newgroup->password = '';
			$newgroup->theme = '';
           	$newgroup->lang = current_language();
            $newgroup->timecreated = time();
            if (!$newgrpid = insert_record("groups", $newgroup)) {
   	            notify("Could not insert the new group '$newgroup->name'");
                return;
       	    }
       	}

        if ($academystudents = get_records_select('dean_academygroups_members', "academygroupid = $academygroup->id" , 'id, userid'))	{

		    foreach ($academystudents as $astud)	  {
                /// Enrol student
			    if ($usr = get_record_select('user', "id = $astud->userid", 'id, lastname, firstname, deleted'))	{
				    // print '-'.$usr->id.':'.$usr->lastname.' '. $usr->firstname. '<br>';
				    if ($usr->deleted != 1)	 {
						if (!enrol_student_dean($astud->userid, $courseid))  {
	 	                     notify("Could not add student with id $astud->userid to the course $rec->courseid!");
			            }
			        } else {
		 	 	      	delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
		  	 	    }
 			    } else {
        			delete_records('dean_academygroups_members', 'userid', $astud->userid, 'academygroupid', $academygroup->id);
	     		}

            	 /// Add people to a group
			     $newmember->groupid = $newgrpid;
	             $newmember->userid = $astud->userid;
    	         $newmember->timeadded = time();
        	     if (!insert_record('groups_members', $newmember)) {
            	    notify("Error occurred while adding user $astud->userid to group $academygroup->name");
                 }
            }
	    }
    }
}

function isstudentinanycourse($userid=0) {
    global $USER, $CFG;

    if (empty($CFG->rolesactive)) {     // Teachers are locked out during an upgrade to 1.7
        return false;
    }

    if (!$userid) {
        if (empty($USER->id)) {
            return false;
        }
        $userid = $USER->id;
    }

    if (record_exists('role_assignments', 'roleid', 5, 'userid', $userid)) {    // Has no student roles anywhere
        return true;
    }

    return false;
}


function get_idotdelenie_group($namegroup)
{
    $idotdelenie = 0;
    $strsql = "SELECT distinct grup, idOtdelenie FROM mdl_bsu_students where grup = '$namegroup'";
    if ($bsugroup = get_record_sql($strsql))    {
        $idotdelenie = $bsugroup->idOtdelenie;
    } else {
    	$len = strlen ($namegroup);
    	$numgroup = substr($namegroup, -2);
    	if ($len == 6 && $numgroup < 50)	{
    	    $idotdelenie = 2;
        } else if ($numgroup > 50 && $numgroup <100 ) {
            $idotdelenie =  3;
        }
    }

    return $idotdelenie;
}

function get_current_edyearid()
{
    $year = date("Y");
    $m = date("n");
    if(($m >= 1) && ($m <= 8 )) {
		$y = $year-1;
    } else {
		$y = $year;
    }

	if ($year = get_record_select('bsu_ref_edyear', "God = $y", 'id'))	{
  		return $year->id;
	} else {
  		return 12;
	}
}

function get_current_sessionid($gid)
{
	$g = get_record_select('dean_academygroups', "id=$gid", 'name', '');
	$g = substr($g->name, 0, 4);
	$year = date("Y");
	$m = date("n");
	switch($m) {
		case 8:
			if($g == $year) $session = 1; else $session = 2;
		break;
		case 9: case 10: case 11: case 12: case 1:
			$session = 3;
		break;
		case 2: case 3: case 4:
			$session = 4;
		break;
		case 5: case 6: case 7:
			$session = 5;
		break;
	}
	return $session;
}


function isheaddepartment() {
	GLOBAL $USER, $CFG;

	$user = get_record_select('user', "id=$USER->id", 'id, lastname, firstname');
	$fullname = fullname($user);
	$fullname_upper = mb_strtoupper($fullname, 'UTF-8');
	$result = 0;
	if($staff = get_record_select('bsu_staffform', "name='$fullname_upper' AND subdepartmentid>292 AND departmenthead=1", 'id, subdepartmentid')) {
		$result = $staff->subdepartmentid;
	} else {
		if($staffs = get_records_select('dean_teacher_card', "userid=$USER->id", '', 'id, staffid')) {
			foreach($staffs as $staff) $a[] = $staff->staffid;
			$s = implode(',', $a);
			if($staff = get_record_select('bsu_staffform', "id IN ($s) AND subdepartmentid>292 AND departmenthead=1", 'id, subdepartmentid'))
				$result = $staff->subdepartmentid;
		}
	}
/*
	if($staff = get_record_select('bsu_staffform', "name='$fullname_upper' AND subdepartmentid>292", 'subdepartmentid'))
		if($user = get_record_select('bsu_contracts', "name='$fullname' AND idstaff_appointments=7", 'id')) $result = $staff->subdepartmentid;
*/
	return $result;
}

function dativecase($lastname, $firstname, $secondname, $sex=-1)
{
    global $user;

	$lastname = trim($lastname);
	$firstname = trim($firstname);
	$secondname = trim($secondname);

	if (!empty($lastname) && !empty($firstname) && !empty($secondname)) {
		if($sex == -1) {
			$sex = 0;
			if (mb_substr($secondname, -1, 1, 'UTF-8') == 'ч')	{
				$sex = 1;
			}
		}
		if ($sex == 1)	{
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

        $user->lastnamedative = $lastname;
        $user->firstnamedative = $firstname . ' ' . $secondname;
		return "$lastname $firstname $secondname";
	}
}

function listbox_subdepartment($scriptname, $subid, $fid)
{
  global $CFG;

  if(count($fid) == 1) $fid='0'.$fid;

  $menu = array();
  $menu[0] = get_string('selectasubdepartment', 'block_dean').'...';

  if($alls = get_records_sql("SELECT id, name FROM {$CFG->prefix}bsu_ref_subdepartment WHERE name LIKE '05$fid%' ORDER BY name"))   {
		foreach ($alls as $data) 	{
			$menu[$data->id] =$data->name;
		}
  }

  echo '<tr> <td>'.get_string('department', 'block_dean').': </td><td>';
  popup_form($scriptname, $menu, 'switchdep', $subid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}
?>
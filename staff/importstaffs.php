<?php // $Id: importstaffs.php,v 1.5 2012/03/27 06:00:13 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_school/lib_school.php');    
    require_once('../lib_att2.php');
	require_once($CFG->dirroot.'/lib/uploadlib.php');

	// define('ROLE_NON_EDITING_TEACHER', 4);

    $rid = optional_param('rid', 0, PARAM_INT);       // Rayon id
    $oid = optional_param('oid', 0, PARAM_INT);       // OU id
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
    $typeou = optional_param('typeou', '-');       // Type OU
    $stft = optional_param('stft', 0, PARAM_INT);  	  // Type stafftype id
	$action   = optional_param('action', '');
    
	$currentyearid = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $currentyearid;
    }

	$strlistrayons =  listbox_rayons_att("importstaffs.php?oid=0&yid=$yid&rid=", $rid);
	$strlisttypeou =  listbox_typeou_att("importstaffs.php?rid=$rid&yid=$yid&oid=0&typeou=", $rid, $typeou);

	if (!$strlistrayons && !$strlisttypeou)   { 
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$strtitle = get_string('title2','block_mou_att');
	$struser = get_string('user');
    $strstaffs = get_string('importstaff', 'block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strstaffs, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strstaffs, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	echo $strlistrayons;
	echo $strlisttypeou;

	if ($typeou != '-')	{
		if ($strlistou = listbox_ou_att("importstaffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=", $rid, $typeou, $oid, $yid))	{ 
			echo $strlistou;
		} else {
			echo '</table>';
			notice(get_string('ounotfound', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid");
		}	
	} 
	echo '</table>';

//    print_heading('Страница в стадии доработки.', 'center', 3);
//    exit();

	if ($rid != 0 && $oid != 0 && $typeou != '-')   {

		$role_sotrudnik = get_record('role', 'shortname', 'sotrudnik');    
        $edutype = get_config_typeou($typeou);
        
    	$context = get_context_instance($edutype->context, $oid);
        $edit_capability = has_capability('block/mou_att2:editattestationuser', $context);
        

    	if ($edit_capability )	{
    	
           	$currenttab = 'importstaff';
            include('tabs.php');

    		$um = new upload_manager('userfile',false,false,null,false,0);
    		$f = 0;
    		if ($um->preprocess_files()) {
    			$filename = $um->files['userfile']['tmp_name'];
                $rayon = get_record_select('monit_rayon', "id = $rid", 'id, name');
                importstaff($filename);
            }    
                
        	/// Print the form
            $struploadusers = get_string('importstaffschool', 'block_mou_att', '');
            print_heading_with_help($struploadusers, 'importstaff', 'mou');
            $struploadusers = get_string('importstaff', 'block_mou_att');
        
            $maxuploadsize = get_max_upload_file_size();
        	$strchoose = ''; // get_string("choose"). ':';
        
            echo '<center>';
            echo '<form method="post" enctype="multipart/form-data" action="importstaffs.php">'.
                 $strchoose.'<input type="hidden" name="MAX_FILE_SIZE" value="'.$maxuploadsize.'">'.
                 '<input type="hidden" name="rid" value="'.$rid.'">'.
                 '<input type="hidden" name="oid" value="'.$oid.'">'.
                 '<input type="hidden" name="yid" value="'.$yid.'">'.
                 '<input type="hidden" name="stft" value="'.$stft.'">'.
                 '<input type="hidden" name="typeou" value="'.$typeou.'">'.         
                 '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'">';
       
    	    echo '<input type="file" name="userfile" size="100">'.
    	         '<br><input type="submit" value="'.$struploadusers.'">'.
    	         '</form>';
    	    echo '</center>';
            
            print_help_import();
        }    
    }
    print_footer();



function importstaff($filename)
{
    global $CFG, $rid, $yid, $oid, $typeou, $rayon, $edutype, $context, $role_sotrudnik;

    @set_time_limit(0);
    @raise_memory_limit("192M");
    if (function_exists('apache_child_terminate')) {
        @apache_child_terminate();
    }

    $csv_delimiter = ';';
    $usersnew = 0;
    $userserrors  = 0;
    $linenum = 2; // since header is line 1
    $redirlink = "importstaffs.php?rid=$rid&yid=$yid&typeou=$typeou&oid=$oid";
    $ou = get_record_select($edutype->tblname, "id=$oid", 'id, name');
    if ($stafftypes = get_records_select('monit_att_stst', "edutypeid = $edutype->id", 'id'))   {
        $stafftype = current($stafftypes);
    } else {
        $stafftype->stafftypeid = 0;
    }    
    
    // print_r($stafftype); echo '<br>';
    
    $coursecontext = get_context_instance(CONTEXT_COURSE, 1);
    
    $teachersql = "SELECT u.id, u.username, u.firstname, u.lastname
                  FROM {$CFG->prefix}user u
	              LEFT JOIN {$CFG->prefix}monit_att_staff s ON s.userid = u.id
 	              WHERE s.{$edutype->idname}=$oid AND u.deleted = 0 AND u.confirmed = 1";
	$tezki = array();

	if ($teachers = get_records_sql($teachersql))	{
        foreach ($teachers as $teacher)  {
        	$tezki[] = mb_strtolower ($teacher->lastname . ' '. $teacher->firstname, 'UTF-8');
        }
	}

    $text = file($filename);
	if($text == FALSE){
		error(get_string('errorfile', 'block_monitoring'), $redirlink);
	}
	$size = sizeof($text);

	$textlib = textlib_get_instance();
  	for($i=0; $i < $size; $i++)  {
		$text[$i] = $textlib->convert($text[$i], 'win1251');
    }   
    unset ($textlib);

    $required = array( "lastname" => 1, "firstname" => 1); // "email" => 1, 'phone1' => 1, 'city' => 1, 'description' => 1, 'idschool' => 1);
    
    $header = split($csv_delimiter, $text[0]); // --- get and check header (field names) ---
    // print_r($header); echo '<hr>';
    translate_header($header);
    
	echo 'login;password;lastname;firstname;email<br>';

	$userfields = array('lastname', 'firstname', 'email', 'phone1');
    $stafffields = array('birthday', 'graduate', 'whatgraduated', 'yeargraduate', 'gos_awards', 'reg_awards');
    $appfields = array('appointment', 'appointment_head', 'pedagog_time', 'standing', 'standing_head',
                    	'standing_this', 'standing_this_head', 'qualify', 'qualify_date', 'qualify_head', 'qualify_date_head', 'date_advan_train', 'place_advan_train');

  	for($i=1; $i < $size; $i++)  {
        $line = split($csv_delimiter, $text[$i]);
        foreach ($line as $key => $value) {
            $record[$header[$key]] = trim($value);
        }

        // print_r($record);
        // add fields to object $user
        foreach ($record as $name => $value) {
            // check for required values
            if (isset($required[$name]) and !$value) {
                error(get_string('missingfield', 'error', $name). " ".
                      get_string('erroronline', 'error', $linenum) .". ".
                      get_string('processingstops', 'error'),
                      'importstaffs.php');
            }
            // normal entry
            else {
                if (in_array($name,$userfields))    {
                	$user->{$name} = addslashes($value);
                } else if (in_array($name,$stafffields))    {
                	$staff->{$name} = addslashes($value);
                } else if (in_array($name,$appfields))    {
                    $appointment->{$name} = addslashes($value);
                }    
            }
        }


        // CREATE USER ====================
        
        $ln_fn = mb_strtolower ($user->lastname . ' '. $user->firstname, 'UTF-8');
        if (in_array($ln_fn, $tezki))	{
               notify(get_string('usernotaddedregistered', 'error', $user->username . ' '. $user->lastname. ' '.  $user->firstname));
               $userserrors++;
               continue;
        }
        
		$translitlastname = translit_russian_utf8 ($user->lastname);
		$user->username = $translitlastname;
        $j = 1;
        $makecontinue = false;
		while (record_exists_mou('user', 'username', $user->username))  {
			$user->username = $translitlastname.$j;
	 		if ($olduser = get_record_select('user', "username = '$user->username'", 'id, lastname, firstname'))		{
			    if ($olduser->lastname == $user->lastname && $olduser->firstname == $user->firstname)	{
                   notify("$olduser->id ".get_string('usernotaddedregistered', 'error', $user->username . ' '. $user->lastname. ' '.  $user->firstname));
                   $userserrors++;
                   $makecontinue = true;
                   break;
                }
            }
			if ($j++ > 50) break;
		}

		if ($makecontinue) continue;

		if (empty ($user->email)) {
			$user->email = $user->username . '@temp.ru';
		}

        $staff->pswtxt = gen_psw($user->username);
        $user->password = hash_internal_user_password($staff->pswtxt);
   	    $user->city = $rayon->name;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->confirmed = 1;
        $user->timemodified = time();
        $user->country = 'RU';
        $user->lang = 'ru_utf8';
        $user->description = $appointment->appointment . ' ('. $ou->name . ')';
        
        if ($newid = insert_record("user", $user)) {
            echo "$user->username; $staff->pswtxt; $user->lastname; $user->firstname; $user->email<br>";
            $usersnew++;
            $staff->userid = $newid;
        } else {
            // Record not added -- possibly some other error
            notify(get_string('usernotaddederror', 'error', $user->username));
            $userserrors++;
            continue;
        }

   		if (!role_assign_mou($role_sotrudnik->id, $newid, $context->id))	{
   			notify("SOTRUDNIK $teacher1->userid not assigned.");
		}
        
        // CREATE STAFF ====================
		$staff->rayonid = $rid;
        $staff->{$edutype->idname} = $oid;
        $staff->edutypeid = $edutype->id;
        // print_r($user); echo '<hr>';                
        // print_r($ыефаа); echo '<hr>';

	    $datefield = array('birthday', 'qualify_date', 'qualify_date_head', 'date_advan_train');
		foreach ($datefield as $df)  {
		     if (isset($staff->{$df}) && !empty($staff->{$df}))	{
            	 if (is_date($staff->{$df}))	{
           		    $staff->{$df} = convert_date($staff->{$df});
            	 }
              }
        }

		if ($newstaffid = insert_record('monit_att_staff', $staff))	 {
		    // CREATE appointment ====================
            $appointment->staffid = $newstaffid;
            $appointment->stafftypeid = $stafftype->stafftypeid;
            if (!insert_record('monit_att_appointment', $appointment))	{
                error(get_string('errorinaddingteacher','block_mou_att'), $redirlink);
            }  
		} else  {
			error(get_string('errorinaddingteacher','block_mou_att'), $redirlink);
		}

        $linenum++;
        unset($user);
        unset($staff);
    }
    $strusersnew = get_string("usersnew");
    notify("$strusersnew: $usersnew", 'green', 'center');
    notify(get_string('errors', 'admin') . ": $userserrors");
	echo '<hr />';
}


function print_help_import()
{
        	echo '<hr>';
            print_simple_box_start_old('center', '100%', '#ffffff', 0);
            ?>


            <h2>Как загрузить список учителей школы в систему</h2>
            <p> Данное средство позволяет загрузить список педагогических и руководящих работников выбранной школы. </p>
            
            <ul>
            <li>Каждая строка файла содержит одну запись.</li>
            <li>Каждая запись - ряд данных, отделенных точками с запятой (;).</li>
            <li>Первая запись файла является особенной и содержит список имен полей. Они определяют формат остальной части файла.
            <blockquote>
            <p><strong>Требования к именам полей:</strong> эти поля должен быть включены в первую запись; они определяют для каждого сотрудника: </p>
            <p></p>
            <font color="#990000" face="Courier New, Courier, mono">
            фамилия;имя отчество;электронная почта;телефон;дата рождения;образование;какое учреждение закончил;год окончания;должность (преподаваемый предмет);руководящая должность;педагогическая нагрузка;стаж педагогической работы;стаж руководящей работы;стаж в занимаемой должности ;стаж в занимаемой руководящей должности ;наличие квалификационной категории учителя;дата присвоения для учителя;наличие квалификационной категории руководителя;дата присвоения для руководителя;государственные награды (год, название);отраслевые награды (год, название);дата окончания последнего курсового обучения;место обучения
            </font></p>
            </p>
            <p> <strong>Обязательными полями являются 'фамилия' и 'имя отчество'.</strong>
            </ul>
            <p><strong>Методика создания файла импорта:</strong>
            <ul>
            <li>Создать новую книгу в Microsof Excel</li>
            <li>В первой строке ввести названия полей. Одно название в одну ячейку, т.е. в ячейку A1 - фамилия, B1 - имя отчество, C1 - электронная почта и т.д. (названия полей вводить без точек с запятой и других знаков препинания)</li>
            <li>Заполнить вторую строку данными сотрудника, например, в ячейку A2 - Иванов, B2 - Иван Иванович, C2 - ivanov@mail.ru и т.д.<br />
            <font color="#990000"><b>НЕЛЬЗЯ ДОБАВЛЯТЬ К ДАТЕ 'г.' или 'год' или что-нибудь. ТОЛЬКО ЦИФРЫ, РАЗДЕЛЕННЫЕ ТОЧКАМИ.</b></font>
            <li>Аналогично заполнить данные по другим сотрудникам (одна строка - один сотрудник).
            <li>После ввода данных сохранить книгу сначала в формате XLS, а затем в формате CSV. Сохранение в CSV формате выполняется командой "Сохранить как" и в списке "Тип файла" выбирается "CSV".
            <li>Полученный CSV файл можно использовать для импорта кадрового состава школы в базу данных системы.
            </ul>
            
            <p><strong>Пример файла импорта в формате CSV (кодировка Windows-1251):</strong> </p>
            <p><font size="-1" face="Courier New, Courier, mono"></font>
            фамилия;имя отчество;электронная почта;телефон;дата рождения;образование;какое учреждение закончил;год окончания;должность (преподаваемый предмет);руководящая должность;педагогическая нагрузка;стаж педагогической работы;стаж руководящей работы;стаж в занимаемой должности ;стаж в занимаемой руководящей должности ;наличие квалификационной категории учителя;дата присвоения для учителя;наличие квалификационной категории руководителя;дата присвоения для руководителя;государственные награды (год, название);отраслевые награды (год, название);дата окончания последнего курсового обучения;место обучения<br />
            Алинова;Алина Ивановна;alinova@mail.ru;(4722)55-22-33;21.11.1949;высшее;Белгородский ГПИ;1976;;директор;;39;2;;1;высшая;03.03.2005;первая;04.04.2006;"2005, почетное звание ""Заслуженный учитель РФ""";"2001 значок ""Отличник  народного просвещения""";02.02.2003;БРИПКППС<br />
            Натальева;Наталья Ивановна;nataly@rambler.ru;(4722)11-22-33;01.01.1965;высшее;Белгородский ГПИ;1988;учитель;зам по воспитательной работе;18;19;3;18;3;Высшая;09.10.2003;первая;03.11.2005;;"2007  нагрудный знак ""Почетный работник общего образованияРФ""";04.11.2004;БРИПКППС<br />
            Нинова;Нина Ивановна;nina@temp.ru;(4722)11-11-11;01.01.1957;высшее;Белгородский ГПИ;1982;учитель;;18;26;;15;;Вторая;19.03.2002;;;;;03.05.2006;БРИПКППС<br />
            Иванов;Иван Федорович;ivanov@yandex.ru;(4722)44-11-22;01.01.1963;высшее;Белгородский ГПИ;1988;преподователь-организатор ОБЖ;;18;19;;12;;Первая;11.12.2001;;;;;02.02.2003;БРИПКППС<br />
            </font></p>
            
            <p><strong>Пример файла импорта в формате XLS: <a href="<?php echo $CFG->wwwroot.'/file.php/1/shablon_t_h.xls' ?>"> shablon_t_h.xls</a></strong></p>

            <?php
            print_simple_box_end_old();
}   

function translate_header(&$header)
{
	$string_rus[]='фамилия';                                    //1
	$string_rus[]='имя отчество';
	$string_rus[]='электронная почта';
	$string_rus[]='телефон';
	$string_rus[]='дата рождения';                              //5
	$string_rus[]='образование';
	$string_rus[]='какое учреждение закончил';
	$string_rus[]='год окончания';
	$string_rus[]='должность (преподаваемый предмет)';
	$string_rus[]='руководящая должность';
	$string_rus[]='педагогическая нагрузка';
	$string_rus[]='стаж педагогической работы';
	$string_rus[]='стаж руководящей работы';
	$string_rus[]='стаж в занимаемой должности';
	$string_rus[]='стаж в занимаемой руководящей должности';
	$string_rus[]='наличие квалификационной категории учителя';
	$string_rus[]='дата присвоения для учителя';
	$string_rus[]='наличие квалификационной категории руководителя';
	$string_rus[]='дата присвоения для руководителя';
	$string_rus[]='государственные награды (год, название)';
	$string_rus[]='отраслевые награды (год, название)';
	$string_rus[]='дата окончания последнего курсового обучения';
	$string_rus[]='место обучения';

	$string_lat[]='lastname';
	$string_lat[]='firstname';
	$string_lat[]='email';
	$string_lat[]='phone1';
	$string_lat[]='birthday';
	$string_lat[]='graduate';
	$string_lat[]='whatgraduated';
	$string_lat[]='yeargraduate';
	$string_lat[]='appointment';
	$string_lat[]='appointment_head';
	$string_lat[]='pedagog_time';
	$string_lat[]='standing';
	$string_lat[]='standing_head';
	$string_lat[]='standing_this';
	$string_lat[]='standing_this_head';
	$string_lat[]='qualify';
	$string_lat[]='qualify_date';
	$string_lat[]='qualify_head';
	$string_lat[]='qualify_date_head';
	$string_lat[]='gos_awards';
	$string_lat[]='reg_awards';
	$string_lat[]='date_advan_train';
	$string_lat[]='place_advan_train';

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
       	     echo '<pre>'; print_r($header); echo '</pre>';
             echo $i;
			 error(get_string('errorinnamefield', 'block_mou_att', $header[$i]), "importstaffs.php");
       	}
    }
    // echo '<pre>'; print_r($header); echo '</pre>';
}


function translit_russian_utf8($input)
{
  $arrRus = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м',
                  'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ь',
                  'ы', 'ъ', 'э', 'ю', 'я',
                  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М',
                  'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ь',
                  'Ы', 'Ъ', 'Э', 'Ю', 'Я');
  $arrEng = array('a', 'b', 'v', 'g', 'd', 'e', 'jo', 'zh', 'z', 'i', 'y', 'k', 'l', 'm',
                  'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'kh', 'c', 'ch', 'sh', 'sch', '',
                  'y', '', 'e', 'ju', 'ja',
                  'A', 'B', 'V', 'G', 'D', 'E', 'JO', 'ZH', 'Z', 'I', 'Y', 'K', 'L', 'M',
                  'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'KH', 'C', 'CH', 'SH', 'SCH', '',
                  'Y', '', 'E', 'JU', 'JA');
  return str_replace($arrRus, $arrEng, $input);
}

?>
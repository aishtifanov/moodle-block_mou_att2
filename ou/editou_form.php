<?php  // $Id: editou_form.php,v 1.5 2012/09/19 13:10:02 shtifanov Exp $

require_once($CFG->libdir.'/formslib.php');

class editou_form extends moodleform {
    function definition() {

        global $yid, $rid, $oid, $ou, $typeou, $tablename;

        $mform =& $this->_form;

        //--------------------------------------------------------------------------------
        $mform->addElement('header','', get_string('schooltab1', 'block_monitoring'));

	    $choices[0] = '-';
	    $records = get_records_select('monit_school_type', "is_att_type = 1 AND tblname = '$tablename'", 'id, name');
	    if ($records)	{
	    	foreach  ($records as $type)	{
    			if (mb_strlen($type->name, 'UTF-8') > 100)	{
    				$typename= mb_substr($type->name, 0,  100, 'UTF-8') . ' ...'; 
    			}  else {
    	  			$typename= $type->name;	
    			}
	    		$choices[$type->id] = $typename;
	    	}
            unset($records);
	    }

        $mform->addElement('select', 'typeinstitution',  get_string('typeinstitution', 'block_monitoring'), $choices);
        $mform->addRule('typeinstitution', get_string('missingname'), 'required', null, 'client');
        $mform->setDefault('typeinstitution', 0);

        $mform->addElement('text', 'number', get_string('numberou', 'block_monitoring'), 'maxlength="4" size="3"');
        $mform->addRule('number', get_string('missingname'), 'required', null, 'client');
        $mform->setType('number', PARAM_INT);

        $mform->addElement('text', 'name',  get_string('nameschool', 'block_monitoring'), 'maxlength="255" size="70"');
        $mform->addRule('name', get_string('missingname'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'fio',  get_string('directorschool', 'block_monitoring'), 'maxlength="100" size="70"');
        $mform->addRule('fio', get_string('missingname'), 'required', null, 'client');
        $mform->setType('fio', PARAM_TEXT);

        $mform->addElement('text', 'appointment',  get_string('appointmenthead', 'block_monitoring'), 'maxlength="100" size="70"');
        $mform->addRule('appointment', get_string('missingname'), 'required', null, 'client');
        $mform->setType('appointment', PARAM_TEXT);
/*
        unset($choices);
	    $choices[0] = '-';
	    $records = get_records_select('monit_school_category', '', 'id, name');
	    if ($records)	{
	    	foreach  ($records as $type)	{
    			if (mb_strlen($type->name, 'UTF-8') > 100)	{
    				$typename= mb_substr($type->name, 0,  100, 'UTF-8') . ' ...'; 
    			}  else {
    	  			$typename= $type->name;	
    			}
	    		$choices[$type->id] = $typename;
	    	}
            unset($records);
	    }
        $mform->addElement('select', 'stateinstitution',  get_string('stateinstitution', 'block_monitoring'), $choices);
        $mform->setDefault('stateinstitution', 0);
*/        
        $mform->addElement('text', 'numsession',  get_string('numsession', 'block_monitoring'), 'maxlength="1" size="1"');
        $mform->setType('numsession', PARAM_INT);

        //--------------------------------------------------------------------------------
        $mform->addElement('header','', get_string('schooltab5', 'block_monitoring'));        

        unset($choices);
	    $choices[0] = '-';
	    for ($i=1; $i<=7; $i++)	{
		    $choices[$i] = get_string('typesettlement'.$i, 'block_monitoring');
		}
        $mform->addElement('select', 'typesettlement',  get_string('typesettlement', 'block_monitoring'), $choices);
        $mform->setDefault('typesettlement', 0);

        unset($choices);
	    $choices[0] = '-';
        $choices[1] = get_string('yes');
        $choices[-1] = get_string('no');;
        $mform->addElement('select', 'iscountryside',  get_string('iscountryside', 'block_monitoring'), $choices);
        $mform->setDefault('iscountryside', 0);

        $mform->addElement('text', 'phones',  get_string('telnum', 'block_monitoring'), 'maxlength="99" size="40"');
        $mform->setType('phones', PARAM_TEXT);

        $mform->addElement('text', 'fax',  get_string('fax', 'block_monitoring'), 'maxlength="15" size="15"');
        $mform->setType('fax', PARAM_TEXT);

        $mform->addElement('text', 'realaddress',  get_string('realaddress', 'block_monitoring'), 'maxlength="254" size="85"');
        $mform->setType('realaddress', PARAM_TEXT);

        unset($choices);
	    $choices[0] = '-';
        $choices[1] = get_string('yes');
        $choices[-1] = get_string('no');;
        $mform->addElement('select', 'isjurequalreal',  get_string('isjurequalreal', 'block_monitoring'), $choices);
        $mform->setDefault('isjurequalreal', 0);

        $mform->addElement('text', 'juridicaladdress',  get_string('juridicaladdress', 'block_monitoring'), 'maxlength="254" size="85"');
        $mform->setType('juridicaladdress', PARAM_TEXT);

        $mform->addElement('text', 'www',  get_string('www', 'block_monitoring'), 'maxlength="254" size="70"');
        $mform->setType('www', PARAM_TEXT);

        $mform->addElement('text', 'email',  get_string('email', 'block_monitoring'), 'maxlength="100" size="40"');
        $mform->setType('email', PARAM_TEXT);

        //--------------------------------------------------------------------------------
        $mform->addElement('header','', get_string('licensedu', 'block_monitoring'));

        $mform->addElement('text', 'numlicense',  get_string('numlicense', 'block_monitoring'), 'maxlength="20" size="20"');
        $mform->setType('numlicense', PARAM_TEXT);

        $mform->addElement('text', 'regnumlicense',  get_string('regnumlicense', 'block_monitoring'), 'maxlength="20" size="20"');
        $mform->setType('regnumlicense', PARAM_TEXT);

        $mform->addElement('date_selector', 'startdatelicense', get_string('startdatelicense', 'block_monitoring'), 
                            array('startyear' => 2000, 'stopyear'  => 2050, 'timezone'  => 99, 'optional'  => false));
        $mform->setDefault('startdatelicense', time() + 3600 * 24);

        $mform->addElement('date_selector', 'enddatelicense', get_string('enddatelicense', 'block_monitoring'),
                            array('startyear' => 2000, 'stopyear'  => 2050, 'timezone'  => 99, 'optional'  => true));  

        $mform->setDefault('enddatelicense', time() + 3600 * 24);
        
        
        $sql = "SELECT l2.id, l2.parentid, l2.name, l1.name as parentname 
                FROM mdl_monit_education_level_ref l1
                inner join mdl_monit_education_level_ref l2 on l1.id=l2.parentid";
  	    $records = get_records_sql($sql);
	    if ($records)	{
	        $ii = 0;
	    	foreach  ($records as $edulevel)	{
	    	  if ($ii == 0)   {
	    	      $ii++;
                  // $mform->addElement('radio', 'educationlevelid', 'Уровень образования', $edulevel->name, $edulevel->id);
                  $mform->addElement('checkbox', 'educationlevel['.$edulevel->id.']', 'Уровень образования',  $edulevel->name);
	    	  } else {
                  // $mform->addElement('radio', 'educationlevelid', '', $edulevel->name, $edulevel->id);
                  $mform->addElement('checkbox', 'educationlevel['.$edulevel->id.']', '',  $edulevel->name);
	    	  }
           }   
        }
        $mform->setType('educationlevelid', PARAM_INT);
        


        //--------------------------------------------------------------------------------
        /*
        $mform->addElement('header','', get_string('licensextra', 'block_monitoring'));

        $mform->addElement('text', 'numlicensextra',  get_string('numlicensextra', 'block_monitoring'), 'maxlength="20" size="20"');
        $mform->setType('numlicensextra', PARAM_TEXT);

        $mform->addElement('text', 'regnumlicensextra',  get_string('regnumlicensextra', 'block_monitoring'), 'maxlength="20" size="20"');
        $mform->setType('regnumlicensextra', PARAM_TEXT);

        $mform->addElement('date_selector', 'startdatelicensextra', get_string('startdatelicensextra', 'block_monitoring'),
                            array('startyear' => 2000, 'stopyear'  => 2050, 'timezone'  => 99, 'optional'  => false));        
        $mform->setDefault('startdatelicensextra', time() + 3600 * 24);

        $mform->addElement('date_selector', 'enddatelicenseextra', get_string('enddatelicenseextra', 'block_monitoring'),  
                            array('startyear' => 2000, 'stopyear'  => 2050, 'timezone'  => 99, 'optional'  => true));
        $mform->setDefault('enddatelicenseextra', time() + 3600 * 24);
        */

        //--------------------------------------------------------------------------------
        $mform->addElement('header','', get_string('accreditcertificate', 'block_monitoring'));

        $mform->addElement('text', 'numcertificate',  get_string('numcertificate', 'block_monitoring'), 'maxlength="20" size="20"');
        $mform->setType('numcertificate', PARAM_TEXT);

        $mform->addElement('text', 'regnumcertificate',  get_string('regnumcertificate', 'block_monitoring'), 'maxlength="20" size="20"');
        $mform->setType('regnumcertificate', PARAM_TEXT);

        $mform->addElement('date_selector', 'startdatecertificate', get_string('startdatecertificate', 'block_monitoring'), 
                            array('startyear' => 2000, 'stopyear'  => 2050, 'timezone'  => 99, 'optional'  => false));
        $mform->setDefault('startdatecertificate', time() + 3600 * 24);

        $mform->addElement('date_selector', 'enddatecertificate', get_string('enddatecertificate', 'block_monitoring'),
                            array('startyear' => 2000, 'stopyear'  => 2050, 'timezone'  => 99, 'optional'  => true));
        $mform->setDefault('enddatecertificate', time() + 3600 * 24);

        //--------------------------------------------------------------------------------
        $mform->addElement('header','', get_string('schooltab3', 'block_monitoring'));

        $elements = array ('inn' => 15, 'kpp' => 10,  'okpo' => 10, 'okato' => 11, 'okogu' => 10, 'okfs' => 10, 'okved' => 20);
        foreach ($elements as $element => $maxlength)   {
            $mform->addElement('text', $element,  get_string($element, 'block_monitoring'), 'maxlength="' . $maxlength . '" size="10"');
            $mform->setType($element, PARAM_TEXT);
        }     

        //--------------------------------------------------------------------------------
        $mform->addElement('header','', get_string('schooltab4', 'block_monitoring'));
        
        unset($choices);
	    $choices[0] = '-';
	    for ($i=1; $i<=5; $i++)	{
		    $choices[$i] = get_string('type_ege'.$i, 'block_monitoring');
		}
        $mform->addElement('select', 'type_ege',  get_string('type_ege', 'block_monitoring'), $choices);
        $mform->setDefault('type_ege', 0);
        
        unset($choices);
	    $choices[0] = '-';
	    for ($i=1; $i<=6; $i++)	{
		    $choices[$i] = get_string('state_ege'.$i, 'block_monitoring');
		}
        
        //--------------------------------------------------------------------------------
        
        $mform->addElement('header','', 'Другое');

        unset($choices);
        $choices = array();
	    $choices[0] = 'нет';
        $choices[6] = 'да';
        $mform->addElement('select', 'clusterdou',  'Наличие дошкольной группы в ОУ', $choices);
        $mform->setDefault('clusterdou', 0);
        
                
		$mform->addElement('hidden', 'yearid',  $yid);  $mform->setType('yearid', PARAM_INT);
		$mform->addElement('hidden', 'rayonid', $rid);  $mform->setType('rayonid', PARAM_INT);
		$mform->addElement('hidden', 'yid',  $yid);  $mform->setType('yid', PARAM_INT);
		$mform->addElement('hidden', 'rid', $rid);  $mform->setType('rid', PARAM_INT);
		$mform->addElement('hidden', 'oid', $oid);  $mform->setType('oid', PARAM_INT);        
        $mform->addElement('hidden', 'typeou', $typeou);  $mform->setType('typeou', PARAM_TEXT);
        $mform->addElement('hidden', 'typeeducation', 1);  $mform->setType('typeeducation', PARAM_INT);
        $mform->addElement('hidden', 'dateclosing', 0);  $mform->setType('dateclosing', PARAM_INT);
        $this->add_action_buttons();
    }

    function validation($data) {
        $errors = array();

        if (0 == count($errors)){
            return true;
        } else {
            return $errors;
        }

    }

}
?>

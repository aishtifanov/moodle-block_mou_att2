<?php  // $Id: editou.php,v 1.1.1.1 2011/03/25 07:53:50 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');
    require_once('editou_form.php');

    require_login();

    $rid = optional_param('rid', 0, PARAM_INT);       // Rayon id
    $oid = optional_param('oid', 0, PARAM_INT);       // OU id
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
    $typeou = optional_param('typeou', '-');       // Type OU
	$action   = optional_param('action', '');

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    if ($oid == 0)  {
        get_constants_rayon($typeou, $CONTEXT_RAYON, $tablename);
        $context = get_context_instance($CONTEXT_RAYON, $rid);
        $strtitle = get_string('addou','block_monitoring');
           
    } else {
        get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
    	$context = get_context_instance($CONTEXT_OU, $oid);
    }    	

	if (!has_capability('block/mou_att2:editattestationuser', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    $redirlink = "ou.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou";
    
	// $strclasses = get_string('classes','block_mou_ege');
	$strtitle0 = get_string('title2','block_mou_att');
	$strscript = get_string('ou','block_mou_att');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle0, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php?rid=$rid&yid=$yid", 'type' => 'misc');
    $navlinks[] = array('name' => $strscript, 'link' => $redirlink, 'type' => 'misc');
    $navlinks[] = array('name' => $strtitle, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); // , navmenu($course)

    $edutype = get_config_typeou($typeou);

 	if ($rid != 0)  {

        $editform = new editou_form('editou.php');
        
        if ($oid != 0)  {
    	    if (!$ou = get_record($tablename, 'id', $oid))	{
    	    	 error('OU not found!');
    	    }
    	    if (!empty($ou)) {

                if ($education_level = get_records_select('monit_education_level', "{$edutype->idname} = $ou->id ")) {
                    $ou->educationlevel = array();
                }
                foreach ($education_level as $el)   {
                    $ou->educationlevel[$el->educationlevelid]=1;
                }

    	        $editform->set_data($ou);
    	    }
        }    

	    if ($editform->is_cancelled())	{
            redirect($redirlink, '', 0);
	    } else if ($data = $editform->get_data()) 	{
            // print_object($data); echo  '<hr>';
	    	if (!empty($ou))	 {
		    	$data->id =  $ou->id;
		    	// print_r($data);
		        if (update_record($tablename, $data)) {
		            // notice(get_string('giaresultupdated', 'block_mou_ege', $data->id));
		            // redirect($redirlink, get_string('succesupdatedata', 'block_monitoring'), 110);
                    $ou = get_record($tablename, 'id', $ou->id);
		        } else {
		            error('Error in update OU.');
		        }
		    } else {
		    	$data->timemodified = time();
		        if ($newid = insert_record($tablename, $data)) {
		            set_field($tablename, 'uniqueconstcode', $newid , 'id', $newid);
		            // notice(get_string('giaresultupdated', 'block_mou_ege', $data->id));
		            // redirect($redirlink, get_string('succesupdatedata', 'block_monitoring'), 110);
                    $ou = get_record($tablename, 'id', $newid);
		        } else {
		            // print_r($rec);
		            error('Error in insert OU.');
		        }
		    }

            if ($education_level = get_records_select('monit_education_level', "{$edutype->idname} = $ou->id ")) {
                foreach ($data->educationlevel as $educationlevelid => $v) {
                    $data->educationlevelid[$educationlevelid] = $educationlevelid;
                }
                foreach ($education_level as $el)   {
                    if (!in_array($el->educationlevelid, $data->educationlevelid))  {
                        delete_records_select('monit_education_level', "id = $el->id");
                    }
                }
                foreach ($data->educationlevel as $educationlevelid => $v) {
                    if (!record_exists_select('monit_education_level', "{$edutype->idname} = $ou->id and educationlevelid=$educationlevelid"))  {
                        $rec = new stdClass();
                        $rec->yearid = $yid;
                        $rec->rayonid = $rid;
                        $rec->{$edutype->idname} = $ou->id;
                        $rec->typeinstitution = $ou->typeinstitution;
                        $rec->educationlevelid  = $educationlevelid;
                        if (!insert_record('monit_education_level', $rec)) {
                            error('Error in insert monit_education_level(2)!');
                        }
                    }
                }

            } else {
                $rec = new stdClass();
                $rec->yearid = $yid;
                $rec->rayonid = $rid;
                $rec->{$edutype->idname} = $ou->id;
                $rec->typeinstitution = $ou->typeinstitution;
                foreach ($data->educationlevel as $educationlevelid => $v)  {
                    $rec->educationlevelid = $educationlevelid;
                    if (!insert_record('monit_education_level', $rec)) {
                        error('Error in insert monit_education_level(1)!');
                    }
                }
            }

            redirect($redirlink, get_string('succesupdatedata', 'block_monitoring'), 0);

	    } else {
	        print_heading (get_string ('registerdata', 'block_monitoring'), 'center', 3);
	        $editform->display();
	    }
	}

    print_footer();

?>
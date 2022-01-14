<?php // $Id: operators.php,v 1.2 2011/10/28 10:43:45 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib_att2.php');

    define("MAX_USERS_TO_LIST_PER_ROLE", 100);
    
    $rid = optional_param('rid', 0, PARAM_INT);          // Rayon id
    $oid = optional_param('oid', 0, PARAM_INT);          // OU id
    $levelmonit  = optional_param('level', 'region');
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
    $typeou = optional_param('typeou', '-');       // Type OU    
   	$action = optional_param('action', '');       // action    

    if ($yid == 0)	{
    	$yid = get_current_edu_year_id();
    }

    $strlistrayons =  listbox_rayons_att("", $rid);
        
    if ($rid == 0 && $strlistrayons <> '')   {
        $rid = get_first_rid();
    }

    get_edit_capability_region_rayon($rid, $edit_capability_region, $edit_capability_rayon);
    
	if (!$edit_capability_region && !$edit_capability_rayon)  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$strtitle = get_string('title2','block_mou_att');
    $stroperators = get_string('operators', 'block_monitoring');

    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $stroperators, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $stroperators, $SITE->fullname, $navigation, "", "", true, "&nbsp;"); 

    include('tabsoperators.php');

	// print_heading(get_string($levelmonit.'operatorsapraou', 'block_mou_att'), "center", 2);

   
	switch ($levelmonit)	{
		case 'region':
                      if (!$context = get_context_instance(CONTEXT_REGION_ATT, 1))   {
                      // if (!$context = get_context_instance_by_id($contextid)) {
                            error("Context ID was incorrect (can't find it)");
                      }
                      
		break;

		case 'rayon':
        
                	$strlistrayons =  listbox_rayons_att("operators.php?level=rayon&yid=$yid&rid=", $rid);
                	$strlisttypeou =  listbox_typeou_att("operators.php?level=rayon&rid=$rid&yid=$yid&typeou=", $rid, $typeou);
                
                	if (!$strlistrayons && !$strlisttypeou)   { 
                		error(get_string('permission', 'block_mou_school'), '../index.php');
                	}
                    
                	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
                	echo $strlistrayons;
                	echo $strlisttypeou;
                	echo '</table>';
	                if ($rid != 0 && $typeou != '-')   {
                        $outype = get_record_select('monit_school_type', "cod = '$typeou'", 'id, name, cod, tblname');
                      	$outype->context = CONTEXT_RAYON;
                      	switch ($outype->tblname)	{
                      		case 'monit_school': 
                                        $outype->context = CONTEXT_RAYON;
                      		break;
                      		case 'monit_college': 
                                        $outype->context = CONTEXT_RAYON_COLLEGE;
                      		break;
                      		case 'monit_udod': 
                                        $outype->context = CONTEXT_RAYON_UDOD;
                      		break;
                      		case 'monit_education': 
                                        $outype->context = CONTEXT_RAYON_DOU;
                      		break;
                      	}
                          
                        if (!$context = get_context_instance($outype->context, $rid))   {
                            error("Context ID was incorrect (can't find it)");
                        }
                    }      	
		break;

		case 'ou':
                	$strlistrayons =  listbox_rayons_att("operators.php?level=ou&oid=0&yid=$yid&rid=", $rid);
                	$strlisttypeou =  listbox_typeou_att("operators.php?level=ou&rid=$rid&yid=$yid&oid=0&typeou=", $rid, $typeou);
                
                	if (!$strlistrayons && !$strlisttypeou)   { 
                		error(get_string('permission', 'block_mou_school'), '../index.php');
                	}	
                	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
                	echo $strlistrayons;
                	echo $strlisttypeou;
                	// echo $typeou;
                	if ($typeou != '-')	{
                		if ($strlistou = listbox_ou_att("operators.php?level=ou&rid=$rid&yid=$yid&typeou=$typeou&oid=", $rid, $typeou, $oid, $yid))	{ 
                			echo $strlistou;
                		} else {
                			echo '</table>';
                			notice(get_string('ounotfound', 'block_mou_att'), "../index.php?rid=$rid&yid=$yid");
                		}	
                	} 
                	echo '</table>';
                	
                	if ($rid != 0 && $typeou != '-')   {
                
                        if ($oid != 0) {
                	
                        	// print_tabs_years($yid, "ou.php?rid=$rid&typeou=$typeou&oid=$oid&yid=");
                            get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
                            
                        	if (!$context = get_context_instance($CONTEXT_OU, $oid))   {
                                error("Context ID was incorrect (can't find it)");
                            }
                        }
                   }             
        break;
    }    
		
        
    if (isset($context))    {
        $baseurl = "assign.php?rid=$rid&yid=$yid&contextid=".$context->id;
        
        $overridableroles = get_overridable_roles($context, 'name', ROLENAME_BOTH);
        $assignableroles  = get_assignable_roles($context, 'name', ROLENAME_BOTH);
        
        print_heading_with_help(get_string('assignrolesin', 'role', print_context_name($context)), 'assignroles');

        // Get the names of role holders for roles with between 1 and MAX_USERS_TO_LIST_PER_ROLE users,
        // and so determine whether to show the extra column. 
        $rolehodlercount = array();
        $rolehodlernames = array();
        $strmorethanten = get_string('morethan', 'role', MAX_USERS_TO_LIST_PER_ROLE);
        $showroleholders = false;
        foreach ($assignableroles as $roleid => $rolename) {
            $countusers = count_role_users($roleid, $context);
            $rolehodlercount[$roleid] = $countusers;
            $roleusers = '';
            if (0 < $countusers && $countusers <= MAX_USERS_TO_LIST_PER_ROLE) {
                $roleusers = get_role_users($roleid, $context, false, 'u.id, u.username, u.lastname, u.firstname');
                if (!empty($roleusers)) {
                    $strroleusers = array();
                    foreach ($roleusers as $user) {
                        $strroleusers[] = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $user->id . '" >' . fullname($user) . " ($user->username)</a>";
                    }
                    $rolehodlernames[$roleid] = implode('<br />', $strroleusers);
                    $showroleholders = true;
                }
            } else if ($countusers > MAX_USERS_TO_LIST_PER_ROLE) {
                $rolehodlernames[$roleid] = '<a href="'.$baseurl.'&roleid='.$roleid.'">'.$strmorethanten.'</a>';
            } else {
                $rolehodlernames[$roleid] = '';
            }
        }
		
		
        // Print overview table
        $table->tablealign = 'center';
        $table->cellpadding = 5;
        $table->cellspacing = 0;
        $table->width = '60%';
        $table->head = array(get_string('roles', 'role'), get_string('description'), get_string('users'));
        $table->wrap = array('nowrap', '', 'nowrap');
        $table->align = array('right', 'left', 'center');
        if ($showroleholders) {
            $table->head[] = '';
            $table->wrap[] = 'nowrap';
            $table->align[] = 'left';
        }
		
		
        foreach ($assignableroles as $roleid => $rolename) {
        	
        	//if (in_array($roleid, $what_role_show_id))	{
	            $description = format_string(get_field('role', 'description', 'id', $roleid));
	            $row = array('<a href="'.$baseurl.'&roleid='.$roleid.'">'.$rolename.'</a>',$description, $rolehodlercount[$roleid]);
	            // $row = array('<b>'.$rolename.'<b>',$description, $rolehodlercount[$roleid]);
	            if ($showroleholders) {
	                $row[] = $rolehodlernames[$roleid];
	            }
	            $table->data[] = $row;
	        //}    
        }
        print_table($table);
       
    }    				
    print_footer();

?>


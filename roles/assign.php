<?php // $Id: assign.php,v 1.4 2011/06/07 11:59:11 shtifanov Exp $
      // Script to assign users to contexts

    require_once("../../../config.php");
    require_once($CFG->dirroot.'/mod/forum/lib.php');
    require_once($CFG->libdir.'/adminlib.php');
    include($CFG->dirroot.'/blocks/monitoring/lib_auth.php');
	require_once('../lib_att2.php');    

    define("MAX_USERS_PER_PAGE", 5000);
    define("MAX_USERS_TO_LIST_PER_ROLE", 50);

    $rid = optional_param('rid', 0, PARAM_INT);       // Rayon id
    $oid = optional_param('oid', 0, PARAM_INT);       // OU id
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
	$typeou = optional_param('typeou', '-');       // Type OU    

    $contextid      = required_param('contextid',PARAM_INT); // context id
    $roleid         = optional_param('roleid', 0, PARAM_INT); // required role id
    $add            = optional_param('add', 0, PARAM_BOOL);
    $remove         = optional_param('remove', 0, PARAM_BOOL);
    $showall        = optional_param('showall', 0, PARAM_BOOL);
    $searchtext     = optional_param('searchtext', '', PARAM_RAW); // search string
    $previoussearch = optional_param('previoussearch', 0, PARAM_BOOL);
    $hidden         = optional_param('hidden', 0, PARAM_BOOL); // whether this assignment is hidden
    $extendperiod   = optional_param('extendperiod', 0, PARAM_INT);
    $extendbase     = optional_param('extendbase', 0, PARAM_INT);
    $userid         = optional_param('userid', 0, PARAM_INT); // needed for user tabs
    $courseid       = optional_param('courseid', 0, PARAM_INT); // needed for user tabs

    $errors = array();

    $previoussearch = ($searchtext != '') or ($previoussearch) ? 1:0;

    $baseurl = "assign.php?typeou=$typeou&rid=$rid&oid=$oid&yid=$yid&contextid=".$contextid;

    if (! $context = get_context_instance_by_id($contextid)) {
        error("Context ID was incorrect (can't find it)");
    }

    $inmeta = 0;
    $courseid = SITEID;
    $course = clone($SITE);

    require_login($course);

    require_capability('moodle/role:assign', $context);

/// needed for tabs.php

    $overridableroles = get_overridable_roles($context, 'name', ROLENAME_BOTH);
    $assignableroles  = get_assignable_roles($context, 'name', ROLENAME_BOTH);
    /*
    $what_role_show_id = array (8, 10, 11, 12, 13);
    foreach ($assignableroles as $kk => $vv) {
    	if (!in_array($kk, $what_role_show_id))	{
    		unset($assignableroles[$kk]);
    	}
    }
	*/
/// Get some language strings

    $strpotentialusers = get_string('potentialusers', 'role');
    $strexistingusers = get_string('existingusers', 'role');
    $straction = get_string('assignroles', 'role');
    $strroletoassign = get_string('roletoassign', 'role');
    $strsearch = get_string('search');
    $strshowall = get_string('showall');
    $strparticipants = get_string('participants');
    $strsearchresults = get_string('searchresults');

    $unlimitedperiod = get_string('unlimited');
    $defaultperiod = $course->enrolperiod;
    for ($i=1; $i<=365; $i++) {
        $seconds = $i * 86400;
        $periodmenu[$seconds] = get_string('numdays', '', $i);
    }

 
/// Make sure this user can assign that role

    if ($roleid) {
        if (!isset($assignableroles[$roleid])) {
            error ('you can not override this role in this context');
        }
    }


    $context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    $edit_capability_region = has_capability('block/mou_att2:editou', $context_region);
    $context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	$edit_capability_rayon = has_capability('block/mou_att2:editou', $context_rayon);

/// Print the header and tabs

    get_constants_ou($typeou, $CONTEXT_OU, $tablename, $strtitle, $strselect, $where);
    $strtitle = get_string('title','block_mou_att');
    $strscript = get_string('ou','block_mou_att');
    $localrole = get_string('localroles', 'role');
    
    $navlinks = array();

    switch ($context->contextlevel) {
        case CONTEXT_REGION:
        case CONTEXT_REGION_ATT:
        	$strreportregion = get_string('regionopers', 'block_monitoring');
        	$title = get_string('assignroles','role');
        	
            $navlinks[] = array('name' => $strtitle, 'link' => $CFG->wwwroot.'/blocks/mou_att2/index.php', 'type' => 'misc');
            $navlinks[] = array('name' => $strreportregion, 'link' => $CFG->wwwroot.'/blocks/mou_att2/roles/operators.php?level=region', 'type' => 'misc');
            $navlinks[] = array('name' => $title, 'link' => null, 'type' => 'misc');
            $navigation = build_navigation($navlinks);
            print_header($SITE->fullname, $SITE->fullname, $navigation);
            break;

        case CONTEXT_RAYON:
        case CONTEXT_RAYON_COLLEGE:
        case CONTEXT_RAYON_UDOD:
        case CONTEXT_RAYON_DOU:
        	$strrayons = get_string('rayons', 'block_monitoring');
       	
            $navlinks[] = array('name' => $strtitle, 'link' => $CFG->wwwroot.'/blocks/mou_att2/index.php', 'type' => 'misc');
            $navlinks[] = array('name' => $strrayons, 'link' => null, 'type' => 'misc');
            $navigation = build_navigation($navlinks);
            print_header($SITE->fullname, $SITE->fullname, $navigation);
            break;

        case CONTEXT_SCHOOL:
        case CONTEXT_COLLEGE:
        case CONTEXT_UDOD:
        case CONTEXT_DOU:
            $navlinks[] = array('name' => $strtitle, 'link' => $CFG->wwwroot.'/blocks/mou_att2/index.php', 'type' => 'misc');
            $navlinks[] = array('name' => $strscript, 'link' => $CFG->wwwroot."/blocks/mou_att2/ou/ou.php?typeou=$typeou&rid=$rid&yid=$yid&oid=$oid", 'type' => 'misc');
            $navlinks[] = array('name' => $localrole, 'link' => "assign.php?typeou=$typeou&rid=$rid&yid=$yid&oid=$oid&contextid=$contextid", 'type' => 'misc');
            $navlinks[] = array('name' => $localrole, 'link' => null, 'type' => 'misc');
            $navigation = build_navigation($navlinks);
            print_header($SITE->fullname, $SITE->fullname, $navigation);
            break;
            
        case CONTEXT_SYSTEM:
            $stradministration = get_string('administration');
            $navlinks[] = array('name' => $stradministration, 'link' => '../index.php', 'type' => 'misc');
            $navlinks[] = array('name' => $straction, 'link' => null, 'type' => 'misc');
            $navigation = build_navigation($navlinks);
            print_header($SITE->fullname, "$SITE->fullname", $navigation);
            break;

        default:
            error ('This context (' . $context->contextlevel . ') is not selected in mou_att2/assign.php!');
            return false;

    }


/// Process incoming role assignment

    if ($frm = data_submitted()) {

        if ($add and !empty($frm->addselect) and confirm_sesskey()) {

            foreach ($frm->addselect as $adduser) {
                if (!$adduser = clean_param($adduser, PARAM_INT)) {
                    continue;
                }
                $allow = true;
                if ($inmeta) {
                    if (has_capability('moodle/course:managemetacourse', $context, $adduser)) {
                        //ok
                    } else {
                        $managerroles = get_roles_with_capability('moodle/course:managemetacourse', CAP_ALLOW, $context);
                        if (!empty($managerroles) and !array_key_exists($roleid, $managerroles)) {
                            $erruser = get_record('user', 'id', $adduser, '','','','', 'id, firstname, lastname');
                            $errors[] = get_string('metaassignerror', 'role', fullname($erruser));
                            $allow = false;
                        }
                    }
                }
                if ($allow) {
                    switch($extendbase) {
                        case 0:
                            $timestart = $course->startdate;
                            break;
                        case 3:
                            $timestart = $today;
                            break;
                        case 4:
                            $timestart = $course->enrolstartdate;
                            break;
                        case 5:
                            $timestart = $course->enrolenddate;
                            break;
                    }

                    if($extendperiod > 0) {
                        $timeend = $timestart + $extendperiod;
                    } else {
                        $timeend = 0;
                    }
                    if (! role_assign($roleid, $adduser, 0, $context->id, $timestart, $timeend, $hidden)) {
                        $errors[] = "Could not add user with id $adduser to this role!";
                    }
                }
            }
            
            $rolename = get_field('role', 'name', 'id', $roleid);
            add_to_log($course->id, 'role', 'assign', 'admin/roles/assign.php?contextid='.$context->id.'&roleid='.$roleid, $rolename, '', $USER->id);
        } else if ($remove and !empty($frm->removeselect) and confirm_sesskey()) {

            $sitecontext = get_context_instance(CONTEXT_SYSTEM);
            $topleveladmin = false;

            // we only worry about this if the role has doanything capability at site level
            if ($context->id == $sitecontext->id && $adminroles = get_roles_with_capability('moodle/site:doanything', CAP_ALLOW, $sitecontext)) {
                foreach ($adminroles as $adminrole) {
                    if ($adminrole->id == $roleid) {
                        $topleveladmin = true;
                    }
                }
            }

            foreach ($frm->removeselect as $removeuser) {
                $removeuser = clean_param($removeuser, PARAM_INT);

                if ($topleveladmin && ($removeuser == $USER->id)) {   // Prevent unassigning oneself from being admin
                    continue;
                }

                if (! role_unassign($roleid, $removeuser, 0, $context->id)) {
                    $errors[] = "Could not remove user with id $removeuser from this role!";
                } else if ($inmeta) {
                    sync_metacourse($courseid);
                    $newroles = get_user_roles($context, $removeuser, false);
                    if (!empty($newroles) and !array_key_exists($roleid, $newroles)) {
                        $erruser = get_record('user', 'id', $removeuser, '','','','', 'id, firstname, lastname');
                        $errors[] = get_string('metaunassignerror', 'role', fullname($erruser));
                        $allow = false;
                    }
                }
            }
            
            $rolename = get_field('role', 'name', 'id', $roleid);
            add_to_log($course->id, 'role', 'unassign', 'admin/roles/assign.php?contextid='.$context->id.'&roleid='.$roleid, $rolename, '', $USER->id);
        } else if ($showall) {
            $searchtext = '';
            $previoussearch = 0;
        }
        
        
    
    }

    print_heading_with_help(get_string('assignrolesin', 'role', print_context_name($context)), 'assignroles');


    if ($roleid) {        /// prints a form to swap roles

    /// Get all existing participants in this context.
        // Why is this not done with get_users???

        if (!$contextusers = get_role_users($roleid, $context, false, 'u.id, u.firstname, u.lastname, u.email, ra.hidden')) {
            $contextusers = array();
        }

        $select  = "username <> 'guest' AND deleted = 0 AND confirmed = 1";

        $usercount = count_records_select('user', $select) - count($contextusers);

        $searchtext = trim($searchtext);

        if ($searchtext !== '') {   // Search for a subset of remaining users
            $LIKE      = sql_ilike();
            // $FULLNAME  = sql_fullname();
            // $selectsql = " AND ($FULLNAME $LIKE '%$searchtext%' OR email $LIKE '%$searchtext%') ";
            $selectsql = " AND (lastname $LIKE '$searchtext%') "; // change by shtifanov
            $select  .= $selectsql;
        } else {
            $selectsql = "";
        }

        if ($edit_capability_region || $edit_capability_rayon)  {

            /************************************************************************
             *                                                                      *
             * context level is above or equal course context level                 *
             * in this case we pull out all users matching search criteria (if any) *
             *                                                                      *
             ************************************************************************/

            /// MDL-11111 do not include user already assigned this role in this context as available users
            /// so that the number of available users is right and we save time looping later

			if ($searchtext !== '') { 
				$strsql = 'SELECT id, firstname, lastname, email
                                                FROM '.$CFG->prefix.'user
                                                WHERE '.$select.'
                                                AND id NOT IN (
                                                    SELECT u.id
                                                    FROM '.$CFG->prefix.'role_assignments r,
                                                    '.$CFG->prefix.'user u
                                                    WHERE r.contextid = '.$contextid.'
                                                    AND u.id = r.userid
                                                    AND r.roleid = '.$roleid.'
                                                    '.$selectsql.')
                                                ORDER BY lastname ASC, firstname ASC';
			// echo $strsql;                                                
            $availableusers = get_recordset_sql($strsql);

            $usercount = $availableusers->_numOfRows;
            } else {
            	$usercount = '100000...';  // change by shtifanov
			}
            
        } else {

            /************************************************************************
             *                                                                      *
             * context level is above or equal course context level                 *
             * in this case we pull out all users matching search criteria (if any) *
             *                                                                      *
             * MDL-11324                                                            *
             * a mini get_users_by_capability() call here, this is done instead of  *
             * get_users_by_capability() because                                    *
             * 1) get_users_by_capability() does not deal with searching by name    *
             * 2) exceptions array can be potentially large for large courses       *
             * 3) get_recordset_sql() is more efficient                             *
             *                                                                      *
             ************************************************************************/

            if ($possibleroles = get_roles_with_capability('moodle/course:view', CAP_ALLOW, $context)) {

                $doanythingroles = get_roles_with_capability('moodle/site:doanything', CAP_ALLOW, get_context_instance(CONTEXT_SYSTEM));
/*
                $validroleids = array();
                foreach ($possibleroles as $possiblerole) {
                    if (isset($doanythingroles[$possiblerole->id])) {  // We don't want these included
                            continue;
                    }
                    if ($caps = role_context_capabilities($possiblerole->id, $context, 'moodle/course:view')) { // resolved list
                        if (isset($caps['moodle/course:view']) && $caps['moodle/course:view'] > 0) { // resolved capability > 0
                            $validroleids[] = $possiblerole->id;
                        }
                    }
                }
*/
				$validroleids = array(3,4,5,6,7,8,9,10,11,12,13,14,15);
                if ($validroleids) {
                    $roleids =  '('.implode(',', $validroleids).')';

                    $select = " SELECT u.id, u.firstname, u.lastname, u.email";
                    $countselect = "SELECT COUNT(u.id)";
                    $from   = " FROM {$CFG->prefix}user u
                                INNER JOIN {$CFG->prefix}role_assignments ra ON ra.userid = u.id
                                INNER JOIN {$CFG->prefix}role r ON r.id = ra.roleid";
                    $where  = " WHERE ra.contextid ".get_related_contexts_string($context)."
                                AND u.deleted = 0
                                AND ra.roleid in $roleids";
                    $excsql = " AND u.id NOT IN (
                                    SELECT u.id
                                    FROM {$CFG->prefix}role_assignments r,
                                    {$CFG->prefix}user u
                                    WHERE r.contextid = $contextid
                                    AND u.id = r.userid
                                    AND r.roleid = $roleid
                                    $selectsql)";
					$strsql = $select . $from . $where . $selectsql . $excsql;
					// echo $strsql; 
                    $availableusers = get_recordset_sql($strsql); // // change by shtifanov
                }
				$usercount =  $availableusers->_numOfRows;
                // $usercount =  '1000...'; // $availableusers->_numOfRows; // // change by shtifanov
            }

			 
        }

        echo '<div align=center class="selector">';
        $assignableroles = array('0'=>get_string('listallroles', 'role').'...') + $assignableroles;
        popup_form("assign.php?typeou=$typeou&rid=$rid&oid=$oid&yid=$yid&contextid=$contextid&roleid=",
            $assignableroles, 'switchrole', $roleid, '', '', '', false, 'self', $strroletoassign);
        echo '</div>';

        print_simple_box_start('center');
        include('assign.html');
        print_simple_box_end();

        if (!empty($errors)) {
            $msg = '<p>';
            foreach ($errors as $e) {
                $msg .= $e.'<br />';
            }
            $msg .= '</p>';
            print_simple_box_start('center');
            notify($msg);
            print_simple_box_end();
        }
		
		//Back to Assign Roles button
		echo "<br/>";
		echo "<div class='continuebutton'>";
		print_single_button('assign.php', array('contextid' => $contextid), get_string('assignrolesin', 'role', ''));
		echo "</div>";

    } else {   // Print overview table

        // sync metacourse enrolments if needed
        if ($inmeta) {
            sync_metacourse($course);
        }

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
                $roleusers = get_role_users($roleid, $context, false, 'u.id, u.lastname, u.firstname');
                if (!empty($roleusers)) {
                    $strroleusers = array();
                    foreach ($roleusers as $user) {
                        $strroleusers[] = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $user->id . '" >' . fullname($user) . '</a>';
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
		
	   //Continue to Course Button
	   echo "<br/>";
	   /*
	   echo "<div class='continuebutton'>";
	   print_single_button($CFG->wwwroot.'/course/view.php', array('id' => $courseid), get_string('continuetocourse'));
	   echo "</div>";
	   */
    }
	
    print_footer($course);

/*    
function listbox_levelmonit_att($lid)
{
    global $USER;
    
    $context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    if (!$edit_capability_region = has_capability('block/mou_att2:editou', $context_region))    {
       $context_rayon = get_context_instance(CONTEXT_RAYON, $rid);
	   if (!$edit_capability_rayon = has_capability('block/mou_att2:editou', $context_rayon))  {
	       return false; 
	   }
    } 

    if ($edit_capability_region)    {
        $levemenu[0] = get_string('levelregion','block_monitoring');      
    }    
  
    if ($edit_capability_rayon  || $edit_capability_region) {  
      $levemenu[1] = get_string('levelrayon','block_monitoring');
      $levemenu[2] = get_string('levelschool','block_monitoring');
    }
    
    $scriptname = '';
    switch ($lid)   {
        case 0: if ($context = get_record_select('context', "contextlevel =" . CONTEXT_REGION . " AND instanceid = 1", 'id')) {
                    $scriptname = "assign.php?contextid={$context->id}";
                }
        break;
        case 1:             
    }  
    
    
  popup_form($scriptname, $levemenu, 'switchlevel', $lid, '', '', '', false);
  return 1;
}
*/    
    
?>
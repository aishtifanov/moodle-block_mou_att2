<?php // $Id: __update.php,v 1.2 2011/05/10 12:13:45 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib_att2.php');

    $yid = optional_param('yid', 0, PARAM_INT);		// Текущий учебный год
    /*		
    $oldyid = optional_param('oldyid', 7, PARAM_INT);    // Предыдущий учебный год
    $yid = optional_param('yid', 8, PARAM_INT);          // Year id
    */
    $strtitle = "Создание копии критериев аттестации текущего учебного года на следующий ";
    
    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_att/index.php">'.get_string('title','block_mou_att').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header($SITE->shortname . ': '. $strtitle, $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}
    
    ignore_user_abort(false); 
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("128M");
 	if (function_exists('apache_child_terminate')) {
	    @apache_child_terminate();
	}    
    
    notify("<b>Внимание! Выберите тот учебный год, критерии которого надо скопировать в следующий.</b><br> Например, если выбрать 2014/2015 уч. год, то критерии будут скопированы в 2015/2016 уч. год.");
    echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    listbox_years("copycriterianextyear.php?yid=", $yid);
    echo '</table>';

    if ($yid > 0)   {
       $nextyid = $yid + 1;
    
	   if ($oldcrits = get_records_select('monit_att_criteria', "yearid=$yid", 'id')) {

           foreach ($oldcrits as $oldcrit) {
               $oldcrit->yearid = $nextyid;
               $oldcritid = $oldcrit->id;
               if (!record_exists_select('monit_att_criteria', "yearid = $oldcrit->yearid and stafftypeid=$oldcrit->stafftypeid and num = '$oldcrit->num'")) {
                   if ($newid = insert_record('monit_att_criteria', $oldcrit)) {
                       // print_object($oldcrit);
                       // id, criteriaid, name, mark, maxmark, typefield, namefield, printname
                       $oldests = get_records_select('monit_att_estimates', "criteriaid = $oldcritid", 'id');
                       foreach ($oldests as $oldest) {
                           $oldest->criteriaid = $newid;
                           insert_record('monit_att_estimates', $oldest);
                       }
                       notify("Критерий '$oldcrit->name' скопирован в $oldcrit->yearid учебный год.", 'notifysuccess');
                   }
               } else {
                   notify("Критерий '$oldcrit->name' уже есть в системе в $oldcrit->yearid учебном году.");
               }
           }
       } else {
           notify("Критерии для копирования не найдены!");
       }
    }    
 
	print_footer();
?>





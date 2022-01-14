<?PHP // $Id: staffattempts.php,v 1.6 2012/02/16 11:03:04 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/filelib.php');
    require_once('../../monitoring/lib.php');
    require_once('../lib_att2.php');
    require_once("../../../mod/quiz/locallib.php");

    $id = optional_param('id', 0, PARAM_INT);  	  //
    
    $currenttab = 'staffattempts';
    include('tabsatt.php'); 
    
    if ($edit_capability_rayon && $action == 'da' && $id > 0)   {
        $attempt = get_record_select('quiz_attempts', "id=$id");
        $quiz = get_record_select('quiz', "id=$attempt->quiz");
        quiz_delete_attempt($attempt, $quiz);
        /*
        delete_records_select('question_attempts', "id=$id");
        delete_records_select('question_states', "attempt=$id");
        delete_records_select('question_sessions', "attemptid=$id");
        delete_records_select('quiz_attempts', "uniqueid=$id");
        */
    }    

    $table = table_staffattempts($uid);
    print_color_table($table);
    print_footer();    
    
    

function table_staffattempts($uid)
{
    global $CFG, $edit_capability_rayon, $urlparam;

    // $uid = 308;
    
    $strteststart  = get_string('strteststart','block_mou_att');
    $strtime  = get_string('zatrvremya','block_mou_att');
    $strmark  = get_string('ballov','block_mou_att');
    $strcolgrade  = get_string('colgrade','block_mou_att');
    $strdiscipline = get_string('course','block_mou_att');
    $strtestname = get_string('testname','block_mou_att');
    $strproc = get_string('procents','block_mou_att');

    $strtimeformat = get_string('strftimedatetime');

    $table = new stdClass();
    $table->head  = array ($strdiscipline, $strtestname, $strteststart, $strtime, $strcolgrade, $strproc, $strmark);
    $table->align = array ("сenter", "left", "center", "center", "center", "center", "center");
    $table->class = 'moutable';
    
    if ($edit_capability_rayon) {
        $table->head[] = get_string('action', 'block_monitoring');
        $table->align[] = 'center';
    }

    
    $strsql = "SELECT id, uniqueid, quiz, userid, attempt, sumgrades, timestart, timefinish, timemodified, layout, preview
               FROM {$CFG->prefix}quiz_attempts
			   WHERE userid= $uid";
    // echo $strsql;
                
	if ($qattempts = get_records_sql($strsql))  {
	    // print_r($qattempts);
        foreach ($qattempts as $attempt)   {
            $quiz = get_record_select('quiz', "id = $attempt->quiz", 'id, course, name, sumgrades');
            $course  = get_record_select('course', "id = $quiz->course", 'id, fullname');
            $row = array();
            $row[] = "<div align=left><strong><a href=\"{$CFG->wwwroot}/course/view.php?id=$course->id\">".$course->fullname."</a></strong></div>";
            $row[] = $quiz->name;

            $startdate = userdate($attempt->timestart, $strtimeformat);
            // $row[] = '<a href="review.php?q='.$quiz->id.'&attempt='.$attempt->attempt.'">'.$startdate.'</a>';
            $row[] = $startdate;
            if ($attempt->timefinish) {
                $row[] = format_time($attempt->timefinish - $attempt->timestart);
            } else {
                $row[] = get_string('unfinished', 'quiz');
            }
      
            $row[] = $attempt->attempt;
      
            $gradestr = ($attempt->sumgrades*100)/($quiz->sumgrades);      
            $mark = $ball = 0;
            if (is_numeric($gradestr)) {
                $ball = round($gradestr);

                if ($ball >= 5 && $ball < 20)		$mark = 1;                        
                else if ($ball >= 20 && $ball < 50)	$mark = 2;
                else if ($ball >= 50 && $ball < 75)	$mark = 3;	
                else if ($ball >= 75 && $ball < 90)	$mark = 4;
                else if ($ball >= 90)				$mark = 5;
            } 

		    $row[] = $ball . '%';
            $row[] = $mark;      
            
            $strlinkupdate = '';
            if ($edit_capability_rayon) {
				$title = get_string('deleteattempts','block_mou_att');
			    $strlinkupdate .= "<a title=\"$title\" href=\"staffattempts.php?id=$attempt->id&action=da&$urlparam\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
                $row[] = $strlinkupdate;
            }    
            
            $table->data[] = $row;
        }
    } 
    return $table;
}    
  
?>
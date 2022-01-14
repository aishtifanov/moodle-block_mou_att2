<?php

    require_once('../../../config.php');
    require_once("$CFG->dirroot/mod/quiz/locallib.php");
    // require_once('../../monitoring/lib.php');

	define("MAX_SYMBOLS_LISTBOX", 100);
	
    require_login();

    $context_region = get_context_instance(CONTEXT_REGION_ATT, 1);
    $edit_capability = has_capability('block/mou_att2:editmeetingak', $context_region); 
	if (!$edit_capability )  {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    /// assign all of the configurable language strings
    $strtitle = get_string('title2', 'block_mou_att');
	$strquizdatetime = get_string('setquizdate','block_cdoadmin'); // get_string('date') . '  ' . get_string('time') . ' ' . get_string('modulename', 'quiz');
    $navlinks = array();
    $navlinks[] = array('name' => $strtitle, 'link' => "$CFG->wwwroot/blocks/mou_att2/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strquizdatetime, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($SITE->shortname . ': '. $strquizdatetime, $SITE->fullname, $navigation, "", "", true, "&nbsp;");

	$strname = get_string('name');
	$strall = get_string('all');
	$strcourse = get_string('course');
	$strstartdate = get_string("quizopen", "quiz");
	$strenddate = get_string("quizclose", "quiz");

    print_heading('Установка параметров тестов', 'center', 4);
/// A form was submitted so generate a report
    if ($frm = data_submitted()) {
    	// print_r($frm);
		quiz_process_options($frm);

	    // echo "review: $frm->review <br>";


        echo "<hr />\n";

        print_simple_box_start('center', '80%');

        echo "<table align=center cellpadding=\"5\" cellspacing=\"2\" border=\"0\">\n";
        echo '<tr>';
        echo '<td><strong>'.$strname.'</strong></td>';
        echo '<td><strong>'.$strstartdate.'</strong></td>';
        echo '<td><strong>'.$strenddate .'</strong></td>';
        echo "<tr>\n";


    /// course users
        if ( !empty($frm->courses) ) {
            $allcourses = in_array('all', $frm->courses);
            $courses = get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname');
            $NUMERRORS = 0;
            foreach ($courses as $course) {
                if ( ($allcourses or (in_array($course->id, $frm->courses)) ) and ($course->id != 1) ) {
	                /// get quiz
                    $quizes = get_records('quiz', 'course', $course->id);
			        if ( !empty($quizes) ) {
	           			$len = strlen ($course->fullname);
						if ($len > 100)  {
							// $school->name = substr($school->name, 0, 200) . ' ...';
							$course->fullname = substr($course->fullname,0,strrpos(substr($course->fullname,0, 110),' ')) . ' ...';
						}
	                    echo '<tr><th colspan="3"align="left"><hr><strong>'.$strcourse.': '.$course->fullname.'</strong></th></tr>';
    	                foreach ($quizes as $quiz) {
	  					    $quiz->optionflags = $frm->optionflags;
							$quiz->review = $frm->review;
							if (isset($frm->timeopenoff))	{
								$dateopen = $quiz->timeopen = 0;
							} else {
						    	$quiz->timeopen = make_timestamp($frm->openyear, $frm->openmonth, $frm->openday,
						        	                             $frm->openhour, $frm->openminute, 0);
						        $dateopen = date("d.m.Y \- H:i", $quiz->timeopen);	                             
						    }    	                             
							if (isset($frm->timecloseoff))	{
								$dateclose = $quiz->timeclose = 0;
							} else {
							    $quiz->timeclose = make_timestamp($frm->closeyear, $frm->closemonth, $frm->closeday,
    	                                  						  $frm->closehour, $frm->closeminute, 0);
    	                        $dateclose = date("d.m.Y \- H:i", $quiz->timeclose);          						  
							}

							$quiz->questionsperpage = $frm->questionsperpage;
							$quiz->attempts = $frm->attempts;

	  				        if (!update_record("quiz", $quiz)) {
		                        // error("Could not update quiz \"$quiz->name\" in course \"$course->fullname\"!");
		                        $NUMERRORS++;
	        	                echo "<tr><td>";
   		                        notify("ОШИБКА при обновлении теста \"$quiz->name\" в курсе \"$course->fullname\"!");
	    	                    echo '</td><td></td><td></td></tr>';
						    } else {
	        	                echo "<tr><td><a href=\"$CFG->wwwroot/mod/quiz/view.php?q=$quiz->id\">$quiz->name</a></td>";
	    	                    echo '<td>'.$dateopen.'</td>';
	        	                echo '<td>'.$dateclose .'</td>';
	    	                    echo "</tr>\n";
                            }
	    	            }
						echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
					}
            	}
    	    }
  	        notify("Количество ошибок при обновлении тестов = $NUMERRORS");
	     }
      }

    echo "</table>\n";

    print_simple_box_end();

    /// Load up a list of courses
    $courseselect  = "<select name=\"courses[]\" multiple=\"multiple\" size=\"35\">\n";
   
    $courseselect .= '<option value="all"';
    if ((isset($frm->courses) and in_array('all', $frm->courses)) or !$frm ) $courseselect .= 'selected="selected"';
    $courseselect .= ">$strall</option>\n";

    $categories = get_categories();
    foreach ($categories as $category) {
        $courseselect .= "<optgroup label=\"$category->name\">\n";
        $courses = get_courses($category->id, 'c.sortorder ASC', 'c.id, c.fullname');
        foreach ($courses as $course) {
            $courseselect .= "<option value=\"$course->id\"";
            if (isset($frm->courses) and in_array($course->id, $frm->courses)) $courseselect .= 'selected="selected"';
   			$len = strlen ($course->fullname);
   			$strfn = $course->fullname;
			if ($len > MAX_SYMBOLS_LISTBOX*2)  {
				$strfn = mb_substr($course->fullname, 0, MAX_SYMBOLS_LISTBOX, "UTF-8") . ' ...';
			}
            $courseselect .= ">$strfn</option>\n";
        }
        
/*
        $subcategories = get_all_subcategories($category->id);
        
        if (!empty($subcategories))	{
			print_r($subcategories);
			echo $category->name;
		    foreach ($subcategories as $scatid) {
		    	$subcat = get_record('course_categories', 'id', $scatid);
		        $courseselect .= "<optgroup label=\">>>>$subcat->name\">\n";
		        $courses = get_courses($scatid, 'c.sortorder ASC', 'c.id, c.fullname');
		        foreach ($courses as $course) {
		            $courseselect .= "<option value=\"$course->id\"";
		            if (isset($frm->courses) and in_array($course->id, $frm->courses)) $courseselect .= 'selected="selected"';
		   			$len = strlen ($course->fullname);
		   			$strfn = $course->fullname;
					if ($len > MAX_SYMBOLS_LISTBOX*2)  {
						$strfn = mb_substr($course->fullname, 0, MAX_SYMBOLS_LISTBOX, "UTF-8") . ' ...';
					}
		            $courseselect .= ">$strfn</option>\n";
		        }
		        $courseselect .= "</optgroup>\n";
			}        	
        }
*/        
        $courseselect .= "</optgroup>\n";
        // break;
    }
    
    $courseselect .= "</select>\n";


    print_simple_box_start('center', '80%');
?>

<form name="userreportform" action="<?php echo $ME ?>" method="post">
    <table align="center" cellpadding="5" cellspacing="2" border="0">

		<tr>
            <td align="right" valign="top"><strong><?php print_string('courses') ?></strong></td>
            <td>
                <?php echo $courseselect ?>
            </td>
        </tr>


        <tr valign="top">
	    <td align="right"><b><?php print_string("quizopen", "quiz") ?>:</b></td>
    	<td align="left">
    <?php
		$nowdate = time();
        print_date_selector("openday", "openmonth", "openyear", $nowdate );
        print_time_selector("openhour", "openminute", $nowdate); 
        echo '<input name="timeopenoff" type="checkbox" value="1" /><label>Отключить</label></span>';  // checked="checked"        
        helpbutton("timeopen", get_string("quizopen","quiz"), "quiz");
     ?>
	    </td>
		</tr>

		<tr valign="top">
    <td align="right"><b><?php print_string("quizclose", "quiz") ?>:</b></td>
     <td align="left">
    <?php
		$nowdate = time();
		print_date_selector("closeday", "closemonth", "closeyear", $nowdate);
        print_time_selector("closehour", "closeminute", $nowdate);
        echo '<input name="timecloseoff" type="checkbox" value="1" /><label>Отключить</label></span>'; // checked="checked"        
        helpbutton("timeopen", get_string("quizclose","quiz"), "quiz");
     ?>
    </td>
</tr>




<td align="right">
  <table>
    <tr><td align="right"><b><?php print_string("reviewoptions", "quiz") ?>:</b></td></tr>
  </table>
</td>
<td align="left">
  <table>
    <tr valign="top">
      <td align="center"><?php print_string('reviewimmediately', 'quiz') ?></td>
      <td align="center"><?php print_string('reviewopen', 'quiz') ?></td>
      <td align="center"><?php print_string('reviewclosed', 'quiz') ?></td>
      <td>
        <?php helpbutton("review2", get_string("allowreview","quiz"), "quiz"); ?>
      </td>
    </tr>
    <tr>
      <td align="left">
        <input type="checkbox" name="responsesimmediately" value="Yes"  />
        <?php	print_string('responses', 'quiz'); ?><br />
        <input type="checkbox" name="answersimmediately" value="Yes"  />
        <?php	print_string('answers', 'quiz'); ?><br />
        <input type="checkbox" name="feedbackimmediately" value="Yes" checked="checked"/>
        <?php	print_string('feedback', 'quiz'); ?><br />        
        <input type="checkbox" name="generalfeedbackimmediately" value="Yes" checked="checked"/>
        <?php	print_string('generalfeedback', 'quiz'); ?><br />        
        <input type="checkbox" name="scoreimmediately" value="Yes" checked="checked"/>
        <?php	print_string('scores', 'quiz'); ?><br />        
        <input type="checkbox" name="overallfeedbackimmediately" value="Yes"checked="checked" />
        <?php	print_string('overallfeedback', 'quiz'); ?>        
      </td>
    <td align="left">
      <input type="checkbox" name="responsesopen" value="Yes" />
        <?php	print_string('responses', 'quiz'); ?><br />      
      <input type="checkbox" name="answersopen" value="Yes"  />
        <?php	print_string('answers', 'quiz'); ?><br />      
      <input type="checkbox" name="feedbackopen" value="Yes"  checked="checked"/>
        <?php	print_string('feedback', 'quiz'); ?><br />      
      <input type="checkbox" name="generalfeedbackopen" value="Yes" checked="checked" />
        <?php	print_string('generalfeedback', 'quiz'); ?><br />      
      <input type="checkbox" name="scoreopen" value="Yes" checked="checked"/>
      	<?php	print_string('scores', 'quiz'); ?><br />
      <input type="checkbox" name="overallfeedbackopen" value="Yes" checked="checked"/>
      	<?php	print_string('overallfeedback', 'quiz'); ?><br />	        
     </td> 
	<td align="left">    
      <input type="checkbox" name="responsesclosed" value="Yes"  />
        <?php	print_string('responses', 'quiz'); ?><br />      
      <input type="checkbox" name="answersclosed" value="Yes"  />
        <?php	print_string('answers', 'quiz'); ?><br />      
      <input type="checkbox" name="feedbackclosed" value="Yes" checked="checked" />
		<?php	print_string('feedback', 'quiz'); ?><br />      
      <input type="checkbox" name="generalfeedbackclosed" value="Yes"  checked="checked" />
        <?php	print_string('generalfeedback', 'quiz'); ?><br />      
      <input type="checkbox" name="scoreclosed" value="Yes" checked="checked" />      
      	<?php	print_string('scores', 'quiz'); ?><br />
      <input type="checkbox" name="overallfeedbackclosed" value="Yes" checked="checked"/>
      	<?php	print_string('overallfeedback', 'quiz'); ?><br />	        

      </td>
    </tr>
  </table>
</td>

    <tr valign="top">
        <td align="right"><b><?php print_string('questionsperpage', 'quiz') ?>:</b></td>
         <td align="left">
        <?php
            $perpage= array();
            for ($i=0; $i<=50; ++$i) {
                $perpage[$i] = $i;
            }
            $perpage[0] = get_string('allinone', 'quiz');

            choose_from_menu($perpage, 'questionsperpage', 0, '');
            helpbutton('questionsperpage', get_string('questionsperpage'), 'quiz');
         ?>
        </td>
    </tr>

<tr valign="top">
  <td align="right"><b><?php print_string("attemptsallowed", "quiz") ?>:</b></td>
  <td>
    <?php
        unset($options);
        $options[0] = get_string("attemptsunlimited", "quiz");
        $options[1] = "1";
        for ($i=2;$i<=6;$i++) {
            $options[$i] = "$i";
        }
        choose_from_menu ($options, "attempts", 1, "", "", "");
        helpbutton("attempts", get_string("attemptsallowed","quiz"), "quiz");
    ?>
  </td>
</tr>

        <tr>
            <td colspan="2" align="center">
                <input type="submit" value="<?php print_string('savechanges') ?>" />
            </td>
        </tr>


    </table>
</form>


<?php
    print_simple_box_end();

    print_footer();


?>

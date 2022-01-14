<?php // $Id: img_synchr.php,v 1.1 2012/02/27 06:46:33 shtifanov Exp $

    require_once('../../config.php');
    require_once('lib_dean.php');
  /*
 
SELECT id, question, questiontext FROM `moodle`.`mdl_question_match_sub`
where questiontext like '%file.php/2633/%'
order by question
 
 */
  
	$action = optional_param('action', '');
	$corseid = optional_param('s', 0, PARAM_INT);          // Start id
  
  	$strtitle = get_string('img_synchr', 'block_dean');
	     
	$breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header($strtitle, $SITE->fullname,$breadcrumbs);
 
 	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

   
	$patterns[] = "/\/file.php\/[0-9]\//i";
	$patterns[] = "/\/file.php\/[0-9][0-9]\//i";	
	$patterns[] = "/\/file.php\/[0-9][0-9][0-9]\//i";	   
	$patterns[] = "/\/file.php\/[0-9][0-9][0-9][0-9]\//i";	
	$replacement = "/file.php/$corseid/";

    set_time_limit(0);
    
	if ($action == 'change' && $corseid != 0)	{

		if (!$course = get_record('course', 'id', $corseid)) {
			error('Course not found.', '__qq.php');
		}	

		if ($questions = get_records_sql("SELECT id,course,questions FROM {$CFG->prefix}quiz
										WHERE course=$corseid"))	{
			foreach ($questions as $question){
				$explode = explode(',', $question->questions);
				foreach ($explode as $expl){
					if ($quest = get_record_sql("SELECT id,questiontext,name FROM {$CFG->prefix}question
												WHERE id=$expl")){
						// print_r($quest); echo '<br>';			
						
						$search = $quest->questiontext;
						foreach ($patterns as $pattern)	{
							if (preg_match($pattern, $search)){
								$text = preg_replace($pattern, $replacement, $search);
								set_field('question', 'questiontext', addslashes($text), 'id', $quest->id);
								echo $search . '<br>';
								echo $text . '<hr>';			
							}							
						}
						
						$search = $quest->name;  
						foreach ($patterns as $pattern)	{						
							if (preg_match($pattern,$search)){
								$text = preg_replace($pattern,$replacement,$search);
								set_field('question', 'name', addslashes($text), 'id', $quest->id);	
								echo $search . '<br>';								
								echo $text . '<hr>';		
							}
						}	
						
			
						if ($answers = get_records_sql("SELECT id,question,answer FROM {$CFG->prefix}question_answers
														WHERE question={$quest->id}")){
							// print_r($quest); echo '<hr>';								
							foreach ($answers as $answer){
								$search = $answer->answer;
								foreach ($patterns as $pattern)	{  
									if (preg_match($pattern,$search)){
										$text = preg_replace($pattern,$replacement,$search);
										set_field('question_answers', 'answer', addslashes($text), 'id', $answer->id);
										echo $search . '<br>';
										echo $text . '<hr>';								
									}
								}	 
							} 
						}	
						
						if ($question_match_sub = get_records_sql("SELECT id,questiontext,answertext FROM {$CFG->prefix}question_match_sub
														WHERE question={$quest->id}")){
							foreach ($question_match_sub as $quest_match_sub){
								$search = $quest_match_sub->questiontext;
								foreach ($patterns as $pattern)	{  
									if (preg_match($pattern,$search)){
										$text = preg_replace($pattern,$replacement,$search);
										set_field('question_match_sub', 'questiontext', addslashes($text), 'id', $quest_match_sub->id);
										echo $search . '<br>';
										echo $text . '<hr>';								
									}
								}
								
							$search = $quest_match_sub->answertext;
							foreach ($patterns as $pattern)	{  
								if (preg_match($pattern,$search)){
									$text = preg_replace($pattern,$replacement,$search);
									set_field('question_match_sub', 'answertext', addslashes($text), 'id', $quest_match_sub->id);
									echo $search . '<br>';
									echo $text . '<hr>';								
									}
								}								
									 
							} 
						}	
						
					}				
				}
			}
			notify('Changes in TESTs successfully saved.');
		} else {
			notify('Tests and questions not found.');	
		}
		
		if ($glossary = get_records_sql("SELECT id,course,name,intro FROM {$CFG->prefix}glossary
										WHERE course=$corseid"))	{
			foreach ($glossary as $gloss){
				$search = $gloss->name;
				foreach ($patterns as $pattern)	{  
					if (preg_match($pattern,$search)){
						$text = preg_replace($pattern,$replacement,$search);
						set_field('glossary', 'name', addslashes($text), 'id', $gloss->id);
						echo $search . '<br>';
						echo $text . '<hr>';								
					}
				}
				
				$search = $gloss->intro;
				foreach ($patterns as $pattern)	{  
					if (preg_match($pattern,$search)){
						$text = preg_replace($pattern,$replacement,$search);
						set_field('glossary', 'intro', addslashes($text), 'id', $gloss->id);
						echo $search . '<br>';
						echo $text . '<hr>';								
					}
				}
				
				if ($glossary_entries = get_records_sql("SELECT id,glossaryid,concept,definition FROM {$CFG->prefix}glossary_entries
										WHERE glossaryid={$gloss->id}")){
					foreach ($glossary_entries as $glossary_entr){
						$search = $glossary_entr->concept;
						foreach ($patterns as $pattern)	{  
							if (preg_match($pattern,$search)){
								$text = preg_replace($pattern,$replacement,$search);
								set_field('glossary_entries', 'concept', addslashes($text), 'id', $glossary_entr->id);
								echo $search . '<br>';
								echo $text . '<hr>';								
							}
						}
						
						$search = $glossary_entr->definition;
						foreach ($patterns as $pattern)	{  
							if (preg_match($pattern,$search)){
								$text = preg_replace($pattern,$replacement,$search);
								set_field('glossary_entries', 'definition', addslashes($text), 'id', $glossary_entr->id);
								echo $search . '<br>';
								echo $text . '<hr>';								
							}
						}
					}
				}
			}
			notify('Changes in Glossary successfully saved.');
		}	else {
			notify('Glossary not found.');
		}
		
	}	
 
 	$searchtext = (string)$corseid;
 	
 	echo '<div align=center><form name="studentform" id="studentform" method="post" action="img_synchr.php?action=change">Course ID: &nbsp&nbsp'.
		 '<input type="text" name="s" size="10" value="' . $searchtext. '" />'.
      	 '<input name="search" id="search" type="submit" value="'.get_string('synchroniz', 'block_dean').'">'.
		 '</form></div>';

	print_footer();
?>
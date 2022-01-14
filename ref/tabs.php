<?php  // $Id: tabs.php,v 1.2 2011/09/07 14:00:13 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $toprow2 = array();
   	$toprow2[] = new tabobject('refschooltype', 'refschooltype.php', get_string('refschooltype', 'block_mou_att'));
   	$toprow2[] = new tabobject('refstafftype', 'refstafftype.php',  get_string('refstafftype', 'block_mou_att'));
    $toprow2[] = new tabobject('rangemarks', 'rangemarks.php',  get_string('rangemarks', 'block_mou_att'));
   	$toprow2[] = new tabobject('stafftypeou', 'stafftypeou.php',   get_string('stafftypeou', 'block_mou_att'));
    $toprow2[] = new tabobject('attcriteria', 'attcriteria.php',   get_string('attcriteria', 'block_mou_att'));
    $toprow2[] = new tabobject('importcriteria', 'importcriteria.php',   get_string('importcriteria', 'block_mou_att'));
    
    if (isadmin())  {
        $toprow2[] = new tabobject('copycriterianextyear', 'copycriterianextyear.php',   'Копирование критериев');
    }

    $tabs2 = array($toprow2);
    print_tabs($tabs2, $currenttab, NULL, NULL);

?>

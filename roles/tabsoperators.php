<?php  // $Id: tabsoperators.php,v 1.2 2012/02/02 06:28:47 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set

   $toprow = array();
   
   $uri = "rid=$rid&yid=$yid";

   $toprow[] = new tabobject('region', $CFG->wwwroot."/blocks/mou_att2/roles/operators.php?level=region&$uri",
                get_string('regionopers', 'block_monitoring'));
   $toprow[] = new tabobject('rayon', $CFG->wwwroot."/blocks/mou_att2/roles/operators.php?level=rayon&$uri",
                get_string('rayonopers', 'block_monitoring'));
   $toprow[] = new tabobject('ou', $CFG->wwwroot."/blocks/mou_att2/roles/operators.php?level=ou&$uri",
                get_string('ouopers', 'block_mou_att'));
   $toprow[] = new tabobject('importoper', $CFG->wwwroot."/blocks/mou_att2/roles/importopers.php",
    	        get_string('importopers', 'block_monitoring'));
   $toprow[] = new tabobject('oldoper', $CFG->wwwroot."/blocks/mou_att/users/operators.php",
    	        'Старая система ролей');

   $tabs = array($toprow);
   print_tabs($tabs, $levelmonit, NULL, NULL);


?>
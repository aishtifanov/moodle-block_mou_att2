<?php  // $Id: tabs.php,v 1.1.1.1 2011/03/25 07:53:50 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set

   if (empty($currenttab)) {
       error('You cannot call this script in that way');
   }

   $toprow = array();

   $toprow = array();
   $toprow[] = new tabobject('shtatstaffs', "staffs.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou",
                get_string('shtatstaffs', 'block_mou_att'));

   $toprow[] = new tabobject('sovmeststaff', "sovmeststaff.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou",
	                get_string('sovmeststaff', 'block_mou_att'));

   $toprow[] = new tabobject('importstaff', "importstaffs.php?rid=$rid&yid=$yid&oid=$oid&typeou=$typeou",
 	               get_string('importstaff', 'block_mou_att'));

   $tabs = array($toprow);
   print_tabs($tabs, $currenttab, NULL, NULL);

?>
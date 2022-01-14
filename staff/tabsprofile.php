<?php  // $Id: tabsprofile.php,v 1.7 2009/11/18 13:47:46 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab) or empty($user)) {
        error('You cannot call this script in that way');
    }

    $toprow = array();
    $toprow[] = new tabobject('profile', "viewstaff.php?rid=$rid&oid=$oid&yid=$yid&uid={$user->id}&typeou=$typeou",
                               get_string('profileatt', 'block_mou_att'));

    if ($edit_capability || ($editownprofile && $uid == $USER->id))  {
	    $toprow[] = new tabobject('registrationcard', "registrationcard.php?rid=$rid&oid=$oid&yid=$yid&uid={$user->id}&typeou=$typeou",
                			      get_string('editprofileatt', 'block_mou_att'));
	}
    $tabs = array($toprow);
    
    print_tabs($tabs, $currenttab, NULL, NULL);

?>
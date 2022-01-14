<?php // $Id: regcardedit.php,v 1.3 2011/09/16 12:00:50 shtifanov Exp $

   require_once($CFG->libdir.'/uploadlib.php');
   require_once($CFG->libdir.'/filelib.php');
       
   $CFG->maxbytes = MAX_SCAN_COPY_SIZE; 

   $struploadafile = get_string('loadfiledocs', 'block_mou_att');
   $strmaxsize = get_string("maxsize", "", display_size($CFG->maxbytes));

   if (!isset($user->htmleditor)) {
       $user->htmleditor = 1;
   }
   if (!isset($user->picture)) {
       $user->picture = NULL;
   }
   if (empty($user->lang)) {
       $user->lang = $CFG->lang;
   }
   if (!isset($user->theme)) {
       $user->theme = '';
   }
   if (!isset($user->trackforums)) {
       $user->trackforums = 0;
   }
   if (!isset($user->secondname)) {
   	   if (isset($user->firstname) && !empty($user->firstname)) {
     	   list($f,$s) = explode(' ', $user->firstname);
           $user->firstname = $f;
           $user->secondname = $s;
       } else {
           $user->secondname = '';
       }
   }

   if (!isset($user->city)) {
       $user->city = get_string('belgorod', 'block_monitoring');
   }

   $user->country = 'RU';
   $user->auth = 'manual';

   echo '<div align=right><small><b>'. get_string('attentionform', 'block_monitoring') . '</b></small> <font color="red">*</font><br>';
   echo '<small><b>'. get_string('attentionformznakminus', 'block_mou_att') . '</b></small></div>';
?>

<form method="post" name="form" enctype="multipart/form-data" action="registrationcard.php">
<table class="formtable">
<input type="hidden" name="rid" value="<?php echo $rid ?>" />
<input type="hidden" name="oid" value="<?php echo $oid ?>" />
<input type="hidden" name="yid" value="<?php echo $yid ?>" />
<input type="hidden" name="uid" value="<?php echo $uid ?>" />
<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
<input type="hidden" name="id" value="<?php echo $user->id ?>" />
<input type="hidden" name="staffid" value="<?php echo $user->staffid ?>" />

<?php
    if (!empty($CFG->gdversion) and empty($CFG->disableuserimages)) {
?>
<tr>
    <th><?php print_string('currentfoto', 'block_mou_att') ?>:</th>
    <td>
       <?php print_user_picture($user->id, 1, $user->picture, false, false, false);
             if ($user->picture) {
                 echo '&nbsp;&nbsp;<input type="checkbox" name="deletepicture" alt="'.get_string("delete").'" value="1" />';
                 print_string("delete");
             }
       ?>
    </td>
</tr>
<tr>
    <th><?php print_string("newpicture");  helpbutton("picture", get_string("helppicture")); ?>:</th>
    <td>
    <?php
       require_once($CFG->dirroot.'/lib/uploadlib.php');
       upload_print_form_fragment(1,array('imagefile'),null,false,null,0,0,false);
       if (isset($err["imagefile"])) formerr($err["imagefile"]);
    ?>
    </td>
</tr>
<?php } else if (empty($CFG->gdversion) and isadmin()) {  ?>
<tr>
    <th><?php print_string("newpicture") ?>:</th>
    <td>
    <?php
        echo "<a href=\"$CFG->wwwroot/$CFG->admin/config.php\">";
        print_string('gdnot');
        echo "</a>";
    ?>
    </td>
</tr>
<?php 
   }
   echo '<tr><td colspan="2"><hr /></td></tr>';
?>
<tr>
    <th><?php print_string("lastname") ?><font color="red">*</font>:</th>
    <td>
    <input type="text" name="lastname" size="30" alt="<?php print_string("lastname") ?>" maxlength="20" value="<?php p($user->lastname) ?>" />
    <?php if (isset($err["lastname"])) formerr($err["lastname"]); ?>
    </td>
</tr>
<tr>
    <th><?php print_string("firstname") ?><font color="red">*</font>:</th>
    <td>
    <input type="text" name="firstname" size="30" alt="<?php print_string("firstname") ?>" maxlength="20" value="<?php p($user->firstname) ?>" />
    <?php if (isset($err["firstname"])) formerr($err["firstname"]); ?>
    </td>
</tr>
<tr>
    <th><?php print_string('secondname', 'block_monitoring') ?><font color="red">*</font>:</th>
    <td>
    <input type="text" name="secondname" size="30" alt="<?php print_string('secondname', 'block_monitoring') ?>" maxlength="20" value="<?php p($user->secondname) ?>" />
    <?php if (isset($err["secondname"])) formerr($err["secondname"]); ?>
    </td>
</tr>

<?php
    print_staffeditfields($user, $profile);
    
    $appointments = get_records_select ('monit_att_appointment', "staffid={$user->staffid}", 'id DESC', 'id, staffid, stafftypeid, meetingid, appointment, prevappointment, pedagog_time, standing, standing_this, date_start_app, qualify, qualify_date, place_advan_train, date_advan_train, qualifynow, total_mark');
    if ($appointments)  {
        // $profile->fields = array('appointment', 'pedagog_time', 'standing', 'standing_this', 'qualify', 'qualify_date', 'place_advan_train', 'date_advan_train');
        // $profile->type   = array('text', 'real', 'real', 'real', 'text', 'date', 'text', 'date');
        $profile->fields = array('appointment', 'date_start_app', 'prevappointment','qualify', 'qualify_date', 'place_advan_train', 'qualifynow'); // , 'stafftypeid', 'meetingid'); 'date_advan_train',
        $profile->type   = array('text', 'date', 'text', 'text', 'date', 'text',  'text'); // , 'stft', 'meet'); 'date',
        $i=1;
        foreach ($appointments as $appointment) {
            echo '<tr><td colspan="2"><hr /></td></tr>';
            print_staffeditfields($appointment, $profile, $i);

            echo '<tr><th>'. get_string("stafftypeid", 'block_mou_att') . '</th><td>';
            unset($choices);
            
            $edutypeid = $edutype->id;            
            
            $choices[0] = get_string('selectastft', 'block_mou_att').' ...';
            if ($edutypeid == 0) {
                $strsql = "SELECT id, name  FROM {$CFG->prefix}monit_att_stafftype ORDER BY id";
            } else  {
                $strsql = "SELECT b.id, b.name FROM {$CFG->prefix}monit_att_stst a INNER JOIN {$CFG->prefix}monit_att_stafftype b ON a.stafftypeid=b.id
                           WHERE edutypeid=$edutypeid";
            }
            if ($arr_stfts =  get_records_sql($strsql))	{
            	foreach ($arr_stfts as $astf) 	{
            		$choices[$astf->id] = $astf->name;
            	}
            }
            choose_from_menu ($choices, "stafftypeid".$i, $appointment->stafftypeid, "");

            
            echo '<tr><th>'. get_string("meetingid", 'block_mou_att') . '</th><td>';
            unset($choices);
            if($meetings = get_records_select('monit_att_meeting_ak', "level_ak = 0 and yearid>=$yid", 'date_ak', 'id, level_ak, date_ak')) 	{
            	foreach ($meetings as $meeting)	  {
       				$choices[$meeting->id] = convert_date($meeting->date_ak, 'en', 'ru');
    		        //$choices[$meeting->id] = get_string('meet'.$meeting->level, 'block_mou_att') . ' ' . $choices[$meeting->id];
                }    
        	} else {
        	    $choices[1] = date('d.m.Y');
        	}
	
            choose_from_menu ($choices, "meetingid".$i, $appointment->meetingid, "");
            $appointmentidi = 'appointmentid' . $i; 
            echo "<input type=\"hidden\" name=\"{$appointmentidi}\" value=$appointment->id />";
            echo '</td></tr>';
              
            echo '<tr><th>'. get_string("predstavlenie", 'block_mou_att') . " ($strmaxsize)". '</th><td>';                

	        //echo "<p>$struploadafile($strmaxsize):";
	        //helpbutton('uploadmanual', $struploadafile, 'mou');
	        upload_print_form_fragment(1, array('preddoc'.$appointment->id), false, null, 0, $CFG->maxbytes, false);
            $filearea = "0/users/att/$rid/$uid/_".$appointment->id;
            $delurlparam = "rid=$rid&oid=$oid&uid=$uid&cid=-1&stft=$appointment->id&yid=$yid&typeou=$typeou";    
            $output = show_upload_files ($filearea, $delurlparam);
            echo '<br>'.$output;

            echo '</td></tr>'; 
            
            $i++;          
        }
    }    
?>

<tr><td colspan="2"><hr /></td></tr>
<tr>
    <th><?php print_string("city") ?><font color="red">*</font>:</th>
    <td>
    <input type="text" name="city" size="50" alt="<?php print_string("city") ?>" maxlength="50" value="<?php p($user->city) ?>" />
    <?php if (isset($err["city"])) formerr($err["city"]); ?>
    </td>
</tr>
<tr>
    <th>E-mail<font color="red">*</font>:</th>
    <td>
    <input type="text" name="email" size="30" alt="<?php print_string("email") ?>" maxlength="100" value="<?php p($user->email) ?>" />
    <?php if (isset($err["email"])) formerr($err["email"]); ?>
    </td>
</tr>
<tr>
    <th><?php print_string("phone") ?>:</th>
    <td>
    <input type="text" name="phone1" size="25" alt="<?php print_string("phone") ?>" maxlength="20" value="<?php p($user->phone1) ?>" />
    <?php if (isset($err["phone1"])) formerr($err["phone1"]); ?>
    </td>
</tr>
<tr>
    <th><?php print_string('mobilephone', 'block_monitoring') ?>:</th>
    <td>
    <input type="text" name="phone2" size="25" alt="<?php print_string("phone") ?>" maxlength="20" value="<?php p($user->phone2) ?>" />
    <?php if (isset($err["phone2"])) formerr($err["phone2"]); ?>
    </td>
</tr>
<tr>
    <th><?php print_string("icqnumber") ?>:</th>
    <td><input type="text" name="icq" size="25" alt="<?php print_string("icqnumber") ?>" maxlength="15" value="<?php p($user->icq) ?>" />
    <?php if (isset($err["icq"])) formerr($err["icq"]); ?>
    </td>
</tr>
<tr>
    <th><?php print_string("userdescription") ?>:</th>
    <td><?php
        if (isset($err["description"])) {
            formerr($err["description"]);
            echo "<br />";
        }
        print_textarea(false, 3, 80, 80, 3, 'description', "$user->description");
        helpbutton("text", get_string("helptext"));
    ?>
    </td>
</tr>

<?php
   echo '<tr><td colspan="2"><hr /></td></tr>';

// if (isadmin()) {
// if ($admin_is || $region_operator_is || $rayon_operator_is || $school_operator_is || $college_operator_is) {

    $theadmin = get_admin();
    $adminself = (($theadmin->id == $USER->id) and ($USER->id == $user->id));
    echo '<tr>';
    echo '<th>'.get_string('username').'<font color="red">*</font>:</th>';
    if ($adminself || is_internal_auth($user->auth) ){
        echo "<td><input type=\"text\" name=\"username\" size=\"30\" alt=\"".get_string("username")."\" value=\"";
        p($user->username);
        echo "\" />";
        if (isset($err["username"])) formerr($err["username"]);
    } else {
        echo "<td>";
        p($user->username);
        echo "<input type=\"hidden\" name=\"username\" value=\"";
        p($user->username);
        echo "\" />";
    }
    echo "</td>";
    echo "</tr>\n";

    echo '<tr>';
    echo '<th>'.get_string('newpassword').':</th>';
    echo "<td><input type=\"text\" name=\"newpassword\" size=\"30\" alt=\"".get_string("newpassword")."\" value=\"";
    if (isset($user->newpassword)) {
        p($user->newpassword);
    }
    echo "\" />";
    if (isset($err["newpassword"])) {
        formerr($err["newpassword"]);
    } else if (empty($user->newpassword)) {
        echo "<small>(".get_string("leavetokeep").")</small>";
    }
    echo "</td>";
    echo "</tr>\n";

    echo '<tr>';
    echo '<th>Последний заданный пароль:</th>';
    echo "<td>$user->pswtxt</td>";
    echo "</tr>\n";


?>
<tr>
    <td colspan="2" style="text-align: center;"><input type="submit" value="<?php print_string("updatemyprofile") ?>" /></td>
</tr>
</table>
</form>
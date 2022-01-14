<?php // $Id: shortinfo.php,v 1.5 2014/06/16 12:00:06 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->libdir.'/filelib.php');    
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');    
    require_once('../../mou_accredit/lib_accredit.php');    
    require_once('../lib_att2.php');
    require_once('lib_local.php');

    $currenttab = 'shortinfo';
    include('tabsatt.php');
    
    print_simple_box_start_old('center', '70%', '#ffffff', 0);
    
    print_shortinfo_header($uid, $stft);
     
    print_simple_box_end_old();

   $table = table_attestation($rid, $oid, $uid, $typeou, $stft, $edutype, $cyid, $lastcyid, 'shortinfo');
   if (isset($table->data))	{
         echo '<form name="addform" method="post" action="shortinfo.php">';
         print_color_table($table);
?>         
			    <div align="center">
			            <input type="hidden" name="cyid" value="<?php echo $cyid ?>" />
					    <input type="hidden" name="yid" value="<?php echo $yid ?>" />
						<input type="hidden" name="rid" value="<?php echo $rid ?>" />
						<input type="hidden" name="oid" value="<?php echo $oid ?>" />
						<input type="hidden" name="uid" value="<?php echo $uid ?>" />
						<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
						<input type="hidden" name="stft" value="<?php echo $stft?>" />                            
				        <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>">
				<input type="hidden" name="action" value="wordshortinfo" />
				<input type="submit" name="downloadword" value="<?php print_string('downloadword', 'block_mou_att')?>">
			    </div>
			  </form>
<?php         
	} 	else {
		notice('Критерии не найдены!');
	}

    print_footer();



function  print_shortinfo_header($uid, $stft, $output=true)
{   
    global $CFG, $user, $staff, $rayon, $ou;
    
    $stroutput = '';
    
    $teacher = get_record_select('monit_att_staff', "userid = $uid", 'id, totalstanding');
    $appointment = get_record_select ('monit_att_appointment', "staffid={$teacher->id} and stafftypeid=$stft", 'standing_this, qualify, qualify_date, qualifynow');

    $printstr = get_string('totalstanding', 'block_mou_att');
    if ($teacher->totalstanding)    {
        $stroutput .= $printstr . ': <b>' . $teacher->totalstanding . '</b>';
    } else {
        $stroutput .= $printstr . ': -';
    }    
    
    $profilefields = array('standing_this', 'qualify', 'qualify_date', 'qualifynow');
    $profiletype   = array('real', 'text', 'date', 'text');
    foreach ($profilefields as $key => $pf) {
       $printval = $appointment->{$pf};
       if ($profiletype[$key] == 'date')    {
			if ($appointment->{$pf} == '0000-00-00')	{
				$printval = '-';
			} else {
		    	$printval = convert_date($appointment->{$pf}, 'en', 'ru');
		    }
       } 
       $printstr = get_string($pf, 'block_mou_att');
       $stroutput .= '<br>' . $printstr . ': <b>' . $printval . '</b>';
    }
    
    if ($output)    {
        echo $stroutput;
    } else {
        $strinfo = fullname($user) . ' <br>' . $staff->appointment . ' <br>' ;
        $strinfo .=  $ou->name . '<br>(' . $rayon->name. ')<br> ';
        // print_heading($strinfo, 'center', 4);
        $strinfo = '<H4 align=center>' . $strinfo . '</H4>';
        $stroutput = $strinfo . $stroutput; 
    } 
    
    return $stroutput;
}


function print_shortinfo_to_word($table, $frm)
{
    global $CFG, $staff;
    	
   	header("Content-type: application/vnd.ms-word");
	header("Content-Disposition: attachment; filename=\"shortinfo_{$frm->uid}.doc\"");	
	header("Expires: 0");
	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
	header("Pragma: public");
    
   //$numcolumn = count ($table->columnwidth) - $lastcols;
    
    $buffer = '<html xmlns:v="urn:schemas-microsoft-com:vml"
	xmlns:o="urn:schemas-microsoft-com:office:office"
	xmlns:w="urn:schemas-microsoft-com:office:word"
	xmlns="http://www.w3.org/TR/REC-html40">
	<head>
	<meta http-equiv=Content-Type content="text/html; charset=utf-8">
	<meta name=ProgId content=Word.Document>
	<meta name=Generator content="Microsoft Word 11">
	<meta name=Originator content="Microsoft Word 11">
	<title>Аттестация кадров</title>
    <!--[if gte mso 9]><xml>
     <w:WordDocument>
      <w:View>Print</w:View>
      <w:GrammarState>Clean</w:GrammarState>
      <w:ValidateAgainstSchemas/>
      <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
      <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
      <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
      <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
     </w:WordDocument>
    </xml><![endif]--><!--[if gte mso 9]><xml>
     <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
     </w:LatentStyles>
    </xml><![endif]-->
    <style>
    <!--
     /* Style Definitions */
     p.MsoNormal, li.MsoNormal, div.MsoNormal
    	{mso-style-parent:"";
    	margin:0cm;
    	margin-bottom:.0001pt;
    	mso-pagination:widow-orphan;
    	font-size:12.0pt;
    	font-family:"Times New Roman";
    	mso-fareast-font-family:"Times New Roman";}
    span.GramE
    	{mso-style-name:"";
    	mso-gram-e:yes;}
    @page Section1
    	{size:841.9pt 595.3pt;
    	mso-page-orientation:landscape;
    	margin:1.0cm 2.0cm 42.55pt 2.0cm;
    	mso-header-margin:35.45pt;
    	mso-footer-margin:35.45pt;
    	mso-paper-source:0;}
    div.Section1
    	{page:Section1;}
    -->
    </style>
    <!--[if gte mso 10]>
    <style>
     /* Style Definitions */
     table.MsoNormalTable
    	{mso-style-name:"Обычная таблица";
    	mso-tstyle-rowband-size:0;
    	mso-tstyle-colband-size:0;
    	mso-style-noshow:yes;
    	mso-style-parent:"";
    	mso-padding-alt:0cm 5.4pt 0cm 5.4pt;
    	mso-para-margin:0cm;
    	mso-para-margin-bottom:.0001pt;
    	mso-pagination:widow-orphan;
    	font-size:10.0pt;
    	font-family:"Times New Roman";
    	mso-ansi-language:#0400;
    	mso-fareast-language:#0400;
    	mso-bidi-language:#0400;}
    </style>
    <![endif]-->
    </head>    
    <body lang=RU>
    <div class=Section1>';
        
    $buffer .= print_shortinfo_header($frm->uid, $frm->stft, false);
    
    $strselect = "staffid = $staff->id AND stafftypeid = $frm->stft";

    if ($atts = get_records_select('monit_att_attestation', $strselect, 'id, isprint' ))	 {
        foreach ($atts as $att) {
            set_field('monit_att_attestation', 'isprint', 0, 'id', $att->id);
        }    
    }    

    
    // $buffer .= '<br><br>';
    // echo '<pre>'; print_r($frm); echo '</pre>'; 
    
    foreach ($frm as $key => $value)    {
        echo $value . '<br>';
        if ($value == 'ok')  {
            $td = $table->data[$key];
            $buffer .= '<p>' . $td[1] . '</p>';
            
            $ids = explode ('_', $key);
            
            set_field('monit_att_attestation', 'isprint', 1, 'staffid', $staff->id, 'criteriaid', $ids[1]);   
            // $buffer .=  $td[1] . '<br>';
        }
    }
    
    $buffer .= '</div></body></html>';
	print $buffer;
  
   
}    


?>



<?php // $Id: expertzakl.php,v 1.10 2014/06/16 12:00:06 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->libdir.'/filelib.php');    
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../../mou_accredit/lib_accredit.php');        
    require_once('../lib_att2.php');
    require_once('lib_local.php');
    require_once('names.php');
    
    $currenttab = 'expertzakl';
    include('tabsatt.php');

    $message1 = '<b>ВНИМАНИЕ ЭКСПЕРТАМ!!! <br />
    В проекте экспертного заключения необходимо обязательно проверить правильность записи Ф.И.О. аттестуемого в дательном и родительном падежах. 
    <br>Сложные фамилии, имена и отчества система может сформировать в дательном или родительном падеже неправильно.<br />
    Поэтому после скачивания экспертного заключения в формате Word необходимо исправить Ф.И.О. непосредственно в текстовом процессоре MS Word <br />
    и только потом печатать экспертное заключение.</b>';
    notify ($message1);
?>         
    <form name="addform" method="post" action="expertzakl.php">
		    <div align="center">
				    <input type="hidden" name="yid" value="<?php echo $yid ?>" />
                    <input type="hidden" name="cyid" value="<?php echo $cyid ?>" />
					<input type="hidden" name="rid" value="<?php echo $rid ?>" />
					<input type="hidden" name="oid" value="<?php echo $oid ?>" />
					<input type="hidden" name="uid" value="<?php echo $uid ?>" />
					<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
					<input type="hidden" name="stft" value="<?php echo $stft?>" />                            
			        <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>">
			<input type="hidden" name="action" value="wordexpertzakl" />
			<input type="submit" name="downloadword" value="<?php print_string('downloadword', 'block_mou_att')?>">
		    </div>
	  </form>
      <p></p>
<?php         
        
    print_simple_box_start_old('center', '70%', '#ffffff', 0);
    
    $strout = print_expertzakl_header($uid, $stft);
    echo $strout;
     
    print_simple_box_end_old();

   $table = table_attestation($rid, $oid, $uid, $typeou, $stft, $edutype, $cyid, $lastcyid, 'expertzakl');
   if (isset($table->data))	{
         
        print_color_table($table);
         
        print_simple_box_start_old('center', '70%', '#ffffff', 0);
        
        $strout = print_expertzakl_footer($uid, $stft, $table->sum);
        echo $strout;     
        
        print_simple_box_end_old();
         
?>         
            <form name="addform" method="post" action="expertzakl.php">
				    <div align="center">
						    <input type="hidden" name="cyid" value="<?php echo $cyid ?>" />
							<input type="hidden" name="rid" value="<?php echo $rid ?>" />
							<input type="hidden" name="oid" value="<?php echo $oid ?>" />
							<input type="hidden" name="uid" value="<?php echo $uid ?>" />
							<input type="hidden" name="typeou" value="<?php echo $typeou ?>" />
							<input type="hidden" name="stft" value="<?php echo $stft?>" />                            
					        <input type="hidden" name="sesskey" value="<?php echo $USER->sesskey ?>">
					<input type="hidden" name="action" value="wordexpertzakl" />
					<input type="submit" name="downloadword" value="<?php print_string('downloadword', 'block_mou_att')?>">
				    </div>
			  </form>
<?php         

        notify ($message1);
	} 	else {
		notice('Критерии не найдены!');
	}

    print_footer();




function  print_expertzakl_header($uid, $stft, $output=true)
{   
    global $CFG, $user, $staff, $rayon, $ou;
    
    if (isset($user->firstname) && !empty($user->firstname)) {
     	   list($f,$s) = explode(' ', $user->firstname);
           $user->firstname = $f;
           $user->secondname = trim($s);
    } else {
           $user->secondname = '';
    }
    $user->dativecase  =  dativecase($user->lastname, $user->firstname, $user->secondname);
    
    $user->roditelcase =  roditelcase($user->lastname, $user->firstname, $user->secondname);
    
    get_dative_case_qualify($staff->qualifynow);
    
    $staffappointment = get_dative_case_appointment($staff->appointment);
        
    $stroutput = "<p class=MsoNormal align=center style='margin-bottom:0cm;margin-bottom:.0001pt;
                text-align:center;line-height:normal'><b><span style='font-size:13.0pt;font-family:\"Times New Roman\"'>Экспертное заключение <br> 
                по результатам анализа профессиональной деятельности<br>";
    $stroutput .= $staffappointment . ' <br> (' . $ou->name . ')<br>';

//    $stroutput .= fullname($user) . ', <br>';

    $stroutput .= $user->roditelcase . ', <br>'; 
    
	if ($user->sex)	{
		$stroutput .= 'претендующего';
	} else {
	    $stroutput .= 'претендующей';
	}   
    
    $stroutput .= " на $user->strcateg квалификационную категорию</span></b></p>";

    $stroutput .= "<p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;text-align:
justify;text-indent:1.0cm;line-height:normal'><span style='font-family:\"Times New Roman\"'>Экспертная
группа Главной аттестационной комиссии департамента образования Белгородской области проанализировала предоставленные документально зафиксированные результаты профессиональной деятельности педагогического
работника в межаттестационный период.</span></p>";

    return $stroutput;
}


function  print_expertzakl_footer($uid, $stft, $sum, $output=true)
{   
    global $CFG, $user, $staff, $stafftype, $rayon, $ou;
    
    $truncateappointment = truncate_appointment($stafftype->name);
    $truncateappointment = trim($truncateappointment);

    if ($user->lastname == 'Мижурицкая')    {
        $user->dativecase = 'Мижурицкой Валентине Ивановне';
    } 
    
    $stroutput = "<p align=right><b>Сумма баллов: $sum<br>Необходимое количество баллов: __</b></p>";
    $stroutput .= "<p><b>Решение экспертной группы: </b><br>


 Рекомендовать ГАК присвоить <u> $user->dativecase $user->strcateg квалификационную категорию </u>  по должности «<u>$truncateappointment</u>», т.к. уровень профессиональной компетентности педагога соответствует требованиям, предъявляемым к <u>$user->strcateg2  квалификационной категории</u>, и подтверждается показателями, исчисляемыми в баллах.<br>
<p><b >Примечание:</b>
________________________________________________________________<br>
___________________________________________________________________________

<br>
<b >Рекомендации:</b>
_____________________________________________________________<br>
___________________________________________________________________________


<p><b>Руководитель экспертной группы: </b>

<br><b ><span style='mso-spacerun:yes'>                                                                         </span>
</b>________________/<span style='mso-spacerun:yes'>  </span>__________________

<br>
<b >Члены экспертной группы: </b>

<br>
<span style='mso-spacerun:yes'>                          </span>
<span style='mso-spacerun:yes'>                                                 </span>________________/
<span style='mso-spacerun:yes'>  </span>___________________ 

<br>
<span style='mso-spacerun:yes'>                                                                          </span>________________/
<span style='mso-spacerun:yes'> 
</span>___________________ </span>

<br><span style='mso-spacerun:yes'>                                                     
</span><span style='mso-spacerun:yes'>                     </span>________________/<span
style='mso-spacerun:yes'>  </span>___________________</span></p>

";
    
    
    return $stroutput;
}


function print_expertzakl_to_word($table, $uid)
{
    global $CFG, $user, $stft;
    	
   	header("Content-type: application/vnd.ms-word");
	header("Content-Disposition: attachment; filename=\"expertzakl_{$uid}.doc\"");	
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
    	margin-top:0cm;
    	margin-right:0cm;
    	margin-bottom:10.0pt;
    	margin-left:0cm;
    	line-height:115%;
    	mso-pagination:widow-orphan;
    	font-size:11.0pt;
    	font-family:Calibri;
    	mso-fareast-font-family:"Times New Roman";
    	mso-bidi-font-family:"Times New Roman";}
    p
    	{mso-margin-top-alt:auto;
    	margin-right:0cm;
    	mso-margin-bottom-alt:auto;
    	margin-left:0cm;
    	mso-pagination:widow-orphan;
    	font-size:12.0pt;
    	font-family:"Times New Roman";
    	mso-fareast-font-family:"Times New Roman";}
    p.MsoAcetate, li.MsoAcetate, div.MsoAcetate
    	{mso-style-noshow:yes;
    	mso-style-link:"Balloon Text Char";
    	margin:0cm;
    	margin-bottom:.0001pt;
    	mso-pagination:widow-orphan;
    	font-size:8.0pt;
    	font-family:Tahoma;
    	mso-fareast-font-family:"Times New Roman";}
    span.BalloonTextChar
    	{mso-style-name:"Balloon Text Char";
    	mso-style-noshow:yes;
    	mso-style-locked:yes;
    	mso-style-link:"Текст выноски";
    	mso-ansi-font-size:8.0pt;
    	mso-bidi-font-size:8.0pt;
    	font-family:Tahoma;
    	mso-ascii-font-family:Tahoma;
    	mso-hansi-font-family:Tahoma;
    	mso-bidi-font-family:Tahoma;}
    span.SpellE
    	{mso-style-name:"";
    	mso-spl-e:yes;}
    span.GramE
    	{mso-style-name:"";
    	mso-gram-e:yes;}
    @page Section1
    	{size:595.3pt 841.9pt;
    	margin:49.65pt 42.55pt 49.65pt 70.9pt;
    	mso-header-margin:35.4pt;
    	mso-footer-margin:35.4pt;
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
    
    
    $buffer = print_expertzakl_header($uid, $stft);
    // $buffer .= '<br><br>';
    // echo '<pre>'; print_r($frm); echo '</pre>'; 
    $buffer .= print_table_to_word($table, 0, 'doc', false);

    $buffer .= print_expertzakl_footer($uid, $stft, $table->sum);    

    $buffer .= '</div></body></html>';

	print $buffer;    
}    



function get_dative_case_qualify($qualify)
{    
    global $user;
    
    $qualify = mb_strtolower ( $qualify, 'UTF-8');
    $user->strcateg = '__________';
    $user->strcateg2 = '__________';
    $pos = mb_strpos($qualify, 'перв', 0, 'UTF-8');
    if ($pos !== false) {
        $user->strcateg = 'первую';
        $user->strcateg2 = 'первой';
    } else {        
        $pos = mb_strpos($qualify, 'высш', 0, 'UTF-8');
        if ($pos !== false) {
            $user->strcateg = 'высшую';
            $user->strcateg2 = 'высшей';
        } else {
            $pos = mb_strpos($qualify, 'втор', 0, 'UTF-8');
            if ($pos !== false) {
                $user->strcateg = 'вторую';
                $user->strcateg2 = 'второй';
            }    
        }
    } 
    // return $strcateg;
}  



function get_dative_case_appointment($name)
{    
    global $user;
    
    $name = str_ireplace ('учитель', 'учителя', $name);
	$name = str_ireplace ('воспитатель', 'воспитателя', $name);
	$name = str_ireplace ('методист', 'методиста', $name);
	$name = str_ireplace ('преподаватель', 'преподавателя', $name);
	$name = str_ireplace ('руководитель', 'руководителя', $name);
	$name = str_ireplace ('педагог', 'педагога', $name);
	$name = str_ireplace ('логопед', 'логопеда', $name);
	$name = str_ireplace ('тренер', 'тренера', $name);
	$name = str_ireplace ('старший', 'старшего', $name);
	$name = str_ireplace ('вожатый', 'вожатого', $name);    
     

    return $name;
    // return $strcateg;
}  

function truncate_appointment($appointment)
{
	$name = str_ireplace ('школы', '', $appointment);
	$name = str_ireplace ('СПО НПО', '', $name);
	$name = str_ireplace ('ДОУ', '', $name);
	$name = str_ireplace ('музыкального колледжа', '', $name);
	$name = str_ireplace ('ДШИ, ДХИ, ДМШ', '', $name);
	$name = str_ireplace ('школы-интерната', '', $name);
	$name = str_ireplace ('ДОД', '', $name);

    return $name;
}          

?>



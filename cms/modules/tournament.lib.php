<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/**
 * @package pragyan
 * @copyright (c) 2013 Delta Webteam NIT Trichy
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
/**
 * Form doesnt have ability to associate or unassociate with a group.
 * That is done through groups.
 *
 * If it is not associated with a group
 * 		When associated, copy all regiestered users to groups table -> done by groups
 * 		Have a function which returns userids of all users registered in a form.
 * *
 * If login required is changed from off to on at any time,
 * 		then give warning, and delete all anonymous entries.
 *
 * If loginrequired is turned from on to off, allow only if it is not associted with a group
 * give error msg with the group namem and don't allow him
 *
 * To put deleteall registrants in edit registrants
 *
 *TODO: one maintenance script : If user doesn't exist in form_regdata, remove from form_elementdata
 *
 */
 
 /*
  * TODO:: 
  * 
  * 
  * URGENT:: Send user a confirmation mail on registration
  * 
  * 
  * 
  * */
global $sourceFolder;
global $moduleFolder;
/*
require_once("$sourceFolder/$moduleFolder/quickregister/editform.php");
require_once("$sourceFolder/$moduleFolder/quickregister/editformelement.php");
require_once("$sourceFolder/$moduleFolder/quickregister/registrationformgenerate.php");
require_once("$sourceFolder/$moduleFolder/quickregister/registrationformsubmit.php");
require_once("$sourceFolder/$moduleFolder/quickregister/viewregistrants.php");
*/
 class tournament implements module {
	private $userId;
	private $moduleComponentId;
	private $action;

	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->modulecomponentid = $gotmoduleComponentId;
		$this->action = $gotaction;
		$footer='<div style="position:absolute;right:20px;bottom:20px;">Developed by <a href="http://www.facebook.com/pbornskater">Parameswaran</a></div>';
		if($this->action=="view")
			return $this->actionView().$footer;
		if($this->action=="upload")
			return $this->actionUpload().$footer;
		if($this->action=="edit")
			return $this->actionEdit().$footer;
		if($this->action=="ageedit")
			return $this->actionAgeedit().$footer;			
		if($this->action=="genderedit")
			return $this->actionGenderedit().$footer;			
		if($this->action=="clubedit")
			return $this->actionClubedit().$footer;			

	}
	public function findgroup($age)
{
	/*
	if($age>=16)$grp="above 16";
	else if($age>=14)$grp="14 to 16";
	else if($age>=12)$grp="12 to 14";
	else if($age>=10)$grp="10 to 12";
	else if($age>=18)$grp="8 to 10";
	else if($age>=6)$grp="6 to 8";
	else if($age<6)$grp="below 6";
	*/
	if($age>=16)$grp="16";
	else if($age>=14)$grp="1416";
	else if($age>=12)$grp="1214";
	else if($age>=10)$grp="1012";
	else if($age>=18)$grp="810";
	else if($age>=6)$grp="68";
	else if($age<6)$grp="06";
	return $grp;
}


	public function actionView() {
		global $sourceFolder;		global $moduleFolder;
		$html="Action view";
		return $html;
	}
	public function actionEdit() {
		global $sourceFolder;		global $moduleFolder;
		if($_POST['update']==1){
			$query=$_POST['query'];//mysql_real_escape_string($_GET['query']);
			$html="Your update query was ".$query."<br />";
			mysql_query($query);
			if(mysql_error())$html.='Has errors '.mysql_error()."<br />";
			else $html.="Executed successfully"."<br />";
			return $html;
		}
		$html="<h2> Edit the list </h3>";
$query="SELECT * FROM `tournament_participants` ORDER BY `name`";
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res))
$html.=<<<HTML
<style>
input{width:auto!important;background-color:white;color:black;size:10;}
</style>
<input value="$row[name]"><input value="$row[gender]"><input value="$row[club]">
<input value="$row[group]"><input value="$row[dob]"><input value="$row[blood]">  
<input value="$row[phone1]"><input value="$row[phone2]">
<input type="checkbox" checked=$row[rink1]><input type="checkbox" checked=$row[rink2]>
<input type="checkbox" checked=$row[rink3]><input type="checkbox" checked=$row[rink4]>
<input type="checkbox" checked=$row[rink5]><input type="checkbox" checked=$row[rink6]>
<input type="checkbox" checked=$row[rink7]><input type="checkbox" checked=$row[rink8]>
<input type="checkbox" checked=$row[rink9]><input type="checkbox" checked=$row[rink10]>
<br/>
HTML;
		return $html;
	}
	public function actionAgeedit() {
		global $sourceFolder;		global $moduleFolder;
$html=<<<HTML
<h2> Edit By Age Group </h3>
				<style>
input{width:auto;background-color:white;color:black;}
.participant_div{display:none;}
</style>
<script>
$(function(){
$(".participant").change(function(){

if($(this).is(":checkbox")!=true)
var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+$(this).attr('value')+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
else
var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+$(this).attr('checked')+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
$.ajax({type:"POST",url:"./+edit",data:{update:1,query:query}});
});
$(".group").change(function(){
	$("."+$(this).attr("id")).slideToggle();
});
});
</script>
Below 6<input type='checkbox' class='group' id='06' >&nbsp;&nbsp;&nbsp;&nbsp;
6  - 8<input type='checkbox' class='group' id='68' >&nbsp;&nbsp;&nbsp;&nbsp;
8  - 10<input type='checkbox' class='group' id='810' >&nbsp;&nbsp;&nbsp;&nbsp;
10 - 12<input type='checkbox' class='group' id='1012' >&nbsp;&nbsp;&nbsp;&nbsp;
12 - 14<input type='checkbox' class='group' id='1214' >&nbsp;&nbsp;&nbsp;&nbsp;
14 - 16<input type='checkbox' class='group' id='1416' >&nbsp;&nbsp;&nbsp;&nbsp;
above 16<input type='checkbox' class='group' id='16' >
<div id='response'></div>
HTML;
		$query="SELECT * FROM `tournament_participants` ORDER BY `group`,`name`";
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res))
$html.=<<<HTML
<div class='$row[group] participant_div'>
<input class='participant' name='name' data_id="$row[id]" value="$row[name]"><input class='participant' name='gender' data_id="$row[id]" value="$row[gender]"><input class='participant' name='club' data_id="$row[id]" value="$row[club]">
<input class='participant' name='group' data_id="$row[id]" value="$row[group]"><input class='participant' name='dob' data_id="$row[id]" value="$row[dob]"><input class='participant' name='blood' data_id="$row[id]" value="$row[blood]">  
<input class='participant' name='phone1' data_id="$row[id]" value="$row[phone1]"><input class='participant' name='phone2' data_id="$row[id]" value="$row[phone2]">
<input class='participant' name='rink1' data_id="$row[id]" type="checkbox" checked=$row[rink1]><input class='participant' name='rink2' data_id="$row[id]" type="checkbox" checked=$row[rink2]>
<input class='participant' name='rink3' data_id="$row[id]" type="checkbox" checked=$row[rink3]><input class='participant' name='rink4' data_id="$row[id]" type="checkbox" checked=$row[rink4]>
<input class='participant' name='rink5' data_id="$row[id]" type="checkbox" checked=$row[rink5]><input class='participant' name='rink6' data_id="$row[id]" type="checkbox" checked=$row[rink6]>
<input class='participant' name='rink7' data_id="$row[id]" type="checkbox" checked=$row[rink7]><input class='participant' name='rink8' data_id="$row[id]" type="checkbox" checked=$row[rink8]>
<input class='participant' name='rink9' data_id="$row[id]" type="checkbox" checked=$row[rink9]><input class='participant' name='rink10' data_id="$row[id]" type="checkbox" checked=$row[rink10]>
</div>
<br/>
HTML;
		return $html;
	}
	public function actionGenderedit() {
		global $sourceFolder;		global $moduleFolder;
$html=<<<HTML
<h2> Edit By Gender</h3>
				<style>
input{width:auto;background-color:white;color:black;}
.participant_div{display:none;}
</style>
<script>
$(function(){
$(".participant").change(function(){

if($(this).is(":checkbox")!=true)
var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+$(this).attr('value')+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
else
var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+$(this).attr('checked')+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
$.ajax({type:"POST",url:"./+edit",data:{update:1,query:query}});
});
$(".group").change(function(){	$("."+$(this).attr("id")).slideToggle();});
});
</script>
<div id='response'></div>
HTML;
$q="SELECT DISTINCT `gender` FROM `tournament_participants`";
$r=mysql_query($q);
while($rr=mysql_fetch_array($r))
$html.=$rr['gender']."<input type='checkbox' class='group' id='".$rr['gender']."' >&nbsp;&nbsp;&nbsp;&nbsp;";
$html.="<br />";
		$query="SELECT * FROM `tournament_participants` ORDER BY `gender`,`name`";
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res))
$html.=<<<HTML
<div class='participant_div $row[gender]'>
<input class='participant' name='name' data_id="$row[id]" value="$row[name]"><input class='participant' name='gender' data_id="$row[id]" value="$row[gender]"><input class='participant' name='club' data_id="$row[id]" value="$row[club]">
<input class='participant' name='group' data_id="$row[id]" value="$row[group]"><input class='participant' name='dob' data_id="$row[id]" value="$row[dob]"><input class='participant' name='blood' data_id="$row[id]" value="$row[blood]">  
<input class='participant' name='phone1' data_id="$row[id]" value="$row[phone1]"><input class='participant' name='phone2' data_id="$row[id]" value="$row[phone2]">
<input class='participant' name='rink1' data_id="$row[id]" type="checkbox" checked=$row[rink1]><input class='participant' name='rink2' data_id="$row[id]" type="checkbox" checked=$row[rink2]>
<input class='participant' name='rink3' data_id="$row[id]" type="checkbox" checked=$row[rink3]><input class='participant' name='rink4' data_id="$row[id]" type="checkbox" checked=$row[rink4]>
<input class='participant' name='rink5' data_id="$row[id]" type="checkbox" checked=$row[rink5]><input class='participant' name='rink6' data_id="$row[id]" type="checkbox" checked=$row[rink6]>
<input class='participant' name='rink7' data_id="$row[id]" type="checkbox" checked=$row[rink7]><input class='participant' name='rink8' data_id="$row[id]" type="checkbox" checked=$row[rink8]>
<input class='participant' name='rink9' data_id="$row[id]" type="checkbox" checked=$row[rink9]><input class='participant' name='rink10' data_id="$row[id]" type="checkbox" checked=$row[rink10]>
<br/>
</div>
HTML;
		return $html;
	}
	public function actionClubedit() {
		global $sourceFolder;		global $moduleFolder;
$html=<<<HTML
<h2> Edit By Club </h3>
				<style>
input{width:auto;background-color:white;color:black;}
.participant_div{display:none;}
</style>
<script>
$(function(){
$(".participant").change(function(){
if($(this).is(":checkbox")!=true)
var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+$(this).attr('value')+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
else
var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+$(this).attr('checked')+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
$.ajax({type:"POST",url:"./+edit",data:{update:1,query:query}});
});
$(".group").change(function(){	$("."+$(this).attr("id")).slideToggle();});
});
</script>
<div id='response'></div>
HTML;
$q="SELECT DISTINCT `club` FROM `tournament_participants`";
$r=mysql_query($q);
while($rr=mysql_fetch_array($r))
$html.=$rr['club']."<input type='checkbox' class='group' id='".$rr['club']."' >&nbsp;&nbsp;&nbsp;&nbsp;";
$html.="<br />";
		$query="SELECT * FROM `tournament_participants` ORDER BY `club`, `name`";
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res))
$html.=<<<HTML
<div class='participant_div $row[club]'>
<input class='participant' name='name' data_id="$row[id]" value="$row[name]"><input class='participant' name='gender' data_id="$row[id]" value="$row[gender]"><input class='participant' name='club' data_id="$row[id]" value="$row[club]">
<input class='participant' name='group' data_id="$row[id]" value="$row[group]"><input class='participant' name='dob' data_id="$row[id]" value="$row[dob]"><input class='participant' name='blood' data_id="$row[id]" value="$row[blood]">  
<input class='participant' name='phone1' data_id="$row[id]" value="$row[phone1]"><input class='participant' name='phone2' data_id="$row[id]" value="$row[phone2]">
<input class='participant' name='rink1' data_id="$row[id]" type="checkbox" checked=$row[rink1]><input class='participant' name='rink2' data_id="$row[id]" type="checkbox" checked=$row[rink2]>
<input class='participant' name='rink3' data_id="$row[id]" type="checkbox" checked=$row[rink3]><input class='participant' name='rink4' data_id="$row[id]" type="checkbox" checked=$row[rink4]>
<input class='participant' name='rink5' data_id="$row[id]" type="checkbox" checked=$row[rink5]><input class='participant' name='rink6' data_id="$row[id]" type="checkbox" checked=$row[rink6]>
<input class='participant' name='rink7' data_id="$row[id]" type="checkbox" checked=$row[rink7]><input class='participant' name='rink8' data_id="$row[id]" type="checkbox" checked=$row[rink8]>
<input class='participant' name='rink9' data_id="$row[id]" type="checkbox" checked=$row[rink9]><input class='participant' name='rink10' data_id="$row[id]" type="checkbox" checked=$row[rink10]>
<br/>
</div>
HTML;
		return $html;
	}
	public function actionUpload() {
		global $sourceFolder;		global $moduleFolder;
		if(!isset($_FILES["file"]["name"]))
{
$html=<<<HTML
Upload an excel sheet with list of skaters and appropriate data.<br /><br />Download the 
<a href='../../cms/$moduleFolder/tournament/sample.xls'> sample excel HERE</a>
<br />
<br />
<form action="" method="post"
	enctype="multipart/form-data">
	<label for="file">Filename:</label>
	<input type="file" name="file" id="file"><br /><br />
	<input type="submit" name="submit" value="Submit">
	</form>
HTML;
}
else {
include("./cms/$moduleFolder/tournament/excel.php");
include("./cms/$moduleFolder/tournament/config.php");
			 $ctr=0;
			$date = date_create();
			$timeStamp = date_timestamp_get($date);
			$table='tournament_participants';
			move_uploaded_file($_FILES["file"]["tmp_name"],"./cms/$moduleFolder/tournament/upload/temp/" . $_FILES["file"]["name"].$timeStamp);
			$excelData = readExcelSheet("./cms/$moduleFolder/tournament/upload/temp/" . $_FILES["file"]["name"].$timeStamp);
			//print_r($excelData);
			for($i=2;$i<=count($excelData);$i++) {
			if($excelData[$i][1] == NULL) continue;
	$ii=1;
			//print_r($excelData);
			$sno = mysql_real_escape_string($excelData[$i][$ii++]);
			$name= mysql_real_escape_string($excelData[$i][$ii++]);
			$gender= mysql_real_escape_string($excelData[$i][$ii++]);
			$dob=mysql_real_escape_string($excelData[$i][$ii++]);
			$bloodgrp= mysql_real_escape_string($excelData[$i][$ii++]);
			$club= mysql_real_escape_string($excelData[$i][$ii++]);
			$phone1= mysql_real_escape_string($excelData[$i][$ii++]);
			$phone2= mysql_real_escape_string($excelData[$i][$ii++]);
			//displayinfo($sno.$name.$gender.$dob.$bloodgrp.$club.$phone1.$phone2);
			$y=substr($dob,-4);
//			displayinfo("strlen is ".strlen($dob));
	if(strlen($dob)==10)
	{
	$m=substr($dob,3,2);
	$d=substr($dob,0,2);
	}
	else if(strlen($dob)==8)
	{
	$m=substr($dob,2,1);
	$d=substr($dob,0,1);
	}
	else if(strlen($dob)==9)
	{
	if($dob[2]=='/'||$dob[2]=='-')
	{
	$m=substr($dob,3,1);
	$d=substr($dob,0,2);
	}
	else 
	{
	$m=substr($dob,2,2);
	$d=substr($dob,0,1);
	}
	}
	$nowyear=date('Y');
	$nowyear=2013;
	$y=intval($y);

	$rinks=array();
	foreach ($eventname as $ev1)
	{
	$dd=mysql_real_escape_string($excelData[$i][$ii++]);
	$rinks[]=(($dd=='X'||$dd=='Y'||$dd=='x'||$dd=='y'||$dd=='.')?1:0);
	}

	$age=$nowyear-$y;
	$grp=$this->findgroup($age);
			$sql1="INSERT IGNORE INTO $table VALUES ('$regno','$name', '$age','$gender','$grp', '$y-$m-$d', '$phone1', '$phone2');";
			
	$sql="INSERT IGNORE INTO $table(`id`,`name`,`gender`,`club`, `group`,`dob`,`blood`, `phone1`, `phone2`";
	foreach ($eventname as $ev1)
	$sql.=",`".$ev1."`";
	$sql.=") VALUES ('','$name','$gender','$club','$grp','$y-$m-$d','$bloodgrp', '$phone1', '$phone2'";
	foreach ($rinks as $r1)
	$sql.=",".$r1."";
	$sql.=");";
	displayinfo($sql);
	
	$result=mysql_query($sql);
	//echo $sql."<br />";
	if(mysql_error())
	displayerror("ERROR WAS:".mysql_error());
	else $ctr++;
			}		
	displayinfo($ctr." entries added successfully");

}
		return $html;
	}

	public function createModule($compId) {
		global $sourceFolder, $moduleFolder;
		return true;
		}

	public function deleteModule($moduleComponentId){
		return true;
	}

	public function copyModule($moduleComponentId,$newId){
		return true;
	}
	
 }


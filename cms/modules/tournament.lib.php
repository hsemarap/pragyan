<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}
/**
 * @author D.Parameswaran <hsemarap@gmail.com>
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
		if($this->action=="scoring")
			return $this->actionScoring().$footer;
/*		
		if($this->action=="ageedit")
			return $this->actionAgeedit().$footer;			
		if($this->action=="genderedit")
			return $this->actionGenderedit().$footer;			
		if($this->action=="clubedit")
			return $this->actionClubedit().$footer;			
*/
	}
	public function findgroup($age)
	{
		if($age>=16)$grp="16";
		else if($age>=14)$grp="1416";
		else if($age>=12)$grp="1214";
		else if($age>=10)$grp="1012";
		else if($age>=18)$grp="810";
		else if($age>=6)$grp="68";
		else if($age<6)$grp="06";
		return $grp;
	}
	public function grouptostring($g)
	{
	$grp=array();
	$grp["06"]="Below 6";
	$grp["68"]="6 to 8";
	$grp["810"]="8 to 10";
	$grp["1012"]="10 to 12";
	$grp["1214"]="12 to 14";
	$grp["1416"]="14 to 16";
	$grp["16"]="Above 16";
	return $grp[$g.""];
	}

	public function actionView() {
		$html=<<<HTML
		<style>
		input{width:auto!important;background:white;color:black;size:10;}
		input[type=text]{width:90px!important;background:white;}
		input[type=checkbox]{width:20px!important;}
		.participant_div{padding:2px;background:none;}
		</style>
		<script>
		$(function(){
		$(".group").change(function(){
			$("."+$(this).attr("id")).slideToggle();
		});
		});
		</script>
		Age Group :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Below 6 <input type='checkbox' class='group' id='06'   checked>&nbsp;&nbsp;&nbsp;&nbsp;
		6  - 8  <input type='checkbox' class='group' id='68'   checked>&nbsp;&nbsp;&nbsp;&nbsp;
		8  - 10 <input type='checkbox' class='group' id='810'  checked>&nbsp;&nbsp;&nbsp;&nbsp;
		10 - 12 <input type='checkbox' class='group' id='1012' checked>&nbsp;&nbsp;&nbsp;&nbsp;
		12 - 14 <input type='checkbox' class='group' id='1214' checked>&nbsp;&nbsp;&nbsp;&nbsp;
		14 - 16 <input type='checkbox' class='group' id='1416' checked>&nbsp;&nbsp;&nbsp;&nbsp;
		above 16<input type='checkbox' class='group' id='16'   checked><br /><br />
		Club &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
HTML;
		$q="SELECT DISTINCT `club` FROM `tournament_participants`";
		$r=mysql_query($q);
		while($rr=mysql_fetch_array($r))
		$html.=$rr['club']."<input type='checkbox' class='group' id='".$rr['club']."' checked>&nbsp;&nbsp;&nbsp;&nbsp;";
		$html.="<br /><br />";
		$html.="Gender &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$q="SELECT DISTINCT `gender` FROM `tournament_participants`";
		$r=mysql_query($q);
		while($rr=mysql_fetch_array($r))
		$html.=$rr['gender']."<input type='checkbox' class='group' id='".$rr['gender']."' checked>&nbsp;&nbsp;&nbsp;&nbsp;";
		$html.="<br /><br />";

$query="SELECT * FROM `tournament_participants` ORDER BY `name`";
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res)){
			$grp=$this->grouptostring($row['group']);
			$temp=array();
			$temp[]="";
			for($i=1;$i<20;$i++)$temp[]=$row['rink'.$i]?"$i":" ";
$html.=<<<HTML
<div class='participant_div $row[gender] $row[club] $row[group]'>
<pre>
$row[name]  $row[gender]	$row[club]	$grp	$row[dob]	$row[blood]	$row[phone1] $row[phone2]	$temp[1]	$temp[2]	$temp[3]	$temp[4]	$temp[5]	$temp[6]	$temp[7]	$temp[8]	$temp[9]	$temp[10]
</pre>
</div>
HTML;
}
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
		$html=<<<HTML
		<h2> Edit the list </h3>
		<style>
		input{width:auto!important;background:white;color:black;size:10;}
		input[type=text]{width:90px!important;background:white;}
		input[type=checkbox]{width:20px!important;}
		.participant_div{padding:2px;background:none;}
		</style>
		<script>
		$(function(){
		$(".participant").change(function(){
			var curr=$(this).parent();
		if($(this).is(":checkbox")!=true)
		var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+$(this).attr('value')+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
		else
		var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+($(this).attr('checked')==true?1:0)+"' WHERE `id` = '"+$(this).attr('data_id')+"'";

		$.ajax({type:"POST",url:"./+edit",data:{update:1,query:query},success:function(){
			curr.animate({background:"rgb(50,150,50)"},100);
			curr.css("background","rgb(50,150,50)");
			setTimeout(function(){curr.animate({background:"transparent"},100);},1000);
			setTimeout(function(){curr.css("background","transparent");},1000);
		}});
		});
		$(".group").change(function(){
			$("."+$(this).attr("id")).slideToggle();
		});
		});
		</script>
		Age Group :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Below 6 <input type='checkbox' class='group' id='06'   checked>&nbsp;&nbsp;&nbsp;&nbsp;
		6  - 8  <input type='checkbox' class='group' id='68'   checked>&nbsp;&nbsp;&nbsp;&nbsp;
		8  - 10 <input type='checkbox' class='group' id='810'  checked>&nbsp;&nbsp;&nbsp;&nbsp;
		10 - 12 <input type='checkbox' class='group' id='1012' checked>&nbsp;&nbsp;&nbsp;&nbsp;
		12 - 14 <input type='checkbox' class='group' id='1214' checked>&nbsp;&nbsp;&nbsp;&nbsp;
		14 - 16 <input type='checkbox' class='group' id='1416' checked>&nbsp;&nbsp;&nbsp;&nbsp;
		above 16<input type='checkbox' class='group' id='16'   checked><br /><br />
		Club &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
HTML;
		$q="SELECT DISTINCT `club` FROM `tournament_participants`";
		$r=mysql_query($q);
		while($rr=mysql_fetch_array($r))
		$html.=$rr['club']."<input type='checkbox' class='group' id='".$rr['club']."' checked>&nbsp;&nbsp;&nbsp;&nbsp;";
		$html.="<br /><br />";
		$html.="Gender &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$q="SELECT DISTINCT `gender` FROM `tournament_participants`";
		$r=mysql_query($q);
		while($rr=mysql_fetch_array($r))
		$html.=$rr['gender']."<input type='checkbox' class='group' id='".$rr['gender']."' checked>&nbsp;&nbsp;&nbsp;&nbsp;";
		$html.="<br /><br />";

$query="SELECT * FROM `tournament_participants` ORDER BY `name`";
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res)){
			$grp=$this->grouptostring($row['group']);
			$temp=array();
			$temp[]="";
			for($i=1;$i<20;$i++)$temp[]=$row['rink'.$i]?"checked":"";
$html.=<<<HTML
<div class='participant_div $row[gender] $row[club] $row[group]'>
<input type='text' class='participant' name='name' data_id="$row[id]" value="$row[name]"><input type='text' class='participant' name='gender' data_id="$row[id]" value="$row[gender]"><input type='text' class='participant' name='club' data_id="$row[id]" value="$row[club]">
<input type='text' class='participant' disabled name='group' data_id="$row[id]" value="$grp"><input type='text' class='participant' name='dob' data_id="$row[id]" value="$row[dob]"><input type='text' class='participant' name='blood' data_id="$row[id]" value="$row[blood]">  
<input type='text' class='participant' name='phone1' data_id="$row[id]" value="$row[phone1]"><input type='text' class='participant' name='phone2' data_id="$row[id]" value="$row[phone2]">
1<input class='participant' name='rink1' data_id="$row[id]" type="checkbox"  $temp[1]>2<input class='participant' name='rink2' data_id="$row[id]" type="checkbox"  $temp[2]>
3<input class='participant' name='rink3' data_id="$row[id]" type="checkbox"  $temp[3]>4<input class='participant' name='rink4' data_id="$row[id]" type="checkbox"  $temp[4]>
5<input class='participant' name='rink5' data_id="$row[id]" type="checkbox"  $temp[5]>6<input class='participant' name='rink6' data_id="$row[id]" type="checkbox"  $temp[6]>
7<input class='participant' name='rink7' data_id="$row[id]" type="checkbox"  $temp[7]>8<input class='participant' name='rink8' data_id="$row[id]" type="checkbox"  $temp[8]>
9<input class='participant' name='rink9' data_id="$row[id]" type="checkbox"  $temp[9]>10<input class='participant' name='rink10' data_id="$row[id]" type="checkbox"  $temp[10]>
<br/>
</div>
HTML;
}
		return $html;
	}

	public function actionScoring() {
		global $sourceFolder;		global $moduleFolder;
		$html='';
		$basefolder=dirname($_SERVER[SCRIPT_NAME]);
		$html.=<<<HTML
		<style>
		input{width:auto!important;background:white;color:black;size:10;}
		input[type=text]{width:80px!important;background:white;}
		input[type=checkbox]{width:20px!important;}
		.participant_div{padding:2px;background:none;}
		</style>
		<script type='text/javascript' src="$basefolder/cms/$moduleFolder/tournament/jquery.cookie.js"></script>
		<script>
		$(function(){
			//$("#content").load("$basefolder/cms/$moduleFolder/tournament/tournament_score.php?by=name");
			var cookie_sort,cookie_age,cookie_rinkno;
			cookie_sort=$.cookie("sortby"),cookie_age=$.cookie("agegroup"),cookie_rinkno=$.cookie("rinkno");			
			if(cookie_sort==undefined)$.cookie("sortby","name");
			if(cookie_age==undefined)$.cookie("agegroup","06");
			if(cookie_rinkno==undefined)$.cookie("rinkno","1");
			cookie_sort=$.cookie("sortby"),cookie_age=$.cookie("agegroup"),cookie_rinkno=$.cookie("rinkno");			
			$(".sortby").each(function(){if(cookie_sort==$(this).attr("value"))$(this).attr("checked","1");});
			$(".agegroup > option").each(function(){if(cookie_age==$(this).attr("value"))$(this).attr("selected","true");});
			$(".rinkno > option").each(function(){if(cookie_rinkno==$(this).attr("value"))$(this).attr("selected","true");});
			$(".sortby").change(function(){
				$.cookie("sortby",$(this).attr("value"));
				window.location=window.location;
			});
			$(".agegroup").change(function(){
				$.cookie("agegroup",$(this).attr("value"));
				window.location=window.location;
			});
			$(".rinkno").change(function(){
				$.cookie("rinkno",$(this).attr("value"));
				window.location=window.location;
			});
			$(".group").change(function(){
			$("."+$(this).attr("id")).slideToggle();
			});
		});
		</script><h3>Sort By :</h3>
		Name <input type='radio' class='sortby' name='order' value='name'>&nbsp;&nbsp;&nbsp;&nbsp;
		AgeGroup <input type='radio' class='sortby' name='order' value='group'>&nbsp;&nbsp;&nbsp;&nbsp;
		Club <input type='radio' class='sortby' name='order' value='club'>&nbsp;&nbsp;&nbsp;&nbsp;<br /><br />
		<h3>In</h3>		
		Age Group :<select class='agegroup'>
		<option value='06'>Below 6</option>
		<option value='68'>6 to 8</option>
		<option value='810'>8 to 10</option>
		<option value='1012'>10 to 12</option>
		<option value='1214'>12 to 14</option>
		<option value='1416'>14 to 16</option>
		<option value='16'>Above 16</option>
		</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Rink no: <select class='rinkno'>
		<option value='1'>Rink 1</option>
		<option value='2'>Rink 2</option>
		<option value='3'>Rink 3</option>
		<option value='4'>Rink 4</option>
		<option value='5'>Rink 5</option>
		<option value='6'>Rink 6</option>
		<option value='7'>Rink 7</option>
		<option value='8'>Rink 8</option>
		<option value='9'>Rink 9</option>
		<option value='10'>Rink 10</option>
		</select><br /><br />
HTML;
		$rink_no=mysql_real_escape_string($_COOKIE['rinkno']);
		$age_group=mysql_real_escape_string($_COOKIE['agegroup']);
		$query="SELECT * FROM `tournament_participants` WHERE `group` = '{$age_group}' AND `rink".$rink_no."` = '1' ORDER BY `$_COOKIE[sortby]`,`name`";
		//displayinfo($query);
				$res=mysql_query($query);
				if(mysql_error())displayerror(mysql_error());
				//displayinfo(mysql_num_rows($res));
		while($row=mysql_fetch_assoc($res)){
			$grp=$this->grouptostring($row['group']);
$html.=<<<HTML
<div class='participant_div $row[gender] $row[club] $row[group]'>
<input type='text' disabled class='participant' name='regno' data_id="$row[regno]" value="$row[regno]">
<input type='text' disabled class='participant' name='name' data_id="$row[regno]" value="$row[name]">
<input type='text' disabled class='participant' name='club' data_id="$row[regno]" value="$row[club]">
<input type='text' disabled class='participant' disabled name='group' data_id="$row[regno]" value="$grp">  
HTML;
$q="SELECT * FROM `tournament_timer` WHERE `regno` = '{$row['regno']}' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}'";
$q_insert="INSERT INTO `tournament_timer`(`regno`,`group`,`rinkno`) VALUES('{$row['regno']}','{$age_group}','{$rink_no}')";
$r=mysql_query($q);
if(mysql_error())displayerror(mysql_error());

if(mysql_num_rows($r)==0)mysql_query($q_insert);
if(mysql_error())displayerror(mysql_error());

if(mysql_num_rows($r)==0)// now only new data was added
for($j=1;$j<=5;$j++)
$html.=<<<HTML
<input type='text' class='participant' placeholder='timer {$j}' name='timer{$j}' data_id="$row[regno]" value="">
HTML;
else
while($rowvar=mysql_fetch_assoc($r))
{
	for($j=1;$j<=5;$j++)
	{
	$val=$rowvar['timer'.$j];
	$val=($val!="59:59:59"?$val:"");
	$html.="<input type='text' class='participant' placeholder='Timer {$j}' name='timer{$j}' data_id='$row[regno]' value='$val'>";
	}
	$val=$rowvar['timeravg']; 
	$val=($val!="59:59:59"?$val:"");
	$html.="<input type='text' class='participant' placeholder='Timer Average' name='timer{$j}' data_id='$row[regno]' value='$val'>";
}
$html.=<<<HTML
</div>
HTML;
}
		return $html;
	}

/*
NOT needed as they are implemented in action edit self
	public function actionAgeedit() {
		global $sourceFolder;		global $moduleFolder;
$html=<<<HTML
<h2> Edit By Age Group </h3>
				<style>
input{width:auto;background-color:white;color:black;}
.participant_div{display:none;}
.small{width:60px;}
input[type=checkbox]{width:30px;}
</style>
<script>
$(function(){
$(".participant").change(function(){

if($(this).is(":checkbox")!=true)
var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+$(this).attr('value')+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
else
var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+($(this).attr('checked')==true?1:0)+"' WHERE `id` = '"+$(this).attr('data_id')+"'";

$.ajax({type:"POST",url:"./+edit",data:{update:1,query:query}});
});
$(".group").change(function(){
	$("."+$(this).attr("id")).slideToggle();
});
});
</script>
Below 6 <input type='checkbox' class='group' id='06'   >&nbsp;&nbsp;&nbsp;&nbsp;
6  - 8  <input type='checkbox' class='group' id='68'   >&nbsp;&nbsp;&nbsp;&nbsp;
8  - 10 <input type='checkbox' class='group' id='810'  >&nbsp;&nbsp;&nbsp;&nbsp;
10 - 12 <input type='checkbox' class='group' id='1012' >&nbsp;&nbsp;&nbsp;&nbsp;
12 - 14 <input type='checkbox' class='group' id='1214' >&nbsp;&nbsp;&nbsp;&nbsp;
14 - 16 <input type='checkbox' class='group' id='1416' >&nbsp;&nbsp;&nbsp;&nbsp;
above 16<input type='checkbox' class='group' id='16'   >
<div id='response'></div>
HTML;
		$query="SELECT * FROM `tournament_participants` ORDER BY `group`,`name`";
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res))
{
$temp=array();
$temp[]="";
for($i=1;$i<20;$i++)$temp[]=$row['rink'.$i]?"checked":"";	
$html.=<<<HTML
<div class='$row[group] participant_div'>
<input class='participant' name='name' data_id="$row[id]" value="$row[name]"><input class='participant small' name='gender' data_id="$row[id]" value="$row[gender]"><input class='participant small' name='club' data_id="$row[id]" value="$row[club]">
<input class='participant small' name='group' data_id="$row[id]" value="$row[group]"><input class='participant small' name='dob' data_id="$row[id]" value="$row[dob]"><input class='participant small' name='blood' data_id="$row[id]" value="$row[blood]">  
<input class='participant small' name='phone1' data_id="$row[id]" value="$row[phone1]"><input class='participant small' name='phone2' data_id="$row[id]" value="$row[phone2]">
1<input class='participant' name='rink1' data_id="$row[id]" type="checkbox" $temp[1]>2<input class='participant' name='rink2' data_id="$row[id]" type="checkbox" $temp[2]>
3<input class='participant' name='rink3' data_id="$row[id]" type="checkbox" $temp[3]>4<input class='participant' name='rink4' data_id="$row[id]" type="checkbox" $temp[4]>
5<input class='participant' name='rink5' data_id="$row[id]" type="checkbox" $temp[5]>6<input class='participant' name='rink6' data_id="$row[id]" type="checkbox" $temp[6]>
7<input class='participant' name='rink7' data_id="$row[id]" type="checkbox" $temp[7]>8<input class='participant' name='rink8' data_id="$row[id]" type="checkbox" $temp[8]>
9<input class='participant' name='rink9' data_id="$row[id]" type="checkbox" $temp[9]>10<input class='participant' name='rink10' data_id="$row[id]" type="checkbox" $temp[10]>
</div>
<br/>
HTML;
}
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
*/
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


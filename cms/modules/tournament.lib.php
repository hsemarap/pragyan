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
	//public $nowyear=date('Y');
	public $nowyear=2013;
	public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
		$this->userId = $gotuid;
		$this->modulecomponentid = $gotmoduleComponentId;
		$this->action = $gotaction;
		$footer='<div style="position:relative;left:70%;bottom:-5px;">Developed by <a href="http://www.facebook.com/pbornskater">Parameswaran</a></div>';
		if($this->action=="view")
			return $this->actionView().$footer;
		if($this->action=="upload")
			return $this->actionUpload().$footer;
		if($this->action=="edit")
			return $this->actionEdit().$footer;
		if($this->action=="scoring")
			return $this->actionScoring().$footer;
		if($this->action=="config")
			return $this->actionConfig().$footer;
		if($this->action=="result")
			return $this->actionResult().$footer;
	}
	public function actionResult(){
		global $sourceFolder, $moduleFolder;
		$html="<h2><a href='./+result'>Results Page</a></h2><p align='center'><a href='./+result&club=1'>CLUB Resuts</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='./+result&individual=1'>Individual Resuts</a></p>";
		$query="SELECT DISTINCT `group` FROM `tournament_participants`";
		$groups=array();
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res))$groups[]=$row['group'];
		$query="SELECT DISTINCT `club` FROM `tournament_participants`";
		$clubs=array();
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res))$clubs[]=$row['club'];
		$query="SELECT DISTINCT `gender` FROM `tournament_participants`";
		$genders=array();
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res))$genders[]=$row['gender'];
		$query="SELECT * FROM information_schema.columns WHERE table_name =  'tournament_participants' AND column_name LIKE  'rink%'";
		$rinks=array();
		$res=mysql_query($query);
		while($row=mysql_fetch_assoc($res))$rinks[]=$row['COLUMN_NAME'];

		if(isset($_GET['club']))
		{
			foreach($clubs as $club)
			{
				$html.="<h4>For CLUB : $club</h4>
				<h4>No of <span style='color:gold'>Golds</span> : <span style='color:gold' id='golds_$club'></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				No of <span style='color:grey'>Silvers</span> : <span style='color:grey' id='silvers_$club'></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				No of <span style='color:#8C7853'>Bronzes</span> : <span style='color:#8C7853' id='bronzes_$club'></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Total Points : <span style='color:rgb(156,9,9);' id='total_$club'></span>
				</h4>
				";
				$totpoints=0;
				$positions=array("1"=>0,"2"=>0,"3"=>0); // array of no of gold,silver,bronze;
		foreach($groups as $grp)
		{
			$html.="Age Group ".$this->grouptostring($grp)."<br /><br />";
		foreach($rinks as $rink)
		{
			$qq="SELECT `value` FROM `tournament_config` WHERE `name` = '".$rink."_type'";
			$rr=mysql_query($qq);
			$rrr=(mysql_fetch_assoc($rr));
			$eventtype=$rrr['value'];

			$qq="SELECT * FROM `tournament_$eventtype` WHERE `rinkno` = '".substr($rink,4)."' AND `group` = '$grp' ";
			$rr=mysql_query($qq);
			//displayinfo($qq);
			if(mysql_num_rows($rr)>0)
		{
			$html.="EVENT : ".$rink."<br />";
		//foreach($genders as $gen)
		{
		//	$html.=$gen."<br />";
			$q="SELECT * FROM `tournament_participants` WHERE `group` = '$grp' AND `$rink` = '1'  AND `club` = '$club' ORDER BY `gender` DESC, `name` ";
			$res=mysql_query($q);
			$html.="<table><tr><th>Register NO</th><th>Name</th><th>AgeGroup</th><th>Gender</th><th>Place</th><th>Points</th></tr>";
			while($row=mysql_fetch_assoc($res))
			{
			$regno=$row['regno'];$name=$row['name'];$agegroup=$this->grouptostring($row['group']);
			$gender=$row['gender'];
			$this->updateTimerPositions(substr($rink,4),$agegroup,$gender);
			$this->updatePointsPositions(substr($rink,4),$agegroup,$gender);
			
/*COMMENT THIS AFTER DOING HEATS */
			if($eventtype!='heats')
		{
			$qq="SELECT `position` FROM `tournament_$eventtype` WHERE `rinkno` = '".substr($rink,4)."' AND `regid` = '$row[id]'";
			$rr=mysql_query($qq);
			//displayinfo($qq);
			while($rrr=mysql_fetch_assoc($rr))
			$position=$rrr['position'];			
			$position=($position>0?$position:"Not Performed");
			if($position!="Not Performed" && $position < 4 )
			$positions["$position"]++; 

			$points=$this->getpoints($group,$gender,$position,substr($rink,4));
			if($points!="")$totpoints+=$points;

			$html.="<tr><td>$regno</td><td>$name</td><td>$agegroup</td><td>$gender</td><td>$position</td><td>$points</td></tr>";
		}
			}
			$html.="</table>";
		}
		}
		}
		}		

			$html.="<script>
			$(function(){
				$('#golds_$club').html($positions[1]+' ');
				$('#silvers_$club').html($positions[2]+' ');
				$('#bronzes_$club').html($positions[3]+' ');
				$('#total_$club').html($totpoints+'');
			});
			</script>";


			}

		}
		else if(isset($_GET['individual']))
					{
	
	$query_1="SELECT * FROM `tournament_participants` ORDER BY `group`,`gender`,`name` ";
	$res_1=mysql_query($query_1);
	$html.="<table><tr><th>Name</th><th>AgeGroup</th><th>Golds</th><th>Silvers</th><th>Bronzes</th><th>Total</th><th>Medal Tally</th></tr>";
	while($r1=mysql_fetch_assoc($res_1))
	{						
		//		foreach($clubs as $club)
			{
				$name=$r1['name'];
				$regno=$r1['regno'];
				$id=$r1['id'];
				$gender=$r1['gender'];
				$agegroup=$r1['group'];
				$html.="<tr><td><b>$name</b></td><td>".$this->grouptostring($agegroup)."</td>
				<td><b><span style='color:gold'>Golds</span> : <span style='color:gold' id='golds_$id'></span></td>
				<td><span style='color:grey'>Silvers</span> : <span style='color:grey' id='silvers_$id'></span></td>
				<td><span style='color:#8C7853'>Bronzes</span> : <span style='color:#8C7853' id='bronzes_$id'></span></td>
				<td>Total Points : <span style='color:rgb(156,9,9);' id='total_$id'></span></td>
				</b>
				";
				$totpoints=0;
				$grp=$agegroup;
				$colors=array("","<span style='color:gold'>Gold</span>","<span style='color:grey'>Silver</span>","<span style='color:#8C7853'>Bronze</span>");
				$rinkpos=array();
				$positions=array("1"=>0,"2"=>0,"3"=>0); // array of no of gold,silver,bronze;
			
		foreach($rinks as $rink)
		{
			$qq="SELECT `value` FROM `tournament_config` WHERE `name` = '".$rink."_type'";
			$rr=mysql_query($qq);
			$rrr=(mysql_fetch_assoc($rr));
			$eventtype=$rrr['value'];

			$qq="SELECT * FROM `tournament_$eventtype` WHERE `rinkno` = '".substr($rink,4)."' AND `group` = '$grp' AND `regid` = '$id' ";
			$rr=mysql_query($qq);
			//displayinfo($qq);
			if(mysql_num_rows($rr)>0)
		{
			$this->updateTimerPositions(substr($rink,4),$agegroup,$gender);
			$this->updatePointsPositions(substr($rink,4),$agegroup,$gender);			
/*COMMENT THIS AFTER DOING HEATS */
			if($eventtype!='heats')
		{
			$qq="SELECT `position` FROM `tournament_$eventtype` WHERE `rinkno` = '".substr($rink,4)."' AND `regid` = '$id'";
			$rr=mysql_query($qq);

			while($rrr=mysql_fetch_assoc($rr))
			$position=$rrr['position'];	
			
			$position=($position>0?$position:"Not Performed");
			if($position!="Not Performed" && $position < 4 )
			{$positions["$position"]++; 
			 $rinkpos["$rink"]=$colors[$position];
			}
			$points=$this->getpoints($group,$gender,$position,substr($rink,4));
			if($points!="")$totpoints+=$points;
//			$html.="<tr><td>$regno</td><td>$name</td><td>$agegroup</td><td>$gender</td><td>$position</td><td>$points</td></tr>";
		}
		}
		}
			$html.="<td>";
			foreach($rinkpos as $key=>$val)$html.="$key $val&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$html.="</td></tr>";
			//$html.="<br /><br />";
			$html.="<script>
			$(function(){
				$('#golds_$id').html($positions[1]+' ');
				$('#silvers_$id').html($positions[2]+' ');
				$('#bronzes_$id').html($positions[3]+' ');
				$('#total_$id').html($totpoints+'');
			});
			</script>";


			}

		}
		$html.="</table>";
	}







		if(!isset($_GET["club"]) && !isset($_GET["individual"]))
		foreach($groups as $grp)
		{
			$html.="Age Group ".$this->grouptostring($grp)."<br /><br />";
		foreach($rinks as $rink)
		{
			$qq="SELECT `value` FROM `tournament_config` WHERE `name` = '".$rink."_type'";
			$rr=mysql_query($qq);
			$rrr=(mysql_fetch_assoc($rr));
			$eventtype=$rrr['value'];

			$qq="SELECT * FROM `tournament_$eventtype` WHERE `rinkno` = '".substr($rink,4)."' AND `group` = '$grp' ";
			$rr=mysql_query($qq);
			//displayinfo($qq);
			if(mysql_num_rows($rr)>0)
		{
			$html.="EVENT : ".$rink."<br />";
		//foreach($genders as $gen)
		{
		//	$html.=$gen."<br />";
			$q="SELECT * FROM `tournament_participants` WHERE `group` = '$grp' AND `$rink` = '1' ORDER BY `gender` DESC, `name` ";
			$res=mysql_query($q);
			$html.="<table><tr><th>Register NO</th><th>Name</th><th>AgeGroup</th><th>Gender</th><th>Place</th><th>Points</th></tr>";
			while($row=mysql_fetch_assoc($res))
			{
			$regno=$row['regno'];$name=$row['name'];$agegroup=$this->grouptostring($row['group']);
			$gender=$row['gender'];
			$this->updateTimerPositions(substr($rink,4),$agegroup,$gender);
			$this->updatePointsPositions(substr($rink,4),$agegroup,$gender);
			
/*COMMENT THIS AFTER DOING HEATS */
			if($eventtype!='heats')
		{
			$qq="SELECT `position` FROM `tournament_$eventtype` WHERE `rinkno` = '".substr($rink,4)."' AND `regid` = '$row[id]'";
			$rr=mysql_query($qq);
			//displayinfo($qq);
			while($rrr=mysql_fetch_assoc($rr))
			$position=$rrr['position'];			
			$position=($position>0?$position:"Not Performed");
			$points=$this->getpoints($group,$gender,$position,substr($rink,4));
			
			$html.="<tr><td>$regno</td><td>$name</td><td>$agegroup</td><td>$gender</td><td>$position</td><td>$points</td></tr>";
		}
			}
			$html.="</table>";
		}
		}
		}
		}
		return $html;
	}
	public function getpoints($grp,$gender,$position,$rinkno)
	{
		// Modify if points change for group and gender wise.
		if($position<1 || $position>3)return "";
		return $this->getconfig("rink".$rinkno."_points_".$position);
	}
	public function getconfig($name)
	{
		$qq="SELECT `value` FROM `tournament_config` WHERE `name` = '$name' ";
		$rr=mysql_query($qq);
		while($row=mysql_fetch_assoc($rr))$ans=$row['value'];
		return $ans;
	}
	public function actionConfig()
	{
		$html="<h2>Configuration Page</h2>";
		if(sizeof($_POST)>0)
		{
			if(isset($_POST['update']))
			{
				$name=mysql_real_escape_string($_POST['name']);
				$value=mysql_real_escape_string($_POST['value']);
				$q="UPDATE `tournament_config` SET `value` = '$value' WHERE `name` = '$name'";
				mysql_query($q);
				if(mysql_error())
				$html.="<div id='error'>".mysql_error()."</div>";
			}
			if(isset($_POST['new']))
			{
				$name=mysql_real_escape_string($_POST['name']);
				$q='INSERT INTO `tournament_config`(`name`,`value`) VALUES("'.$name.'","Enter Value")';
				mysql_query($q);
				if(mysql_error())
				$html.="<div id='error'>".mysql_error()."asd</div>";	
			}
			return $html;
		}
		$query="SELECT * FROM `tournament_config`";
		$result=mysql_query($query);
		$html.=<<<STYLE
		<style>
		input{background:white!important;}
		input:disabled{background:transparent!important;border:none;}
		.currentdiv{float:left;width:40%;padding:2px;}
		</style>
STYLE;
		$html.=<<<SCRIPT
		<script>
		$(function(){

		$("#newconfig").click(function(){
			var newname=$(".new.name").attr("value");
			var msg=$(".message").html();
			
			if(newname=="" || !newname)
				{	
					$(".message").html(msg+"<br />Please enter Something in the box to add configuration.");
					setTimeout(function(){
						$(".message").html(msg);},2000);
				}
			else
				$.ajax({
				type:"POST",
				url:"./+config",
				data:{
					new:1,
					name:newname
				},
				success:function(msg){
					
					if($("#error",msg).html()+""!="null")
						{
							alert($("#error",msg).html());
							curr.css("border","2px solid rgb(156,9,9)!important");
						}
					else 
						curr.css("border","2px solid rgb(9,156,9)");
					setTimeout(function(){
						curr.css("border","1px solid #ccc");
					},800);
				}
			});
		});
		$(".current.value").change(function(){
			//alert('a');
			//alert($(this).parent().find(".name"));
			var curr=$(this);
			$.ajax({
				type:"POST",
				url:"./+config",
				data:{
					update:1,
					name:$(this).parent().find(".name").attr("value"),
					value:$(this).parent().find(".value").attr("value")
				},
				success:function(msg){
					//alert($(this).attr("value"));
					if($("#error",msg).html()+""!="null")
						{
							alert($("#error",msg).html());
							curr.css("border","2px solid rgb(156,9,9)!important");
						}
					else 
						curr.css("border","2px solid rgb(9,156,9)");
					setTimeout(function(){
						curr.css("border","1px solid #ccc");
					},800);
				}
			});

		});
		});
		</script>
SCRIPT;
		$i=1;
		while($row=mysql_fetch_assoc($result))
		{
			$html.="<div id='config$i' class='currentdiv'>";
			$html.="<input type='text' name='name' disabled class='name current' value='$row[name]'>";
			$html.="<input type='text' name='name' class='value current' value='$row[value]'>";
			$html.='</div>';
			if($i%2==0)$html.="<br /><br /><br />";
			$i++;
		}
		$html.="<br /><span class='message'></span><br /><input type='text' name='name' placeholder='Add new configuration' class='name new'><button id='newconfig'>Add config</button>";
		return $html;
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

	public function getRegisterNo($grp)
	{
		$startingregno=array(
			"06"=>0,
			"68"=>300,
			"810"=>600,
			"1012"=>900,
			"1214"=>1200,
			"1416"=>1500,
			"16"=>1800
			);
		$query="SELECT * FROM `tournament_participants` WHERE `group` = '$grp' ORDER BY `regno`";
		$res=mysql_query($query);

		if(mysql_num_rows($res)==0)return $startingregno["$grp"];
		$i=$startingregno["$grp"]-1;$prev=$startingregno["$grp"];
		while($row=mysql_fetch_assoc($res))
		{
			if($row['regno']-$i>1)return $i+1;
			$prev=$row['regno'];
			$i++;
		}
		return $prev+1;
	}


	public function findaverage($a,$b,$c)
	{
		//return strtotime('17:14:10','00:00:00');
		if($b==0 && $c==0)return $a;
		if($a==0 && $c==0)return $b;
		if($a==0 && $b==0)return $c;
		if($a==0)return (($b)+($c))/2;
		if($b==0)return (($a)+($c))/2;
		if($c==0)return (($a)+($b))/2;
		$diff=30;
		$d1=floor(abs(($a)-($b)));
		$d2=floor(abs(($b)-($c)));
		$d3=floor(abs(($c)-($a)));
	//echo $d1."<br />".$d2."<br />".$d3;
		if($d1 <= $diff && $d2 <= $diff ) return (($a)+($b)+($c))/3;
		else if($d1 <= $diff && $d3 <= $diff ) return (($a)+($b)+($c))/3;
		else if($d2 <= $diff && $d3 <= $diff ) return (($a)+($b)+($c))/3;
		else if($d1 <= $diff) return (($a)+($b))/2;
		else if($d2<=$diff)  return (($b)+($c))/2;
		else if($d3<=$diff)  return (($c)+($a))/2;
		else return (($a)+($b)+($c))/3;
	}
	public function config_value($config_name,$config_val)
	{
		$q="SELECT * FROM `tournament_config` WHERE `name` = '$config_name' AND `value` = '$config_val' ";
		$res=mysql_query($q);
		return (mysql_num_rows($res)!=0);
	}
	public function findPointAverage($t,$cut){
		$max=-1.0;$min=1000.0;
		foreach($t as $m)if($m!=9999){if($m > $max)$max=$m;if($m < $min)$min=$m;}
		$cnt=0;$tot=0.0;
		if($max==-1 && $min==1000)return 9999;
		foreach($t as $m)
		if($m!=9999){
			$cnt++;
			$tot+=$m;
		}
		if($cut){
			$cnt-=2;
			$tot-=$max+$min;
		}
		if($cnt>0)
		return $tot/$cnt;
		else return 9999;
	}
	public function actionEdit() {
		global $sourceFolder;		global $moduleFolder;
		
		if(isset($_POST['update']) && $_POST['update']==1){
			$query=$_POST['query'];//mysql_real_escape_string($_GET['query']);
			$group=$_POST['agegroup'];
			$id=$_POST['regid'];
			$name=$_POST['name'];
			$value=$_POST['value'];
			if(isset($_POST['dobchange']))
			{
				$q="SELECT * FROM `tournament_participants` WHERE `id` = '$id'";
				$res=mysql_query($q);
				while($row=mysql_fetch_assoc($res))
					$prevgrp="".$row['group'];
	$d=explode(".",str_replace(":",".",str_replace("-",".",str_replace("/",".", "$value"))))[2];
	$m=explode(".",str_replace(":",".",str_replace("-",".",str_replace("/",".", "$value"))))[1];
	$y=explode(".",str_replace(":",".",str_replace("-",".",str_replace("/",".", "$value"))))[0];
	
				$group=$this->findgroup($this->nowyear - $y);
				$regno=$this->getRegisterNo($group);
				if($prevgrp!=$group)
				$q="UPDATE `tournament_participants` SET `regno` = '$regno' , `group` = '$group' WHERE `id` = '$id'";
				mysql_query($q);
				$html="<div id='update' >($q)</div>";
				$html="<div id='agegroup' >".$this->grouptostring($group)."</div>";
			}
			
			if($name!='dob' && $name!='blood'){$name=mysql_real_escape_string($name);$value=mysql_real_escape_string($value);}
			$q="UPDATE `tournament_participants` SET `$name` = '$value' WHERE `id` = '$id'";
			$html.="Your update query was ".$q."<br />";
			mysql_query($q);
			if(mysql_error())$html.='Has errors '.mysql_error()."<br />";
			else $html.="Executed successfully"."<br />";
			return $html;
		}
		if(isset($_POST['timer_update'])){
			$rink_no=$_POST['rinkno'];
			$gender=$_POST['gender'];
			$age_group=$_POST['agegroup'];
			$reg_no=$_POST['regno'];
			$regid=$_POST['regid'];
			$t1=$_POST['timer1'];
			$t2=$_POST['timer2'];
			$t3=$_POST['timer3'];/*	$t4=$_POST['timer4']; $t5=$_POST['timer5']; */
			$tt1=explode(':',$t1)[0]*3600+explode(':',$t1)[1]*60+explode(':',$t1)[2];
			$tt2=explode(':',$t2)[0]*3600+explode(':',$t2)[1]*60+explode(':',$t2)[2];
			$tt3=explode(':',$t3)[0]*3600+explode(':',$t3)[1]*60+explode(':',$t3)[2];
			$avg=$this->findaverage($tt1,$tt2,$tt3);
			$avg=floor($avg/3600).":".floor(($avg%3600)/60).":".floor($avg%60);

			$t1=($t1!=""?$t1:"59:59:59");
			$t2=($t2!=""?$t2:"59:59:59");
			$t3=($t3!=""?$t3:"59:59:59");
			$t4=($t4!=""?$t4:"59:59:59");
			$t5=($t5!=""?$t5:"59:59:59");

			$q="UPDATE `tournament_timer` SET `timeravg` = '$avg' , `timer1` = '$t1' , `timer2` = '$t2' , `timer3` = '$t3' , `timer4` = '$t4' , `timer5` = '$t5' WHERE `regid` = '{$regid}' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}'";
			$html="<div id='timer_update'>".$q."</div>";
			mysql_query($q);
			$q="SELECT * FROM `tournament_timer` WHERE `timeravg` != '59:59:59' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}' ORDER BY `timeravg` ";
			$i=0;$prev=99991;
			$html.="<div id='timer_position'>$q</div>";//.$t1." YY ".$t2." YY ".$t3." YY ".strtotime($t1)." XX ".strtotime($t2)." XX ".strtotime($t3)."</div>";
			$res=mysql_query($q);
			while($row=mysql_fetch_assoc($res))
			{
			$query_gender="SELECT * FROM `tournament_participants` WHERE `id` = '$row[regid]'";
			$res_gender=mysql_query($query_gender);
			while($row_gender=mysql_fetch_assoc($res_gender))$thisgender=$row_gender['gender'];
			if($thisgender==$gender)
			{	if($prev!=$row['timeravg'])
				$i++;
				$prev=$row['timeravg'];
				$query="UPDATE `tournament_timer` SET `position` = '$i' WHERE `regid` = '$row[regid]'";
				mysql_query($query);
				//if($reg_no==$row['regno'])
				$html.="<div id='timer_position_$row[regid]'>$i</div>";
			}
			}
			return $html;			
		}
		if(isset($_POST['quick']))
		{
			$query=$_POST['query'];
			$query=str_replace("tablename","tournament_participants",$query);
			$regno=$this->getRegisterNo($_POST['group']);
			$query=str_replace("regnofromdb","$regno",$query);
			$res=mysql_query($query);
			if(mysql_error())
				return "<div id='error'>".mysql_error()."</div>";
		}
		if(isset($_POST['points_update'])){
			$rink_no=mysql_real_escape_string($_POST['rinkno']);
			$age_group=mysql_real_escape_string($_POST['agegroup']);
			$reg_no=mysql_real_escape_string($_POST['regno']);
			$gender=mysql_real_escape_string($_POST['gender']);
			$t[]=mysql_real_escape_string($_POST['point1']);
			$t[]=mysql_real_escape_string($_POST['point2']);
			$t[]=mysql_real_escape_string($_POST['point3']);
			$t[]=mysql_real_escape_string($_POST['point4']);
			$t[]=mysql_real_escape_string($_POST['point5']);	
			$regid=mysql_real_escape_string($_POST['regid']);
			$t1=($t1!=""?$t1:"9999");
			$t2=($t2!=""?$t2:"9999");
			$t3=($t3!=""?$t3:"9999");
			$t4=($t4!=""?$t4:"9999");
			$t5=($t5!=""?$t5:"9999");
			
			if($this->config_value("rink{$rink_no}_cut_high_low","true"))
			$avg=$this->findPointAverage($t,1);
			else
			$avg=$this->findPointAverage($t,0);

			$t[0]=($t[0]!=""?$t[0]:"9999");
			$t[1]=($t[1]!=""?$t[1]:"9999");
			$t[2]=($t[2]!=""?$t[2]:"9999");
			$t[3]=($t[3]!=""?$t[3]:"9999");
			$t[4]=($t[4]!=""?$t[4]:"9999");

			$alternative=$this->config_value("rink{$rink_no}_alternative_points","true");
			if($alternative)	
			{
			$tt[]=mysql_real_escape_string($_POST['altpoint1']);
			$tt[]=mysql_real_escape_string($_POST['altpoint2']);
			$tt[]=mysql_real_escape_string($_POST['altpoint3']);
			$tt[]=mysql_real_escape_string($_POST['altpoint4']);
			$tt[]=mysql_real_escape_string($_POST['altpoint5']);					
			$tt1=($tt[0]!=""?$tt[0]:"9999");
			$tt2=($tt[1]!=""?$tt[1]:"9999");
			$tt3=($tt[2]!=""?$tt[2]:"9999");
			$tt4=($tt[3]!=""?$tt[3]:"9999");
			$tt5=($tt[4]!=""?$tt[4]:"9999");
			if($this->config_value("rink{$rink_no}_cut_high_low","true"))
			$avg2=$this->findPointAverage($tt,1);
			else
			$avg2=$this->findPointAverage($tt,0);
			$altquery=", `alt_pointavg` = '$avg2' , `alt_point1` = '$tt[0]' , `alt_point2` = '$tt[1]' , `alt_point3` = '$tt[2]' , `alt_point4` = '$tt[3]' , `alt_point5` = '$tt[4]' ";	
			}
			$total=($avg+$avg2);
			$q="UPDATE `tournament_points` SET `total` = '".$total."' ,`pointavg` = '$avg' , `point1` = '$t[0]' , `point2` = '$t[1]' , `point3` = '$t[2]' , `point4` = '$t[3]' , `point5` = '$t[4]' $altquery WHERE `regid` = '{$regid}' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}'";
			$html="<div id='points_update'>".$q."</div>";
			$html="<div id='points_total'>$total</div>";
			mysql_query($q);
			$q="SELECT * FROM `tournament_points` WHERE `total` != '9999' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}' ORDER BY `total` DESC ";-
			$i=0;$prev="random_string";
			$html.="<div id='points_position'>$q</div>";
			$html.="<div id='points_average'>$avg</div>";			
			$html.="<div id='points_altaverage'>$avg2</div>";			
			$res=mysql_query($q);
			while($row=mysql_fetch_assoc($res))
			{
			$query_gender="SELECT * FROM `tournament_participants` WHERE `id` = '$row[regid]'";
			$res_gender=mysql_query($query_gender);
			while($row_gender=mysql_fetch_assoc($res_gender))$thisgender=$row_gender['gender'];
			if($thisgender==$gender)
			{	if($prev!=$row['total'])
				$i++;
				$prev=$row['total'];
				$query="UPDATE `tournament_points` SET `position` = '$i' WHERE `regid` = '$row[regid]'";
				mysql_query($query);
				$html.="<div id='points_position_$row[regid]'>$i</div>";
			}
			}
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
		<div id='quickentry'>
<input type='text' class='quick textbox name' name='name' placeholder='Name' value=""><input type='text' class='quick textbox' name='gender' value="male"><input type='text' class='quick textbox' name='club' value="individual">
<select class='quick textbox agegroup' name='group'>
		<option value='06'>Below 6</option>
		<option value='68'>6 to 8</option>
		<option value='810'>8 to 10</option>
		<option value='1012'>10 to 12</option>
		<option value='1214'>12 to 14</option>
		<option value='1416'>14 to 16</option>
		<option value='16'>Above 16</option>
</select>
<input type='text' class='quick textbox blood' name='blood' placeholder='Blood Group' value="b+">  
<input type='text' class='quick textbox ph1' name='phone1' placeholder='Phone No 1' value="9999999999">
<input type='text' class='quick textbox ph2' name='phone2' placeholder='Phone No 2' value="9999999999">
1<input class='quick checkbox' name='rink1' type="checkbox" >2<input class='quick checkbox' name='rink2' type="checkbox" >
3<input class='quick checkbox' name='rink3' type="checkbox" >4<input class='quick checkbox' name='rink4' type="checkbox" >
5<input class='quick checkbox' name='rink5' type="checkbox" >6<input class='quick checkbox' name='rink6' type="checkbox" >
7<input class='quick checkbox' name='rink7' type="checkbox" >8<input class='quick checkbox' name='rink8' type="checkbox" >
9<input class='quick checkbox' name='rink9' type="checkbox" >10<input class='quick checkbox' name='rink10' type="checkbox"  $temp[10]>			
<button class='quick'>Add</button>
</div>
		<script>
		$(function(){
		$("button.quick").click(
			function(){
				var curr=$("#quickentry");
				var agegroup=$(".quick.agegroup").attr("value");
var query="INSERT INTO `tablename`(`regno`,";
	var len=$(".quick.textbox").length,len2=$(".quick.checkbox").length,i=1,flag=1;
	$(".quick.textbox,.quick.checkbox").each(function(){
		query+="`"+($(this).attr("name"))+"`";
		if(i!=len+len2)query+=",";
		i++;
	});
	i=1;
query+=") VALUES('regnofromdb',";
	$(".quick.textbox").each(function(){
		if(!$(this).attr("value"))
		{
			flag=0;
			alert("Enter value for "+$(this).attr("name"));
			return false;
		}
		query+="'"+($(this).attr("value"))+"',";
	});
	$(".quick.checkbox").each(function(){
		query+="'"+($(this).is(":checked")?1:0)+"'";
		if(i!=len2)query+=",";
		i++;
	});
		query+=")";
alert(query);
				if(flag && confirm('You want to add?'))
				$.ajax({type:"POST",
					url:"./+edit",
					data:{quick:"1",query:query,group:agegroup},
					success:function(msg){
					curr.animate({background:"rgb(50,150,50)"},100);
					if($("#error",msg).html())alert($("#error",msg).html());
					curr.css("background","rgb(50,150,50)");
					setTimeout(function(){curr.animate({background:"transparent"},100);},1000);
					setTimeout(function(){curr.css("background","transparent");},1000);
					}
					});					
	
			});
		$(".participant").change(function(){
			var curr=$(this).parent(),dob_is_changed,agegrp=curr.find(".dobinput").attr("value");
			var regid=$(this).attr('data_id');
		if($(this).hasClass("dobinput"))
		{	
			var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+$(this).attr('value')+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
			dob_is_changed=true;			
		}
		else 
		if($(this).is(":checkbox")!=true)
		var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+$(this).attr('value')+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
		else
		var query="UPDATE `tournament_participants` SET `"+$(this).attr('name')+"` = '"+($(this).attr('checked')==true?1:0)+"' WHERE `id` = '"+$(this).attr('data_id')+"'";
		
		$.ajax({type:"POST",url:"./+edit",data:{
			update:1,
			query:query,
			dobchange:dob_is_changed,
			agegroup:agegrp,
			regid:regid,
			name:$(this).attr('name'),
			value:$(this).attr('value')
			},success:function(msg){
			//alert($("#update",msg).html());
			if(dob_is_changed)
			{
				curr.find(".agegroup").attr("value",$("#agegroup",msg).html());	
			}
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
<input type='text' class='participant agegroup' disabled name='group' data_id="$row[id]" value="$grp"><input type='text' class='participant dobinput' name='dob' data_id="$row[id]" value="$row[dob]"><input type='text' class='participant' name='blood' data_id="$row[id]" value="$row[blood]">  
<input type='text' class='participant' name='phone1' data_id="$row[id]" value="$row[phone1]"><input type='text' class='participant' name='phone2' data_id="$row[id]" value="$row[phone2]">
1<input class='participant' name='rink1' data_id="$row[id]" type="checkbox"  $temp[1]>2<input class='participant' name='rink2' data_id="$row[id]" type="checkbox"  $temp[2]>
3<input class='participant' name='rink3' data_id="$row[id]" type="checkbox"  $temp[3]>4<input class='participant' name='rink4' data_id="$row[id]" type="checkbox"  $temp[4]>
5<input class='participant' name='rink5' data_id="$row[id]" type="checkbox"  $temp[5]>6<input class='participant' name='rink6' data_id="$row[id]" type="checkbox"  $temp[6]>
7<input class='participant' name='rink7' data_id="$row[id]" type="checkbox"  $temp[7]>8<input class='participant' name='rink8' data_id="$row[id]" type="checkbox"  $temp[8]>
9<input class='participant' name='	rink9' data_id="$row[id]" type="checkbox"  $temp[9]>10<input class='participant' name='rink10' data_id="$row[id]" type="checkbox"  $temp[10]>
<br/>
</div>
HTML;
}
		return $html;
	}
	public function updateTimerPositions($rink_no,$age_group,$gender){
		$q1="SELECT * FROM `tournament_timer` WHERE `timeravg` != '59:59:59' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}' ORDER BY `timeravg` ";
		$i1=0;$prev="random_string";
		$res1=mysql_query($q1);
		while($row1=mysql_fetch_assoc($res1))
		{
		$query_gender="SELECT * FROM `tournament_participants` WHERE `id` = '$row[regid]'";
			$res_gender=mysql_query($query_gender);
			while($row_gender=mysql_fetch_assoc($res_gender))$thisgender=$row_gender['gender'];
			if($thisgender==$gender)			
		{	if($prev!=$row1['timeravg'])
			$i1++;
			$prev=$row1['timeravg'];
			$query1="UPDATE `tournament_timer` SET `position` = '$i1' WHERE `regid` = '$row1[regid]'";
			mysql_query($query1);
			if(mysql_error())displayerror(mysql_error());
		}
		}
	}
	public function updatePointsPositions($rink_no,$age_group,$gender){
		$q1="SELECT * FROM `tournament_points` WHERE `total` != '9999' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}' ORDER BY `total` DESC";
		$i1=0;$prev="random_string";
		$res1=mysql_query($q1);
		while($row1=mysql_fetch_assoc($res1))
		{
		$query_gender="SELECT * FROM `tournament_participants` WHERE `id` = '$row[regid]'";
			$res_gender=mysql_query($query_gender);
			while($row_gender=mysql_fetch_assoc($res_gender))$thisgender=$row_gender['gender'];
			if($thisgender==$gender)			
		{	if($prev!=$row1['total'])
			$i1++;
			$prev=$row1['total'];
			$query1="UPDATE `tournament_points` SET `position` = '$i1' WHERE `regid` = '$row1[regid]'";
			mysql_query($query1);
			if(mysql_error())displayerror(mysql_error());
		}
		}
	}

	public function actionScoring() {
		global $sourceFolder;		global $moduleFolder;
		$html='';

	//`timer1` = '40:40:25' , `timer2` = '40:40:40' , `timer3` = '00:00:00' 		
		//echo strtotime("22:40:55.555").' X '.strtotime(date("i:s:u",'40:41:40')).' X '.strtotime(date("i:s:u",'0:0:0'));
		$basefolder=dirname($_SERVER[SCRIPT_NAME]);
		
		if(!isset($_COOKIE['rinkno']))$_COOKIE['rinkno']=1;
		$rink_no=mysql_real_escape_string($_COOKIE['rinkno']);
		$query="SELECT * FROM `tournament_config` WHERE `name` = 'rink".$rink_no."_type'";
		$res=mysql_query($query);
		
		while($row=mysql_fetch_assoc($res))
			$type=$row['value'];


			
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
			var cookie_sort,cookie_age,cookie_rinkno,cookie_gender;
			cookie_sort=$.cookie("sortby"),cookie_age=$.cookie("agegroup"),cookie_gender=$.cookie("gender"),cookie_rinkno=$.cookie("rinkno");			
			if(cookie_sort==undefined)$.cookie("sortby","name");
			if(cookie_age==undefined)$.cookie("agegroup","06");
			if(cookie_gender==undefined)$.cookie("gender","male");
			if(cookie_rinkno==undefined)$.cookie("rinkno","1");
			cookie_sort=$.cookie("sortby"),cookie_age=$.cookie("agegroup"),cookie_rinkno=$.cookie("rinkno");cookie_gender=$.cookie("gender");			
			$(".sortby").each(function(){if(cookie_sort==$(this).attr("value"))$(this).attr("checked","1");});
			$(".agegroup > option").each(function(){if(cookie_age==$(this).attr("value"))$(this).attr("selected","true");});
			$(".gender_selection > option").each(function(){if(cookie_gender==$(this).attr("value"))$(this).attr("selected","true");});
			$(".rinkno > option").each(function(){if(cookie_rinkno==$(this).attr("value"))$(this).attr("selected","true");});
			$(".sortby").change(function(){
				$.cookie("sortby",$(this).attr("value"));
				window.location=window.location;
			});
			$(".agegroup").change(function(){
				$.cookie("agegroup",$(this).attr("value"));
				window.location=window.location;
			});
			$(".gender_selection").change(function(){
				$.cookie("gender",$(this).attr("value"));
				window.location=window.location;
			});
			$(".rinkno").change(function(){
				$.cookie("rinkno",$(this).attr("value"));
				window.location=window.location;
			});
			$(".group").change(function(){
			$("."+$(this).attr("id")).slideToggle();
			});
			$(".timerinput").change(function(){
				var regno=$(this).attr("data_id");
				var regid=$(this).attr("data_regid");
				var tot=0,cnt=0,curr=$(this).parent();
				$(".timer"+regid+".timerinput").each(function(){
				var timeValue = $(this).attr("value")+"",sHours,sMinutes,sSec;
				   //alert(timeValue + ' ' + timeValue == "");
				    timeValue=timeValue.replace('.', ':');
				    timeValue=timeValue.replace(',', ':');
				    timeValue=timeValue.replace('-', ':');
				    timeValue=timeValue.replace('.', ':');
				    timeValue=timeValue.replace(',', ':');
				    timeValue=timeValue.replace('-', ':');
				    if(timeValue == "" || (timeValue.indexOf(":")<0 && timeValue.indexOf(".")<0 && timeValue.indexOf(" ")<0 ))
				    {
				    	//alert(timeValue.indexOf(":")<0 + ' ' + timeValue.indexOf(":"));
				    }
				    else
				    {
				    	var flag=1;
				        sHours = timeValue.split(':')[0];
				        sMinutes = timeValue.split(':')[1];
				        sSec= timeValue.split(':')[2];
				        if(sHours == "" || isNaN(sHours) || parseInt(sHours)>59)
				        {
				        	flag=0;
				            alert("Invalid minute format"+parseInt(sHours));
				        }
				        else if(parseInt(sHours) == 0)
				            sHours = "00";
				        else if (sHours <10)
				            sHours = "0"+parseInt(sHours);

				        if(sMinutes == "" || isNaN(sMinutes) || parseInt(sMinutes)>59)
				        {
				            alert("Invalid Sec format");				            
				            flag=0;
				        }
				        else if(parseInt(sMinutes) == 0)
				            sMinutes = "00";
				        else if (sMinutes <10)
				            sMinutes = "0"+parseInt(sMinutes);    
				        if(sSec == "" || isNaN(sSec) || parseInt(sSec)>59)
				        {
				            alert("Invalid MilliSec format"+sSec);
				            flag=0;
				        }
				        else if(parseInt(sSec) == 0)
				            sSec = "00";
				        else if (sSec <10)
				            sSec = "0"+parseInt(sSec);        
				        $(this).attr("value",sHours + ":" + sMinutes + ":" + sSec);        
				        if(flag){cnt++;
				        	$(this).css("border","2px solid rgb(9,156,9)");
				        	var this_inp=$(this),temptotal;
				        	setTimeout(function(){this_inp.css("border","1px solid grey");},1000);
				        //	t1+=parseInt(sHours);t2+=parseInt(sMinutes);t3+=parseInt(sSec);
				        	temptotal=parseInt(sSec)+parseInt(sMinutes)*60+parseInt(sHours)*3600;
							tot+=temptotal;
							//alert(temptotal);
							if(temptotal==0)cnt--;
	
				        	$.ajax({
				        		type:'POST',
				        		url:"./+edit",
				        		data:
				        	{
				        		timer_update:"1",
				        		regno:regno,
				        		regid:regid,
				        		gender:curr.hasClass("male")?"male":"female",
				        		agegroup:curr.find(".agegroup_input").attr("data_group"),
				        		rinkno:cookie_rinkno,
				        		timer1:($(".timer"+regid+".timerinput")[0].value==""?"59:59:59":$(".timer"+regid+".timerinput")[0].value),
				        		timer2:($(".timer"+regid+".timerinput")[1].value==""?"59:59:59":$(".timer"+regid+".timerinput")[1].value),
				        		timer3:($(".timer"+regid+".timerinput")[2].value==""?"59:59:59":$(".timer"+regid+".timerinput")[2].value),
				        		timer4:($(".timer"+regid+".timerinput")[3].value==""?"59:59:59":$(".timer"+regid+".timerinput")[3].value),
				        		timer5:($(".timer"+regid+".timerinput")[4].value==""?"59:59:59":$(".timer"+regid+".timerinput")[4].value)
				        	},success:function(msg){
				        		
				        		//alert($("#timer_update",msg).html());
				        		/*
				        		var position=$("#timer_position",msg).html();
				        		curr.find(".position").attr("value",position);
				        		*/
				        		
				        		$(".participant.position").each(function(){
				        			var this_regid=$(this).attr("data_regid");
				        			var this_posn=$("#timer_position_"+this_regid,msg).html();
				        			$(this).attr("value",this_posn);
				        		});

								curr.animate({background:"rgb(50,150,50)"},100);
								curr.css("background","rgb(50,150,50)");
								setTimeout(function(){curr.animate({background:"transparent"},100);},1000);
								setTimeout(function(){curr.css("background","transparent");},1000);
							},error: function (xhr, ajaxOptions, thrownError) {
						           //alert(xhr.status);
						           //alert(xhr.responseText);
						           //alert(thrownError);
								curr.animate({background:"rgb(150,50,50)"},100);
								curr.css("background","rgb(150,50,50)");
								setTimeout(function(){curr.animate({background:"transparent"},100);},1000);
								setTimeout(function(){curr.css("background","transparent");},1000);
						       }
							});
							

				        }
				        else $(this).css("border","2px solid rgb(156,9,9)");
				    }	
				});
				var avg=parseInt(parseInt(tot/cnt)/3600)+":"+parseInt((parseInt(tot/cnt)%3600)/60)+":"+parseInt(parseInt(tot/cnt)%60);
				$(".timer"+regid+".timeraverage").attr("value",avg);
				//alert(avg);
			});
/*  POINTS CODE */
			$(".pointinput,.altpointinput").change(function(){
				var regno=$(this).attr("data_id");
				var regid=$(this).attr("data_regid");
				var tot=0.00,cnt=0,curr=$(this).parent();
				$(".point"+regid+".pointinput").each(function(){
				var Value = parseFloat($(this).attr("value"));
				   
				    if(Value == "")
				    {
				    	//alert(timeValue.indexOf(":")<0 + ' ' + timeValue.indexOf(":"));
				    }
				    else
				    {
				    	var flag=1;
				    	if(Value == 9999.00)flag=0;
				        if(flag){cnt++;
				        	$(this).css("border","2px solid rgb(9,156,9)");
				        	var this_inp=$(this),temptotal;
				        	setTimeout(function(){this_inp.css("border","1px solid grey");},1000);				        
							tot+=Value;
							//if(temptotal==0)cnt--;					        		
				        }
				        else $(this).css("border","2px solid rgb(156,9,9)");
				    }	
				});
				$.ajax({
				        		type:'POST',
				        		url:"./+edit",
				        		data:
				        	{
				        		points_update:"1",
				        		regno:regno,
				        		regid:regid,
				        		gender:curr.hasClass("male")?"male":"female",
				        		agegroup:curr.find(".agegroup_input").attr("data_group"),
				        		rinkno:cookie_rinkno,
				        		point1:($(".point"+regid+".pointinput")[0].value==""?9999:$(".point"+regid+".pointinput")[0].value),
				        		point2:($(".point"+regid+".pointinput")[1].value==""?9999:$(".point"+regid+".pointinput")[1].value),
				        		point3:($(".point"+regid+".pointinput")[2].value==""?9999:$(".point"+regid+".pointinput")[2].value),
				        		point4:($(".point"+regid+".pointinput")[3].value==""?9999:$(".point"+regid+".pointinput")[3].value),
				        		point5:($(".point"+regid+".pointinput")[4].value==""?9999:$(".point"+regid+".pointinput")[4].value)
HTML;
if($this->config_value("rink{$rink_no}_alternative_points","true"))	
for($j=1;$j<=5;$j++)$html.=",\naltpoint{$j}:($('.altpoint'+regid+'.altpointinput')[".($j-1)."].value==''?9999:$('.altpoint'+regid+'.altpointinput')[".($j-1)."].value)";		        		
$html.=<<<HTML
				        	},success:function(msg){
				        		
				        		//alert($("#points_average",msg).html());
				        		/*
				        		var position=$("#timer_position",msg).html();
				        		curr.find(".position").attr("value",position);
				        		*/
				        		//alert(msg);
				        		$(".altpoint"+regid+".total").attr("value",$("#points_total",msg).html());
				        		$(".point"+regid+".pointaverage").attr("value",$("#points_average",msg).html());
				        		$(".altpoint"+regid+".altpointaverage").attr("value",$("#points_altaverage",msg).html());
				        		$(".participant.position").each(function(){
				        			var this_regid=$(this).attr("data_regid");
				        			var this_posn=$("#points_position_"+this_regid,msg).html();
				        			$(this).attr("value",this_posn);
				  	      		});

								curr.animate({background:"rgb(50,150,50)"},100);
								curr.css("background","rgb(50,150,50)");
								setTimeout(function(){curr.animate({background:"transparent"},100);},1000);
								setTimeout(function(){curr.css("background","transparent");},1000);
							},error: function (xhr, ajaxOptions, thrownError) {
						           //alert(xhr.status);
						           //alert(xhr.responseText);
						           //alert(thrownError);
								curr.animate({background:"rgb(150,50,50)"},100);
								curr.css("background","rgb(150,50,50)");
								setTimeout(function(){curr.animate({background:"transparent"},100);},1000);
								setTimeout(function(){curr.css("background","transparent");},1000);
						       }
							});
				var avg=parseFloat(tot/cnt);
				//$(".point"+regid+".pointaverage").attr("value",avg);
				//alert(avg);
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
		Gender :<select class='gender_selection'>
		<option value='male'>Male</option>
		<option value='female'>Female</option>
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
		$gender=mysql_real_escape_string($_COOKIE['gender']);
		$query="SELECT * FROM `tournament_participants` WHERE `gender` = '$gender' AND `group` = '{$age_group}' AND `rink".$rink_no."` = '1' ORDER BY `$_COOKIE[sortby]`,`name`";
		//displayinfo($query);
				$res=mysql_query($query);
				if(mysql_error())displayerror(mysql_error()." for query ".$query);
				//displayinfo(mysql_num_rows($res));
if($type=="timer")
$this->updateTimerPositions($rink_no,$age_group,$gender);
if($type=="points")
{
	$this->updatePointsPositions($rink_no,$age_group,$gender);

	$html.="<div id='max_min' style=\"position:absolute;right:300px;top:220px\">MAXIMUM and MINIMUM Points are ".($this->config_value("rink{$rink_no}_cut_high_low","true")?"CUT":"")."</div>";
	$alternative=$this->config_value("rink{$rink_no}_alternative_points","true");	
	$html.="<style>input[type=text].pointinput,input[type=text].altpointinput,input[type=text].altpointaverage,input[type=text].pointaverage{width:5%!important;text-align:center}</style>";
	$html.="<style>input[type=text].total,input[type=text].position,input[type=text].altpointaverage,input[type=text].pointaverage{width:5%!important;text-align:center}</style>";
	$html.="<style>input[type=text].smallinp{width:2%!important;}</style>";
}	
//displayinfo($query);
if($type=="points")
$html.="
<pre>Reg. Name         Representing   Age Group  Point 1  Point 2  Point 3  Point 4  Point 5  Average Posn".($alternative==1?" Pt 1   Pt 2   Pt 3   Pt 4   Pt 5  Average  Total  Position":"")."</pre>
";

		while($row=mysql_fetch_assoc($res)){
			$grp=$this->grouptostring($row['group']);
			$regid=$row['id'];

$html.=<<<HTML
<div class='participant_div $row[gender] $row[club] $row[group]'>
<input type='text' disabled class='participant smallinp' name='regno' data_id="$row[regno]" data_regid="$regid" value="$row[regno]">
<input type='text' disabled class='participant' name='name' data_id="$row[regno]" data_regid="$regid" value="$row[name]">
<input type='text' disabled class='participant' name='club' data_id="$row[regno]" data_regid="$regid" value="$row[club]">
<input type='text' disabled class='participant agegroup_input' data_group="$row[group]" disabled name='group' data_id="$row[regno]" data_regid="$regid" value="$grp">  
HTML;
		if($type=="timer")
	{
$q="SELECT * FROM `tournament_timer` WHERE `regid` = '{$regid}' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}'";
$q_insert="INSERT INTO `tournament_timer`(`regid`,`group`,`rinkno`) VALUES('{$regid}','{$age_group}','{$rink_no}')";
$r=mysql_query($q);
if(mysql_error())displayerror(mysql_error());

if(mysql_num_rows($r)==0)mysql_query($q_insert);
if(mysql_error())displayerror(mysql_error());

if(mysql_num_rows($r)==0)// now only new data was added
{for($j=1;$j<=5;$j++)
$html.=<<<HTML
<input type='text' class='participant timer$regid timerinput' placeholder='Timer {$j}' name='timer{$j}' data_id="$row[regno]" data_regid="$regid" value="">
HTML;
$html.="<input type='text' disabled class='participant timer$regid timeraverage' placeholder='Timer Average' name='timeravg' data_id='$row[regno]' data_regid='$regid' value=''>";
$html.="<input type='text' placeholder='Position' disabled class='participant smallinp timer$regid position' name='position' data_regno='$row[regno]' data_id='0'  data_regid='$regid' value=''>";
}
else
while($rowvar=mysql_fetch_assoc($r))
{
	for($j=1;$j<=5;$j++)
	{
	$val=$rowvar['timer'.$j];
	$val=($val!="59:59:59"?$val:"");
	$html.="<input type='text' class='participant timer$regid timerinput' placeholder='Timer {$j}' name='timer{$j}' data_id='$row[regno]' data_regid='$regid' value='$val'>";
	}
	$val=$rowvar['timeravg']; 
	$val=($val!="59:59:59"?$val:"");
	$html.="<input type='text' disabled class='participant timer$regid timeraverage' placeholder='Timer Average' name='timeravg' data_id='$row[regno]'  data_regid='$regid' value='$val'>";
$posn=($rowvar[position]!="0"?$rowvar[position]:"");
$html.="<input type='text' placeholder='Position' disabled class='participant smallinp timer$regid position' name='position' data_regno='$row[regno]' data_id='$rowvar[position]'  data_regid='$regid' value='$posn'>";
}

$html.=<<<HTML
</div>
HTML;
}	// TYPE TIMER;
else 		if($type=="points")
	{
$q="SELECT * FROM `tournament_points` WHERE `regid` = '$regid' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}'";
$q_insert="INSERT INTO `tournament_points`(`regid`,`group`,`rinkno`) VALUES('$regid','{$age_group}','{$rink_no}')";
$r=mysql_query($q);
if(mysql_error())displayerror(mysql_error());

if(mysql_num_rows($r)==0)mysql_query($q_insert);
if(mysql_error())displayerror(mysql_error());


if(mysql_num_rows($r)==0)// now only new data was added
{
	for($j=1;$j<=5;$j++)
{
$html.=<<<HTML
<input type='text' class='participant point$regid pointinput' placeholder='Point {$j}' name='point{$j}' data_id="$row[regno]" data_regid="$regid" value="">
HTML;
$html.="<input style='bottom:-10px' type='text' class='participant point$regid pointinput' placeholder='Point {$j}' name='point{$j}' data_id='$row[regno]' data_regid='$regid' value=''>";
}
$html.="<input type='text' disabled class='participant point$regid pointaverage' placeholder='Average Points' name='pointavg' data_id='$row[regno]' data_regid='$regid' value=''>";

if($alternative){
for($j=1;$j<=5;$j++)
$html.="<input type='text' class='participant altpoint$regid altpointinput' placeholder='Style {$j}' name='altpoint{$j}' data_id='$row[regno]' data_regid='$regid' value=''>";
$html.="<input type='text' disabled class='participant altpoint$regid altpointaverage' placeholder='Average Points' name='pointavg' data_id='$row[regno]' data_regid='$regid' value=''>";
}
$html.="<input type='text' placeholder='Position' disabled class='participant smallinp point$regid position' name='position' data_regno='$row[regno]' data_id='0' data_regid='$regid' value=''>";
}
else
while($rowvar=mysql_fetch_assoc($r))
{
	for($j=1;$j<=5;$j++)
	{
	$val=$rowvar['point'.$j];
	$val=($val!="9999"?$val:"");
	$html.="<input type='text' class='participant point$regid pointinput' placeholder='Point {$j}' name='point{$j}' data_id='$row[regno]' data_regid='$regid' value='$val'>";
	}
	$val=$rowvar['pointavg']; 
	$val=($val!="9999"?$val:"");
	$html.="<input type='text' disabled class='participant point$regid pointaverage' placeholder='Average' name='pointavg' data_id='$row[regno]' data_regid='$regid' value='$val'>";
	
	if($alternative)
	{
		for($j=1;$j<=5;$j++)
	{
	$val=$rowvar['alt_point'.$j];
	$val=($val!="9999"?$val:"");
	$html.="<input type='text' style='position:relative;' class='participant altpoint$regid altpointinput' placeholder='Style {$j}' name='altpoint{$j}' data_id='$row[regno]' data_regid='$regid' value='$val'>";
	}
	$val=$rowvar['alt_pointavg']; 
	$val=($val!="9999"?$val:"");
	$val1=$rowvar['total']; 
	$val1=($val1!="9999"?$val1:"");
	$html.="<input type='text' disabled class='participant altpoint$regid altpointaverage' placeholder='Average' name='pointavg' data_id='$row[regno]' data_regid='$regid' value='$val'>";
	$html.="<input type='text' disabled class='participant altpoint$regid total' placeholder='Total' name='total' data_id='$row[regno]' value='$val1'>";
	}

	$posn=($rowvar[position]!="0"?$rowvar[position]:"");
	$html.="<input type='text' placeholder='Position' disabled class='participant smallinp point$regid position' name='position' data_regno='$row[regno]' data_id='$rowvar[position]' data_regid='$regid' value='$posn'>";
}

$html.=<<<HTML
</div>
HTML;

}	// TYPE POINTS;


	}


//Note : close </div> for the class participant in each case of rink type
		return $html;
	}

	public function actionUpload() {
		global $sourceFolder;		global $moduleFolder;
		if(!isset($_FILES["file"]["name"]))
{
	//displayinfo(explode(".",str_replace("/",".", "25/24/1993"))[0]);
$html=<<<HTML
Upload an excel sheet with list of Participants and appropriate data.<br /><br />Download the 
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
			$dob=($excelData[$i][$ii++]);
			$bloodgrp= mysql_real_escape_string($excelData[$i][$ii++]);
			$club= mysql_real_escape_string($excelData[$i][$ii++]);
			$phone1= mysql_real_escape_string($excelData[$i][$ii++]);
			$phone2= mysql_real_escape_string($excelData[$i][$ii++]);
	
	$d=explode(".",str_replace(":",".",str_replace("-",".",str_replace("/",".", "$dob"))))[0];
	$m=explode(".",str_replace(":",".",str_replace("-",".",str_replace("/",".", "$dob"))))[1];
	$y=explode(".",str_replace(":",".",str_replace("-",".",str_replace("/",".", "$dob"))))[2];
	$y=intval($y);
	//displayinfo($dob."   ".$d."   ".$m."   ".$y);
	$rinks=array();
	foreach ($eventname as $ev1)
	{
	$dd=mysql_real_escape_string($excelData[$i][$ii++]);
	$rinks[]=(($dd=='X'||$dd=='Y'||$dd=='x'||$dd=='y'||$dd=='.')?1:0);
	}

	$age=$this->$nowyear-$y;
	$grp=$this->findgroup($age);
			$sql1="INSERT IGNORE INTO $table VALUES ('$regno','$name', '$age','$gender','$grp', '$y-$m-$d', '$phone1', '$phone2');";
			
	$registerno=$this->getRegisterNo($grp);

	$sql="INSERT IGNORE INTO $table(`id`,`regno`,`name`,`gender`,`club`, `group`,`dob`,`blood`, `phone1`, `phone2`";
	foreach ($eventname as $ev1)
	$sql.=",`".$ev1."`";
	$sql.=") VALUES ('','$registerno','$name','$gender','$club','$grp','$y-$m-$d','$bloodgrp', '$phone1', '$phone2'";
	foreach ($rinks as $r1)
	$sql.=",".$r1."";
	$sql.=");";
	//displayinfo($sql);
	
	$result=mysql_query($sql);
	//echo $sql."<br />";
	if(mysql_error())
	displayerror("ERROR WAS:".mysql_error());
	else $ctr++;
			}		
	displayinfo($ctr." entr".($ctr>1?"ies":"y")." added successfully");

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


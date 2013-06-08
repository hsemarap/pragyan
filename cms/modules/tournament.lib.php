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
			$html="Your update query was ".$query."<br />";
			mysql_query($query);
			if(mysql_error())$html.='Has errors '.mysql_error()."<br />";
			else $html.="Executed successfully"."<br />";
			return $html;
		}
		if(isset($_POST['timer_update'])){
			$rink_no=$_POST['rinkno'];
			$age_group=$_POST['agegroup'];
			$reg_no=$_POST['regno'];
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

			$q="UPDATE `tournament_timer` SET `timeravg` = '$avg' , `timer1` = '$t1' , `timer2` = '$t2' , `timer3` = '$t3' , `timer4` = '$t4' , `timer5` = '$t5' WHERE `regno` = '{$reg_no}' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}'";
			$html="<div id='timer_update'>".$q."</div>";
			mysql_query($q);
			$q="SELECT * FROM `tournament_timer` WHERE `timeravg` != '59:59:59' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}' ORDER BY `timeravg` ";
			$i=0;$prev=99991;
			$html.="<div id='timer_position'>$q</div>";//.$t1." YY ".$t2." YY ".$t3." YY ".strtotime($t1)." XX ".strtotime($t2)." XX ".strtotime($t3)."</div>";
			$res=mysql_query($q);
			while($row=mysql_fetch_assoc($res))
			{	if($prev!=$row['timeravg'])
				$i++;
				$prev=$row['timeravg'];
				$query="UPDATE `tournament_timer` SET `position` = '$i' WHERE `regno` = '$row[regno]'";
				mysql_query($query);
				//if($reg_no==$row['regno'])
				$html.="<div id='timer_position_$row[regno]'>$i</div>";
			}
			return $html;			
		}
		if(isset($_POST['points_update'])){
			$rink_no=$_POST['rinkno'];
			$age_group=$_POST['agegroup'];
			$reg_no=$_POST['regno'];
			$t[]=$_POST['point1'];
			$t[]=$_POST['point2'];
			$t[]=$_POST['point3'];
			$t[]=$_POST['point4'];
			$t[]=$_POST['point5'];	


			$t1=($t1!=""?$t1:"9999");
			$t2=($t2!=""?$t2:"9999");
			$t3=($t3!=""?$t3:"9999");
			$t4=($t4!=""?$t4:"9999");
			$t5=($t5!=""?$t5:"9999");
			
			if($this->config_value("rink{$rink_no}_cut_high_low","true"))
			$avg=$this->findPointAverage($t,1);
			else
			$avg=$this->findPointAverage($t,0);

			$q="UPDATE `tournament_points` SET `pointavg` = '$avg' , `point1` = '$t[0]' , `point2` = '$t[1]' , `point3` = '$t[2]' , `point4` = '$t[3]' , `point5` = '$t[4]' WHERE `regno` = '{$reg_no}' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}'";
			$html="<div id='points_update'>".$q."</div>";
			mysql_query($q);
			$q="SELECT * FROM `tournament_points` WHERE `pointavg` != '9999' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}' ORDER BY `pointavg` DESC ";
			$i=0;$prev="random_string";
			$html.="<div id='points_position'>$q</div>";
			$html.="<div id='points_average'>$avg</div>";			
			$res=mysql_query($q);
			while($row=mysql_fetch_assoc($res))
			{	if($prev!=$row['pointavg'])
				$i++;
				$prev=$row['pointavg'];
				$query="UPDATE `tournament_points` SET `position` = '$i' WHERE `regno` = '$row[regno]'";
				mysql_query($query);
				$html.="<div id='points_position_$row[regno]'>$i</div>";
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
	public function updateTimerPositions($rink_no,$age_group){
		$q1="SELECT * FROM `tournament_timer` WHERE `timeravg` != '59:59:59' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}' ORDER BY `timeravg` ";
		$i1=0;$prev="random_string";
		$res1=mysql_query($q1);
		while($row1=mysql_fetch_assoc($res1))
		{	if($prev!=$row1['timeravg'])
			$i1++;
			$prev=$row1['timeravg'];
			$query1="UPDATE `tournament_timer` SET `position` = '$i1' WHERE `regno` = '$row1[regno]'";
			mysql_query($query1);
			if(mysql_error())displayerror(mysql_error());
		}
	}
	public function updatePointsPositions($rink_no,$age_group){
		$q1="SELECT * FROM `tournament_points` WHERE `pointavg` != '9999' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}' ORDER BY `timeravg` DESC";
		$i1=0;$prev="random_string";
		$res1=mysql_query($q1);
		while($row1=mysql_fetch_assoc($res1))
		{	if($prev!=$row1['pointavg'])
			$i1++;
			$prev=$row1['pointavg'];
			$query1="UPDATE `tournament_points` SET `position` = '$i1' WHERE `regno` = '$row1[regno]'";
			mysql_query($query1);
			if(mysql_error())displayerror(mysql_error());
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
			$(".timerinput").change(function(){
				var regno=$(this).attr("data_id");
				var tot=0,cnt=0,curr=$(this).parent();
				$(".timer"+regno+".timerinput").each(function(){
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
				        		agegroup:curr.find(".agegroup_input").attr("data_group"),
				        		rinkno:cookie_rinkno,
				        		timer1:($(".timer"+regno+".timerinput")[0].value==""?"59:59:59":$(".timer"+regno+".timerinput")[0].value),
				        		timer2:($(".timer"+regno+".timerinput")[1].value==""?"59:59:59":$(".timer"+regno+".timerinput")[1].value),
				        		timer3:($(".timer"+regno+".timerinput")[2].value==""?"59:59:59":$(".timer"+regno+".timerinput")[2].value),
				        		timer4:($(".timer"+regno+".timerinput")[3].value==""?"59:59:59":$(".timer"+regno+".timerinput")[3].value),
				        		timer5:($(".timer"+regno+".timerinput")[4].value==""?"59:59:59":$(".timer"+regno+".timerinput")[4].value)
				        	},success:function(msg){
				        		
				        		//alert($("#timer_update",msg).html());
				        		/*
				        		var position=$("#timer_position",msg).html();
				        		curr.find(".position").attr("value",position);
				        		*/
				        		
				        		$(".participant.position").each(function(){
				        			var this_regno=$(this).attr("data_regno");
				        			var this_posn=$("#timer_position_"+this_regno,msg).html();
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
				$(".timer"+regno+".timeraverage").attr("value",avg);
				//alert(avg);
			});
/*  POINTS CODE */
			$(".pointinput").change(function(){
				var regno=$(this).attr("data_id");
				var tot=0.00,cnt=0,curr=$(this).parent();
				$(".point"+regno+".pointinput").each(function(){
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
					        	
				        	$.ajax({
				        		type:'POST',
				        		url:"./+edit",
				        		data:
				        	{
				        		points_update:"1",
				        		regno:regno,
				        		agegroup:curr.find(".agegroup_input").attr("data_group"),
				        		rinkno:cookie_rinkno,
				        		point1:($(".point"+regno+".pointinput")[0].value==""?9999:$(".point"+regno+".pointinput")[0].value),
				        		point2:($(".point"+regno+".pointinput")[1].value==""?9999:$(".point"+regno+".pointinput")[1].value),
				        		point3:($(".point"+regno+".pointinput")[2].value==""?9999:$(".point"+regno+".pointinput")[2].value),
				        		point4:($(".point"+regno+".pointinput")[3].value==""?9999:$(".point"+regno+".pointinput")[3].value),
				        		point5:($(".point"+regno+".pointinput")[4].value==""?9999:$(".point"+regno+".pointinput")[4].value)
				        	},success:function(msg){
				        		
				        		//alert($("#points_average",msg).html());
				        		/*
				        		var position=$("#timer_position",msg).html();
				        		curr.find(".position").attr("value",position);
				        		*/
				        		//alert(msg);
				        		$(".point"+regno+".pointaverage").attr("value",$("#points_average",msg).html());
				        		$(".participant.position").each(function(){
				        			var this_regno=$(this).attr("data_regno");
				        			var this_posn=$("#points_position_"+this_regno,msg).html();
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
				var avg=parseFloat(tot/cnt);
				//$(".point"+regno+".pointaverage").attr("value",avg);
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
				if(mysql_error())displayerror(mysql_error()." for query ".$query);
				//displayinfo(mysql_num_rows($res));
if($type=="timer")
$this->updateTimerPositions($rink_no,$age_group);
if($type=="points")
{
	$this->updatePointsPositions($rink_no,$age_group);
	$html.="<div id='max_min' style=\"position:absolute;right:300px;top:200px\">MAXIMUM and MINIMUM Points are ".($this->config_value("rink{$rink_no}_cut_high_low","true")?"CUT":"")."</div>";
}	

		while($row=mysql_fetch_assoc($res)){
			$grp=$this->grouptostring($row['group']);
$html.=<<<HTML
<div class='participant_div $row[gender] $row[club] $row[group]'>
<input type='text' disabled class='participant' name='regno' data_id="$row[regno]" value="$row[regno]">
<input type='text' disabled class='participant' name='name' data_id="$row[regno]" value="$row[name]">
<input type='text' disabled class='participant' name='club' data_id="$row[regno]" value="$row[club]">
<input type='text' disabled class='participant agegroup_input' data_group="$row[group]" disabled name='group' data_id="$row[regno]" value="$grp">  
HTML;
		if($type=="timer")
	{
$q="SELECT * FROM `tournament_timer` WHERE `regno` = '{$row['regno']}' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}'";
$q_insert="INSERT INTO `tournament_timer`(`regno`,`group`,`rinkno`) VALUES('{$row['regno']}','{$age_group}','{$rink_no}')";
$r=mysql_query($q);
if(mysql_error())displayerror(mysql_error());

if(mysql_num_rows($r)==0)mysql_query($q_insert);
if(mysql_error())displayerror(mysql_error());

if(mysql_num_rows($r)==0)// now only new data was added
{for($j=1;$j<=5;$j++)
$html.=<<<HTML
<input type='text' class='participant timer$row[regno] timerinput' placeholder='Timer {$j}' name='timer{$j}' data_id="$row[regno]" value="">
HTML;
$html.="<input type='text' disabled class='participant timer$row[regno] timeraverage' placeholder='Timer Average' name='timeravg' data_id='$row[regno]' value=''>";
$html.="<input type='text' placeholder='Position' disabled class='participant timer$row[regno] position' name='position' data_regno='$row[regno]' data_id='0' value=''>";
}
else
while($rowvar=mysql_fetch_assoc($r))
{
	for($j=1;$j<=5;$j++)
	{
	$val=$rowvar['timer'.$j];
	$val=($val!="59:59:59"?$val:"");
	$html.="<input type='text' class='participant timer$row[regno] timerinput' placeholder='Timer {$j}' name='timer{$j}' data_id='$row[regno]' value='$val'>";
	}
	$val=$rowvar['timeravg']; 
	$val=($val!="59:59:59"?$val:"");
	$html.="<input type='text' disabled class='participant timer$row[regno] timeraverage' placeholder='Timer Average' name='timeravg' data_id='$row[regno]' value='$val'>";
$posn=($rowvar[position]!="0"?$rowvar[position]:"");
$html.="<input type='text' placeholder='Position' disabled class='participant timer$row[regno] position' name='position' data_regno='$row[regno]' data_id='$rowvar[position]' value='$posn'>";
}

$html.=<<<HTML
</div>
HTML;

}	// TYPE TIMER;
else 		if($type=="points")
	{
$q="SELECT * FROM `tournament_points` WHERE `regno` = '{$row['regno']}' AND `rinkno` = '{$rink_no}' AND `group` = '{$age_group}'";
$q_insert="INSERT INTO `tournament_points`(`regno`,`group`,`rinkno`) VALUES('{$row['regno']}','{$age_group}','{$rink_no}')";
$r=mysql_query($q);
if(mysql_error())displayerror(mysql_error());

if(mysql_num_rows($r)==0)mysql_query($q_insert);
if(mysql_error())displayerror(mysql_error());

if(mysql_num_rows($r)==0)// now only new data was added
{for($j=1;$j<=5;$j++)
$html.=<<<HTML
<input type='text' class='participant point$row[regno] pointinput' placeholder='Point {$j}' name='point{$j}' data_id="$row[regno]" value="">
HTML;
$html.="<input type='text' disabled class='participant point$row[regno] pointaverage' placeholder='Average Points' name='pointavg' data_id='$row[regno]' value=''>";
$html.="<input type='text' placeholder='Position' disabled class='participant point$row[regno] position' name='position' data_regno='$row[regno]' data_id='0' value=''>";
}
else
while($rowvar=mysql_fetch_assoc($r))
{
	for($j=1;$j<=5;$j++)
	{
	$val=$rowvar['point'.$j];
	$val=($val!="9999"?$val:"");
	$html.="<input type='text' class='participant point$row[regno] pointinput' placeholder='Point {$j}' name='point{$j}' data_id='$row[regno]' value='$val'>";
	}
	$val=$rowvar['pointavg']; 
	$val=($val!="9999"?$val:"");
	$html.="<input type='text' disabled class='participant point$row[regno] pointaverage' placeholder='Average Points' name='pointavg' data_id='$row[regno]' value='$val'>";
$posn=($rowvar[position]!="0"?$rowvar[position]:"");
$html.="<input type='text' placeholder='Position' disabled class='participant point$row[regno] position' name='position' data_regno='$row[regno]' data_id='$rowvar[position]' value='$posn'>";
}

$html.=<<<HTML
</div>
HTML;

}	// TYPE POINTS;


	}


//Note : close </div> for the class participant in each case of rink type
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


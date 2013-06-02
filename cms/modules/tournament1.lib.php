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
 * @copyright (c) 2008 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
class tournament implements module, fileuploadable {
  private $userId;
  private $moduleComponentId;
  private $action;
  public function getHtml($gotuid, $gotmoduleComponentId, $gotaction) {
	
    $this->userId = $gotuid;
    $this->moduleComponentId = $gotmoduleComponentId;
    $this->action = $gotaction;

    if ($this->action == "view")
      return $this->actionView();
    if ($this->action == "check")
      return $this->actionCheck();
    if ($this->action == "submit")
      return $this->actionSubmit();
    if ($this->action == "score")
      return $this->actionScore();
    if ($this->action == "print")
      return $this->actionPrint();
    if ($this->action == "upload")
      return $this->actionUpload();			
  }

  


  public function actionView() {
/*    
global $urlRequestRoot, $moduleFolder, $cmsFolder,$templateFolder,$sourceFolder,$cmsFolder,$STARTSCRIPTS;
    $scriptsFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/scripts";
    $imagesFolder = "$urlRequestRoot/$cmsFolder/$templateFolder/common/images";
    $js=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/dpic.js";
    $css=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/jquery-ui-1.8.16.custom.css";
    $css1=$urlRequestRoot."/".$cmsFolder."/".$moduleFolder."/qaos1/styles/main.css";
    $mcid=$this->moduleComponentId;
*/
    require_once($sourceFolder."/".$moduleFolder."/tournament/config.php");
$asc='ASC';
$next='DESC';
if(isset($_GET['order']))
{
$asc=$_GET['order'];
if($asc=='DESC')$next='ASC';
}
session_start();
mysql_connect($server,$user,$password);
mysql_selectdb($database);
if(sizeof($_POST)>0)
{
$name=mysql_real_escape_string($_POST['name']);
$dob=mysql_real_escape_string($_POST['dob']);
$y=substr($dob,-4);
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
//	d-m-yyyy
//	dd-mm-yyyy
//	d-mm-yyyy
// dd-m-yyyy
$phone1=mysql_real_escape_string($_POST['phone1']);
$phone2=mysql_real_escape_string($_POST['phone2']);
$regno=mysql_real_escape_string($_POST['reg_no']);
$gender=mysql_real_escape_string($_POST['gender']);
$club=mysql_real_escape_string($_POST['club']);
$rinks=array();
foreach ($eventname as $ev1)
{
$rinks[]=(isset($_POST["".$ev1])==1?1:0);
}
$nowyear=date('Y');
$nowyear=2012;
$y=intval($y);
//$m=intval($m);
//$d=intval($d);

$age=$nowyear-$y;
$grp=findgroup($age);

$sql="INSERT IGNORE INTO $table (`reg_no`,`name`, `age`,`gender`,`club`, `age_group`, `date`, `phone1`, `phone2`";
foreach ($eventname as $ev1)
$sql.=",`".$ev1."`";
$sql.=") VALUES ('$regno','$name', '$age','$gender','$club','$grp', '$y-$m-$d', '$phone1', '$phone2'";
foreach ($rinks as $r1)
$sql.=",".$r1."";
$sql.=");";
$result=mysql_query($sql);
//echo $sql;
if(mysql_error())
echo "ERROR WAS1:".mysql_error();

$sql="INSERT IGNORE INTO `$grp $gender`(`reg_no`,`name`, `dob`,`club`) VALUES ('$regno','$name', '$y-$m-$d', '$club');";
//$result=mysql_query($sql);
//echo $sql;
if(mysql_error())
echo "ERROR WAS:".mysql_error();
}
$ctr=1;

$action="";
    
      return $action;
  }
  public function actionCheck() {
  $action="";
  return $action;
  }
  public function actionSubmit() {
  $action="";
  return $action;
  }
  public function actionScore() {
  $action="";
  return $action;
  }
  public function actionPrint() {
  $action="";
  return $action;
  }

  public function actionUpload() {
  $action="";
  return $action;
  }
}

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
	
	public function createModule($compId) {
		global $sourceFolder, $moduleFolder;
		return true;
		}

	public function deleteModule($moduleComponentId){
		return true;
	}

	public function copyModule($moduleComponentId,$newId){
		return true;//Pls Ctct me later for the updated code
	}
	
 }


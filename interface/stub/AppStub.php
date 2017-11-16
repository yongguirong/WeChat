<?php
/*
 * @brief 	App相关接口协议打解包、网络io处理类
 * @author	scottzhou
 * @date	2012-01-18	 
 * 
 */

require_once ROOT_PATH . '/interface/common/Common.php';

class AppStub {

	public function RegistAppInfo($appId, $appName, $appCname, $owner, 
			$ownerUin, $isDistribute, $property, $city,	
			$svn, $status, $vip, $sip, $cdnSvn, 
			$appTag=NULL, $appSubTag=NULL, $relationChainFlag=NULL) {
		$clusterName = $appName;
		$clusterCname = $appCname;
		$clusterDesc = "";
		$clusterType = "app";
		
		$data = array(
			"appId"=>$appId,
			"appName"=>$appName,
			"appCname"=>$appCname,
			"clusterName"=>$clusterName,
			"clusterCname"=>$clusterCname,
			"clusterType"=>$clusterType,
			"clusterDesc"=>$clusterDesc,
			"owner"=>$owner,
			"ownerUin"=>$ownerUin,
			"isDistribute"=>$isDistribute,
			"property" => $property,
			"city" => $city,
			"svn" => $svn,
			"status" => $status,
			"vip" => $vip,
			"sip" => $sip,
			"cdnSvn" => $cdnSvn
		);
		
		if ($appTag !== NULL){
			$data['appTag'] = $appTag;
		}
		
		if ($appSubTag !== NULL){
			$data['appSubTag'] = $appSubTag;
		}
		
		if ($relationChainFlag !== NULL){
			$data['relationChainFlag'] = $relationChainFlag;
		}

		return doRequestToDataAccess("DA_App_RegistAppInfo", $data);
	}
	
	public function GetAppBasicInfo($appId=0, $tag=-1, $userId="", $appIdList = array()) {
		$data = array();
		if ($appId){
			$data['appId'] = $appId;
		}
		if ($userId){
			$data['userId'] = $userId;
		}
		if ($tag == 0 || $tag == 1){		
			$data['tag'] = $tag;
		}
		if($appIdList != array()) {
			$data['appIdList'] = $appIdList;
		}

		return doRequestToDataAccess("DA_App_GetAppBasicInfo", $data);
	}
	
	public function GetLatestVport($appId) {
		$data = array("appId"=>$appId);
		
		return doRequestToDataAccess("DA_App_GetLatestVport", $data);
	}
	
	public function GetAllAppList($fieldList=NULL, $status=NULL, 
								$ownerUin=NULL, $appId = NULL) {
		$data = array();
		if (!empty($fieldList)){
			$data['fieldList'] = $fieldList;
		}
		if ($status !== NULL){
			$data['status'] = $status;
		}
		if ($ownerUin !== NULL){
			$data['ownerUin'] = $ownerUin;
		}
		if ($appId !== NULL){
			$data['appId'] = $appId;
		}

		return doRequestToDataAccess("DA_App_GetAllAppList", $data);
	}

	public function UpdateAppInfo($reqParam) {

		return doRequestToDataAccess("DA_App_UpdateAppInfo", $reqParam);
	}
	
	public function RegistAppInfoCommon($data){
		
		return doRequestToDataAccess("DA_App_RegistAppInfoCommon", $data);
	}
	
	public function GetAllService(){
		
		return doRequestToDataAccess("DA_App_GetAllService", array());
	}

	public function DeleteApp($data){
		
		return doRequestToDataAccess("DA_App_DeleteApp", $data);
	}

	public function SimpleGetAppBasicInfo($data) {
		
		return doRequestToDataAccess("DA_App_GetAppBasicInfo", $data);
	}
	
	public function QueryActiveCvmAppIdList() {
		
		return doRequestToDataAccess("DA_App_QueryActiveCvmAppIdList", array());
	}
	
	public function UpdateAppInfoByUin($reqParam) {

		return doRequestToDataAccess("DA_App_UpdateAppInfoByUin", $reqParam);
	}
}

?>
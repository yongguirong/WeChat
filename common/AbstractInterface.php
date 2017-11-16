<?php

abstract class AbstractInterface {
	
	// common input parameters
	protected $_toUserName;
	protected $_fromUserName;
	protected $_createTime;
	protected $_msgType;
	protected $_msgId;
	
	//text
	protected $_content;
	
	//image
	protected $_picUrl;
	
	//location
	protected $_location_x;
	protected $_location_y;
	protected $_scale;
	protected $_label;
	
	//link
	protected $_title;
	protected $_description;
	protected $_url;
	
	//event
	protected $_event;
	protected $_eventKey;
	
	protected $_xmlObj;
	
	// common return parameters
	protected  $_retValue = 0;
	protected  $_retMsg = "";
	protected  $_data = array();
	protected  $_responseText = "";
	
	public function getResponseText(){ 
		return $this->_responseText;
	}
		
	public function verifyCommonInput(&$xmlObj) {
		interface_log(DEBUG, 0, "verifyCommonInput!");
		if(null == $xmlObj) {
			$this->_retValue = EC_INVALID_INPUT;
			$this->_retMsg = "xml obj error!";
			return false;
		}
		$this->_xmlObj = $xmlObj;
		$this->_toUserName = (string)$xmlObj->ToUserName;
		$this->_fromUserName = (string)$xmlObj->FromUserName;
		$this->_createTime = (int)$xmlObj->CreateTime;
		$this->_msgType = (string)$xmlObj->MsgType;
		$this->_msgId = (string)$xmlObj->MsgId;
		
		
		if($this->_msgType == 'event') {
			$this->_event = (string)$xmlObj->Event;
			$this->_eventKey = (string)$xmlObj->EventKey;
		}else if($this->_msgType == 'text'){
			$this->_content = (string)$xmlObj->Content;
		}else if($this->_msgType == 'image') {
			$this->_picUrl = (string)$xmlObj->PicUrl;
		}else if($this->_msgType == 'location') {
			$this->_location_x = (float)$xmlObj->Location_X;
			$this->_location_y = (float)$xmlObj->Location_Y;
			$this->_label = (string)$xmlObj->Label;
			$this->_scale = (float)$xmlObj->Scale;
		}else if($this->_msgType == 'link') {
			$this->_title = (string)$xmlObj->Title;
			$this->_description = (string)$xmlObj->Description;
			$this->_url = (string)$xmlObj->Url;
		}
		
		return $this->verifyInput($xmlObj);
		
	}
	
	
	/**
	 * 
	 * 加载需要用到的对象
	 */
	abstract public function initialize();
	/**
	 * 
	 * 输入校验
	 * @param array $args 输入参数
	 */
	abstract public function verifyInput(&$args);
	
	/**
	 * 设置数据 
	 */
	abstract public  function prepareData();
	
	/**
	 * 
	 * 请求处理
	 */
	abstract public function process();
	
	public function renderOutput() {
		$ret = array(
				"timestamp" => time(), 
				"retVal" => $this->_retValue, 
				"retMsg" => genErrMsg($this->_retValue , $this->_retMsg),
				"retStr" => genRetStr($this->_retValue),
				"retData" => $this->_data
			);
		interface_log(DEBUG, 0, "ret:" . json_encode($ret));
		return $ret;
	}
}

?>

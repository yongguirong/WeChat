<?php
require_once dirname(__FILE__) . '/WeChatCallBack.php';
/**
 * echo server implemention
 * @author pacozhong
 *
 */

class WeChatCallBackEchoServer extends WeChatCallBack{
	private $_event;
	private $_content;
	private $_eventKey;
	
	public function init($postObj) {
		if(false == parent::init($postObj)) {
			interface_log ( ERROR, EC_OTHER, "init fail!" );
			return false;
		}
		if($this->_msgType == 'event') {
			$this->_event = (string)$postObj->Event;
			$this->_eventKey = (string)$postObj->EventKey;
		}
		
		if($this->_msgType == 'text'){
			$this->_content = (string)$postObj->Content;
		}
		return true;
	}
	
	
	public function process(){

		if (!($this->_msgType == 'text' || ($this->_msgType == 'event' && $this->_event == 'CLICK'))) {
			interface_log(DEBUG, 0, "msgType:" . $this->_msgType . " event:" . $this->_event);
			return $this->makeHint ( "你发的不是文字或自定义菜单消息!" );
		}
		try {
			$STO = new SingleTableOperation("userinput", "ES");
			if($this->_msgType == 'event' && $this->_event == 'CLICK') {
				$mode = $this->_eventKey;
				if($mode != 'APPEND' && $mode != 'NORMAL'){
					return $this->makeHint("通过自定义菜单发送的模式不正确！");
				}
				//更新用户mode
				$ret = $STO->getObject(array("userId" => $this->_fromUserName));
				if(!empty($ret)) {
					$STO->updateObject(array('mode' => $mode), array("userId" => $this->_fromUserName));
				} else {
					$STO->addObject(array("userId" => $this->_fromUserName, 'mode' => $mode));
				}
				return $this->makeHint("模式设置成" . $mode);
			}else {
				$text = $this->_content;
				
				$ret = $STO->getObject(array("userId" => $this->_fromUserName));
				if(empty($ret)) {
					$STO->addObject(array("userId" => $this->_fromUserName));
					return $this->makeHint($text);	
				}else {
					$mode = $ret[0]['mode'];
					$STO->updateObject(array('input' => $ret[0]['input'] . $text), array("userId" => $this->_fromUserName));
					if($mode == 'APPEND') {
						return $this->makeHint($ret[0]['input'] . $text);
					}else {
						return $this->makeHint($text);
					}
				}
			}
			
		} catch (DB_Exception $e) {
			interface_log(ERROR, EC_DB_OP_EXCEPTION, "query db error" . $e->getMessage());
		}
		
	}	
}

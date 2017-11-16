<?php
require_once dirname(__FILE__) . '/WeChatCallBack.php';

class WeChatCallBackMYZL extends WeChatCallBack{
	
	
	private $interfaceName;
	private $value;
	private $fightInfo;
	private $lastOpInfo;
	private $userInfo;
	private $retStr;
	
	private function getOperandAndOperator(){
		//find current status of user
		$STO = new SingleTableOperation("cWaitingUser", "MYZL");
		$waitingRet = $STO->getObject(array("userId" => $this->_fromUserName));
		interface_log(DEBUG, 0, var_export($waitingRet , true));
		$STO->setTableName("cUser");
		$userInfo = $STO->getObject(array("userId" => $this->_fromUserName));
		$this->userInfo = $userInfo;
		interface_log(DEBUG, 0, var_export($userInfo, true));
		
		if($this->_msgType == 'event' && trim((string)$this->_postObject->Event) == "subscribe") {
			if(empty($userInfo)) {
				$this->interfaceName = "AddUser";
				return true;
			} else {
				$this->interfaceName = "WelcomeBack";
				return true;
			}
			
		}
		
		$eventKey = trim((string)$this->_postObject->EventKey);
		$tmp = explode("_", $eventKey);
		$operator = trim($tmp[0]);
		$operand = trim($tmp[1]);
		interface_log(DEBUG, 0, 'operator:' . $operator . '  operand:' . $operand);
		if($operator == "NEXT"){
			//check if in waiting list
			if(!empty($waitingRet)) {
				$this->interfaceName = "WaitOp";
				return true;
			}
			//getOp
			require_once dirname(__FILE__) . '/../interface/GetOp.php';
			$instance = new GetOp();
			
			if (! $instance->verifyCommonInput ( $this->_postObject )) {
				$ret =  $instance->renderOutput ();
				if($ret['retVal'] == EC_INVALID_INPUT) {
					$this->interfaceName = "InputErrorHint";
					return true;
				}
			}
			
			if (! $instance->initialize ()) {
				$ret =  $instance->renderOutput ();
				if($ret['retVal'] == EC_DB_OP_EXCEPTION) {
					$this->interfaceName = "DbErrorHint";
					return true;
				}
			}
			
			$instance->prepareData ();
			
			if (! $instance->process ()) {
				$ret =  $instance->renderOutput ();
				if($ret['retVal'] == EC_DB_OP_EXCEPTION) {
					$this->interfaceName = "DbErrorHint";
					return true;
				}
				if($ret['retVal'] == EC_USER_NOT_EXIST) {
					$this->interfaceName = "AddUser";
					return true;
				}
				if($ret['retVal'] == EC_FIGHT_NOT_EXIST) {
					$this->interfaceName = "Ready";
					return true;
				}
				if($ret['retVal'] == EC_MULTIPLE_FIGHT) {
					$this->interfaceName = "MultiFightHint";
					return true;
				}
				
			}
			
			$ret = $instance->renderOutput ();
			$this->fightInfo = $ret['retData'];
					
			$this->retStr = $instance->getResponseText();
			interface_log(DEUBUG, 0, "this->retStr:" . $this->retStr);
			if($this->fightInfo['operation'] == START || $this->fightInfo['operation'] == FIRST_END) {
				if($this->fightInfo['first'] == $this->_fromUserName) {
					$this->interfaceName = "Start";
				}else {
					$this->interfaceName = "WaitStart";
				}
				return true;
			}
			
			if($this->fightInfo['operation'] == SECOND_END) {
				$this->interfaceName = "SecondEndHint";
				return true;
			}
			
			if($this->fightInfo['operator'] != $this->_fromUserName) {
				$this->interfaceName = "WaitOperation";
				$this->value = $this->fightInfo['operation'];
				return true;
			}else {
				if($this->fightInfo['operation'] == PUT_MAGIC) {
					if($userInfo[0]['xsft'] <= 0 && 
						$userInfo[0]['hdcx'] <= 0 && 
						$userInfo[0]['chxs'] <= 0 && 
						$userInfo[0]['sszm'] <= 0){
						$this->interfaceName = "PutMagic";
						$this->value = "";
					}else {
						$this->interfaceName = "PutMagicHint";
					}
				}
				if($this->fightInfo['operation'] == CHIP_IN) {
					$this->interfaceName = "ChipInHint";
				}
				
				if($this->fightInfo['operation'] == SHOOT) {
					$this->interfaceName = "Shoot";
				}
			}
			
		}else if($operator == "CHIPIN") {
			$this->interfaceName = "ChipIn";
			$this->value = (int)$operand;
			interface_log(DEBUG, 0, "this->value" . $this->value);
		}else if($operator == "PUTMAGIC") {
			$this->interfaceName = "PutMagic";
			$this->value = $operand;
		} else {
			$this->interfaceName = "InputErrorHint";
			return true;
		}
	}
	
	
	public function process(){
		if($this->_msgType != 'event') {
			return $this->makeHint(MYZL_HINT);
		}
		$retStr = "";
		$this->getOperandAndOperator();
		interface_log(DEBUG, 0, "interfaceName:" . $this->interfaceName . "  value:" . $this->value);
		if($this->interfaceName == "WelcomeBack") {
			$retStr = "WelcomeBack";
		}
		if($this->interfaceName == "WaitOp") {
			$retStr = "等待系统匹配玩家";
		}
		
		if($this->interfaceName == "PutMagicHint") {
			$retStr = "请使用道具";
		} 
		if($this->interfaceName == "ChipInHint") {
			$retStr = "请下注";
		}
		
		if($this->interfaceName == "MultiFightHint") {
			$retStr = "错误的游戏状态";
		}
		
		if($this->interfaceName == "InputErrorHint" ) {
			$retStr = "输入错误";
		} 
		if($this->interfaceName == "DbErrorHint") {
			$retStr = "数据库连接错误";
		}
		
		if($this->interfaceName == "SecondEndHint") {
			if($this->_fromUserName == $this->fightInfo['current']) {
				$retStr = "等待对方结束游戏";	
			} else {
				$retStr = " ";//不能为空字符换
			}
		} 
		if($this->interfaceName == "WaitOperation") {
			$retStr = "等待对方【" . $GLOBALS['constants']['stepName'][$this->value] . "】，请稍后重试";
		}
		if($this->interfaceName == "WaitStart") {
			$retStr = "等待对方开始游戏！";
		}
		if($retStr) {
			return $this->makeHint($this->retStr ?  ($this->retStr . ($retStr == " " ? "": "， ") . $retStr) :  $retStr);
		}
		
		if($this->interfaceName == "Ready") {
			require_once dirname(__FILE__) . '/../interface/Ready.php';
			$obj = new Ready();
		} 
		if($this->interfaceName == "Start") {
			require_once dirname(__FILE__) . '/../interface/Start.php';
			$obj = new Start();
		}
		if($this->interfaceName == "AddUser") {
			require_once dirname(__FILE__) . '/../interface/AddUser.php';
			$obj = new AddUser();
		}
		if($this->interfaceName == "PutMagic") {
			require_once dirname(__FILE__) . '/../interface/PutMagic.php';
			$obj = new PutMagic();
			$obj->setOperand($this->value);
		} 
		
		if($this->interfaceName == "Shoot") {
			require_once dirname(__FILE__) . '/../interface/Shoot.php';
			$obj = new Shoot();
		} 
		if($this->interfaceName == "ChipIn") {
			require_once dirname(__FILE__) . '/../interface/ChipIn.php';
			$obj = new ChipIn();
			$obj->setOperand($this->value);
			interface_log(DEBUG, 0, 'this->value:' . $this->value . ' _operand:' . $obj->getOperand());
		} 
		
		$ret = $obj->verifyCommonInput($this->_postObject);
		if(false == $ret) {
			$rt = $obj->renderOutput();
			return $this->makeHint($rt['retMsg']);
		}
		$ret = $obj->initialize();
		if(false == $ret) {
			$rt = $obj->renderOutput();
			return $this->makeHint($rt['retMsg']);
		}
		
		$ret = $obj->prepareData();
		if(false == $ret) {
			$rt = $obj->renderOutput();
			return $this->makeHint($rt['retMsg']);
		}
		
		$ret = $obj->process();
		if(false == $ret) {
			$rt = $obj->renderOutput();
			interface_log(DEBUG, 0, var_export($rt, true));
			return $this->makeHint($rt['retMsg']);
		}
		return $this->makeHint($obj->getResponseText());
		
	}
}

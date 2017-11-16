<?php
/**
 *
 */

require_once dirname(__FILE__) . '/common/Common.php';


class ChipIn extends AbstractInterface {
  
	private $_args;
	private $_fightInfo;
	private $_userInfo;
	private $_oTable;
	private $_fightId;
	private $_operand;
	
	public function getOperand(){
		return $this->_operand;
	}
	public function setOperand($operand){
		$this->_operand = $operand;
	}
	
	public function verifyInput(&$args) {			
    	$this->_args = array(
    		"userId" => $this->_fromUserName,
    		"money" => $this->_operand
    	);
		
		return true;
	}

	public function initialize() {
		try {
			$this->_oTable = new SingleTableOperation ('cUser', 'MYZL');	
		} catch (Exception $e) {
			$errorNum = $this->_oTable->getErrorNum();
			$this->_retMsg = $this->_oTable->getErrorInfo().$e->getMessage();
			$this->_retValue = genRetCode($errorNum);
			interface_log(ERROR, $this->_retValue, $this->_retMsg);
			return false;
		}
		
		return true;
	}
	
	public function prepareData() {
		try {
			$this->_oTable->setTableName('cUser');
			$data = $this->_oTable->getObject ( array('userId' => $this->_args['userId']) );
			if (count ( $data ) == 0) {
				$this->_retValue = EC_RECORD_NOT_EXIST;
				$this->_retMsg = "userId:" . $this->_args ['userId'];
				interface_log ( ERROR, $this->_retValue, $this->_retMsg );
				return false;
			}
			$this->_userInfo = $data [0];
			
			$this->_oTable->setTableName('cFight');
			$this->_args ['userId'] = DbFactory::getInstance ('MYZL')->escape ( $this->_args ['userId'] );
			$data = $this->_oTable->getObject ( array ("_where" => " (user1='" . $this->_args ['userId'] . "' OR user2='" . $this->_args ['userId'] ."')" ) );
			if (empty ( $data )) {
				$this->_retValue = EC_RECORD_NOT_EXIST;
				$this->_retMsg = "no fight userId:" . $this->_args ['userId'];
				interface_log ( ERROR, $this->_retValue, $this->_retMsg );
				return false;
			} else if (count ( $data ) > 1) {
				$this->_retValue = EC_MULTIPLE_FIGHT;
				$this->_retMsg = "userId:" . $this->_args ['userId'];
				interface_log ( ERROR, $this->_retValue, $this->_retMsg );
				return false;
			} else {
				if ($data [0] ['operator'] != $this->_args ['userId']) {
					$this->_retValue = EC_NOT_THIS_USR_ORDER;
					$this->_retMsg =  "userId:" . $this->_args ['userId'];
					interface_log ( ERROR, $this->_retValue, $this->_retMsg );
					return false;
				}
				if($data[0]['operation'] != CHIP_IN) {
					$this->_retValue = EC_STEP_OPERATION_NOT_MATCH;
					$this->_retMsg = " userId:" . $this->_args ['userId'];
					interface_log ( ERROR, $this->_retValue, $this->_retMsg );
					return false;
				}
				if($this->_args['money'] > $data[0]['maxMoney'] || $this->_args['money'] < $data[0]['minMoney']) {
					$this->_retValue = EC_CHIP_MONEY_NOT_IN_RANGE;
					$this->_retMsg = "fightId:" . $this->_args ['fightId'] . 
										" userId:" . $this->_args ['userId'] . 
										" money:" . $this->_args['money'] . 
										" maxMoney:" . $data[0]['maxMoney'] . 
										" minMoney:" . $data[0]['minMoney'];
					interface_log ( ERROR, $this->_retValue, $this->_retMsg );
					return false;
				}
			}
			$this->_fightInfo = $data [0];
			$this->_fightId = $data[0]['fightId'];
		} catch (Exception $e) {
			$errorNum = $this->_oTable->getErrorNum();
			$this->_retMsg = $this->_oTable->getErrorInfo().$e->getMessage();
			$this->_retValue = genRetCode($errorNum);
			interface_log(ERROR, $this->_retValue, $this->_retMsg);
			return false;
		}
		return true;
	}
	
	
	public function process() {
		
		try {
			DbFactory::getInstance ('MYZL')->autoCommit ();
			
			$msgForOther = $this->_fightInfo['msgForOther'];
			$otherId = $this->_fightInfo['otherId'];
			if($otherId == $this->_fromUserName) {
				$this->_responseText .= $msgForOther;
				$this->_fightInfo['msgForOther'] = "";
			}
			
			$this->_fightInfo['otherId'] = ($this->_fromUserName == $this->_fightInfo['user1']) ? $this->_fightInfo['user2'] : $this->_fightInfo['user1'] ;
			if($this->_fightInfo['msgForOther'] == "") {
				$this->_fightInfo['msgForOther'] = "对方已加注" . $this->_operand . "金币";
			} else {
				$this->_fightInfo['msgForOther'] .= ", 对方已加注" . $this->_operand . "金币";
			}
			
			 
			if ($this->_userInfo ['money'] < $this->_args ['money']) {
				$this->_retValue = EC_NOT_ENOUGH_MONEY;
				interface_log ( ERROR, $this->_retValue );
				return false;
			}
			
			$this->_oTable->setTableName('cUser');
			$newMoney = $this->_userInfo['money'] - $this->_args['money'];
			$this->_oTable->updateObject ( array ('money' => $newMoney ), array ('userId' => $this->_args ['userId'] ) );
			$this->_fightInfo ['money'] += $this->_args ['money'];
			
			//$this->_fightInfo['lastOp'] = $this->_fightInfo['operator'] . ',' . CHIP_IN . ',' . $this->_operand;
			
			$ret = $this->setNextOpAndUser ();
			if ($ret ['code']) {
				$this->_retValue = EC_STEP_ERROR;
				$this->_retMsg = "fightId:" . $this->_fightId . " step:" . $this->_fightInfo['historyOp'];
				interface_log ( ERROR, $this->_retValue, $this->_retMsg );
				return false;
			}
			
			if ($this->_fightInfo ['historyOp'] == '') {
				$newHistoyOp = "CHIP_IN," . $this->_args ['userId'];
			} else {
				$newHistoyOp = $this->_fightInfo ['historyOp'] . "|CHIP_IN," . $this->_args ['userId'];
			}
			$this->_fightInfo ['historyOp'] = $newHistoyOp;
			
			if($this->_args['money'] > $this->_fightInfo['minMoney']) {
				$this->_fightInfo['minMoney'] = $this->_args['money'];
			}
			
			$this->_oTable->setTableName('cFight');
			$this->_oTable->updateObject ($this->_fightInfo, array('fightId' => $this->_fightId));
			
			DbFactory::getInstance ('MYZL')->tryCommit ();
			if($this->_responseText) {
				$this->_responseText .= ', ' . sprintf(MYZL_HINT_CHIPIN_SUC, $this->_operand, $GLOBALS['constants']['stepName'][$this->_fightInfo['operation']]);
			} else{
				$this->_responseText = sprintf(MYZL_HINT_CHIPIN_SUC, $this->_operand, $GLOBALS['constants']['stepName'][$this->_fightInfo['operation']]);
			}
		}catch (DB_Exception $e){
			DbFactory::getInstance('MYZL')->rollback();
			$errorNum = $this->_oTable->getErrorNum();
			$this->_retMsg = $this->_oTable->getErrorInfo().$e->getMessage();
			$this->_retValue = genRetCode($errorNum);
			interface_log(ERROR, $this->_retValue, $this->_retMsg);
			return false;
		}
		return true;
	}
	private function setNextOpAndUser() {
		if($this->_fightInfo['historyOp'] == '') {
			$retOp = CHIP_IN;
		} else {
			$steps = explode('|', $this->_fightInfo['historyOp']);
			$cnt = count($steps);
			$tmp1 = explode(',', $steps[$cnt-1]);
			$op1 = $tmp1[0];
			$user1 = $tmp1[1];
			
			if($op1 == CHIP_IN) {
				$retOp = PUT_MAGIC;
			}else if($op1 == SHOOT) {
				$retOp = CHIP_IN;
			} else if($op1 == PUT_MAGIC) {
				return array('code' => 1, 'msg'  => 'bad step ' . PUT_MAGIC);
			}
		}
		$operator = ($this->_args['userId'] == $this->_fightInfo['user1']) ? $this->_fightInfo['user2'] : $this->_fightInfo['user1'];
		$this->_fightInfo['operation'] = $retOp;
		$this->_fightInfo['operator'] = $operator;
	}
}

?>
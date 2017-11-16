<?php
/**
 * 准备
 */

require_once dirname(__FILE__) . '/common/Common.php';


class Ready extends AbstractInterface {
  
	private $_args;
	
	private $_oTable;

	public function verifyInput(&$args) {			
		
    	$this->_args = array(
    		"userId" => $this->_fromUserName
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
		return true;
	}
	
	public function process() {
		
		try {
			$this->_oTable->setTableName('cUser');
			$data = $this->_oTable->getObject(array('userId' => $this->_args['userId']));
			if(count($data) == 0) {
				$this->_retValue = EC_RECORD_NOT_EXIST;
				$this->_retMsg = "userId:" . $this->_args['userId'];
				interface_log(ERROR, $this->_retValue, $this->_retMsg);
				return false;
			}
			//check whether user has bullet
			if($data[0]['bulletNum'] == 0) {
				$this->_retValue = EC_NOT_ENOUGH_BULLET;
				$this->_retMsg = "userId:" . $this->_args['userId'];
				interface_log(ERROR, $this->_retValue, $this->_retMsg);
				return false;
			}
			//check if already in fight
			$this->_oTable->setTableName('cFight');
			$data = $this->_oTable->getObject(array('user1' => $this->_args['userId'], 'user2' => $this->_args['userId']), 1);
			if(count($data)) {
				$this->_retValue = EC_ALREADY_FIGHT;
				$this->_retMsg = "userId:" . $this->_args ['userId'];
				interface_log ( ERROR, $this->_retValue, $this->_retMsg );
				return false;
			}
			//check if alreay in waiting list
			$this->_oTable->setTableName('cWaitingUser');
			$data = $this->_oTable->getObject(array('userId' => $this->_args['userId']));
			if(empty($data)) {
				//insert into cWaitingUser
				$this->_oTable->addObject(array('userId' => $this->_args['userId']));	
			}
			$this->_responseText = MYZL_HINT_READY_SUC;
		}catch (DB_Exception $e){
			$errorNum = $this->_oTable->getErrorNum();
			$this->_retMsg = $this->_oTable->getErrorInfo().$e->getMessage();
			$this->_retValue = genRetCode($errorNum);
			interface_log(ERROR, $this->_retValue, $this->_retMsg);
			return false;
		}
		return true;
	}
}

?>
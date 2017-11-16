<?php
require_once dirname ( __FILE__ ) . '/common/Common.php';
class Shoot extends AbstractInterface {
	
	private $_args;
	private $_userInfo;
	private $_fightInfo;
	private $_oTable;
	private $_result;
	private $_fightId;
	
	public function verifyInput(&$args) {
		
		$this->_args = array(
		
			"userId" => $this->_fromUserName
		);
		return true;
	}
	
	public function initialize() {
		
		try {
			$this->_oTable = new SingleTableOperation ('cUser', 'MYZL');
		} catch ( Exception $e ) {
			$errorNum = $this->_oTable->getErrorNum ();
			$this->_retMsg = $this->_oTable->getErrorInfo () . $e->getMessage ();
			$this->_retValue = genRetCode ( $errorNum );
			interface_log ( ERROR, $this->_retValue, $this->_retMsg );
			return false;
		}
		return true;
	}
	
	public function prepareData() {
		try {
			$this->_oTable->setTableName ( 'cUser' );
			$data = $this->_oTable->getObject ( array('userId' => $this->_args['userId']));
			if (count ( $data ) == 0) {
				$this->_retValue = EC_RECORD_NOT_EXIST;
				$this->_retMsg = "userId:" . $this->_args ['userId'];
				interface_log ( ERROR, $this->_retValue, $this->_retMsg );
				return false;
			}
			$this->_userInfo = $data [0];
			
			$this->_oTable->setTableName ( 'cFight' );
			$this->_args ['userId'] = DbFactory::getInstance ('MYZL')->escape ( $this->_args ['userId'] );
			$data = $this->_oTable->getObject ( array ("_where" => " (user1='" . $this->_args ['userId'] . "' OR user2='" . $this->_args ['userId'] . "')" ) );
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
					$this->_retMsg = " userId:" . $this->_args ['userId'];
					interface_log ( ERROR, $this->_retValue, $this->_retMsg );
					return false;
				}
				if ($data [0] ['current'] != $this->_args ['userId']) {
					$this->_retValue = EC_NOT_THIS_USR_ORDER;
					$this->_retMsg = " userId:" . $this->_args ['userId'] . "current: " . $data [0] ['current'];
					interface_log ( ERROR, $this->_retValue, $this->_retMsg );
					return false;
				}
				if($data[0]['operation'] != SHOOT) {
					$this->_retValue = EC_STEP_OPERATION_NOT_MATCH;
					$this->_retMsg = " userId:" . $this->_args ['userId'];
					interface_log ( ERROR, $this->_retValue, $this->_retMsg );
					return false;
				}
				if($data[0]['count'] <= 0) {
					$this->_retValue = EC_COUNT_ERROR;
					$this->_retMsg = " userId:" . $this->_args ['userId'];
					interface_log ( ERROR, $this->_retValue, $this->_retMsg );
					return false;
				}
			}
			$this->_fightInfo = $data [0];
			$this->_fightId = $data[0]['fightId'];
		} catch ( Exception $e ) {
			$errorNum = $this->_oTable->getErrorNum ();
			$this->_retMsg = $this->_oTable->getErrorInfo () . $e->getMessage ();
			$this->_retValue = genRetCode ( $errorNum );
			interface_log ( ERROR, $this->_retValue, $this->_retMsg );
			return false;
		}
		return true;
	}
	
	public function process() {
		
		try {
			DbFactory::getInstance ('MYZL')->autoCommit ();
			
			//保存本次状态
			//$this->_fightInfo['lastOp'] = $this->_fromUserName . ',' . SHOOT . ',';
			$msgForOther = $this->_fightInfo['msgForOther'];
			$otherId = $this->_fightInfo['otherId'];
			if($otherId == $this->_fromUserName) {
				$this->_responseText .= $msgForOther;
				$this->_fightInfo['msgForOther'] = "";
				
			}
			$this->_fightInfo['otherId'] = ($this->_fromUserName == $this->_fightInfo['user1']) ? $this->_fightInfo['user2'] : $this->_fightInfo['user1'] ;
			if($this->_fightInfo['msgForOther'] == "") {
				$this->_fightInfo['msgForOther'] = "对方开枪，";
			}else {
				$this->_fightInfo['msgForOther'] .= ", 对方开枪，";
			}
			
			unset($this->_fightInfo['lastInfo']);
			$this->_fightInfo ['lastInfo'] = json_encode ( $this->_fightInfo );
			//计算结果
			$this->_result = $this->calculateResult ();
			interface_log(DEBUG, 0, var_export($this->_result, true));
			
			
			if ($this->_result ['out']) {
				$this->_responseText .= "子弹打出，";
				
				$this->_fightInfo['msgForOther'] .= "子弹打出，";
				if ($this->_fightInfo ['gameNumber'] == 2) {
					$this->_responseText .= "下半局结束，";
					$this->_fightInfo['msgForOther'] .= "下半局结束，";
					if($this->_result['dead']) {
						$this->_fightInfo ['loss2'] = $this->_args ['userId'];	
					} else {
						$this->_fightInfo ['loss2'] = -1;
					}
					$this->_fightInfo ['operation'] = SECOND_END;
					$this->_fightInfo ['operator'] = 0;
					$this->_fightInfo ['count'] == 0;
					//$this->_fightInfo ['current'] = $this->_fightInfo['first'];
				} else {
					$this->_responseText .= "上半局结束，";
					$this->_fightInfo['msgForOther'] .= "上半局结束，";
					if($this->_result['dead']) {
						$this->_fightInfo ['loss1'] = $this->_args ['userId'];
					} else {
						$this->_fightInfo ['loss1'] = -1;
					}
					$this->_fightInfo ['first'] = ($this->_fightInfo ['first'] == $this->_fightInfo ['user1']) ? $this->_fightInfo ['user2'] : $this->_fightInfo ['user1'];
					$this->_fightInfo ['operation'] = FIRST_END;
					$this->_fightInfo ['operator'] = 0;
					$this->_fightInfo ['count'] == 0;
					$this->_fightInfo ['current'] = $this->_fightInfo['first'];
				}
			} else {
				
				if($this->_result['magicEffect'] == CHXS) {
					$this->_responseText .= "本局游戏重新开始，";
					
					$this->_fightInfo['msgForOther'] .= "本局游戏重新开始，";
					$this->_fightInfo ['count'] = 0;
					if($this->_fightInfo['gameNumber'] == 1)
						$this->_fightInfo ['operation'] = START;
					else $this->_fightInfo ['operation'] = FIRST_END; 
					$this->_fightInfo ['operator'] = 0;
					$this->_fightInfo ['current'] = $this->_fightInfo['first'];
				} else {
					//set current
					$this->_responseText .= "子弹未打出，游戏继续";
					$this->_fightInfo['msgForOther'] .= "子弹未打出，游戏继续";
					
					$this->_fightInfo['current'] = ($this->_fightInfo['current'] == $this->_fightInfo['user1'] 
											? $this->_fightInfo['user2'] : $this->_fightInfo['user1']);
					$ret = $this->setNextOpAndUser ();
					if ($ret ['code']) {
						$this->_retValue = EC_STEP_ERROR;
						$this->_retMsg = "fightId:" . $this->_fightId . " step:" . $this->_fightInfo ['historyOp'];
						interface_log ( ERROR, $this->_retValue, $this->_retMsg );
						return false;
					}
					
					if($this->_result['magicEffect'] == SSZM) {
					}  else {
						$this->_fightInfo ['count'] --;	
					}
					
				} 
			}
		
			if ($this->_fightInfo ['historyOp'] == '') {
				$newHistoyOp = SHOOT . ',' . $this->_args ['userId'];
			} else {
				$newHistoyOp = $this->_fightInfo ['historyOp'] . "|" . SHOOT . ',' . $this->_args ['userId'];
			}
			$this->_fightInfo ['historyOp'] = $newHistoyOp;
			
			if($this->_result['out']) {
				$this->divideMoney();
			}
			
			//unset magic
			$this->_fightInfo['magic1'] = '';
			$this->_fightInfo['magic2'] = '';
			
			if($this->_fightInfo['operation'] == SECOND_END) {
				$this->_oTable->setTableName('cFightUncheck');
				$this->_oTable->addObject($this->_fightInfo);
				
				$this->_oTable->setTableName('cFight');
				$this->_oTable->delObject(array('fightId' => $this->_fightId));
				
				$this->_data = $this->_fightInfo;
			} else {
				unset ( $this->_fightInfo ['fightId'] );
				$this->_oTable->setTableName ( 'cFight' );
				$this->_oTable->updateObject ( $this->_fightInfo, array ('fightId' => $this->_fightId ) );
				$this->_data = $this->_fightInfo;	
			}
			
			
			DbFactory::getInstance ('MYZL')->tryCommit ();
			
			
		} catch ( DB_Exception $e ) {
			DbFactory::getInstance('MYZL')->rollback();
			$errorNum = $this->_oTable->getErrorNum ();
			$this->_retMsg = $this->_oTable->getErrorInfo () . $e->getMessage ();
			$this->_retValue = genRetCode ( $errorNum );
			interface_log ( ERROR, $this->_retValue, $this->_retMsg );
			return false;
		}
		return true;
	}
	
	private function divideMoney() {
		$this->_oTable->setTableName('cUser');
		if($this->_result['out']) {
			if($this->_result['dead']) {
				//give all
				$userId = ($this->_args['userId'] == $this->_fightInfo['user1']) ? $this->_fightInfo['user2'] : $this->_fightInfo['user1'];
				DbFactory::getInstance('MYZL')->update("update cUser set money=money + " . $this->_fightInfo['money'] . " where userId='" . $userId . "'");
				$this->_responseText .= "对方获得【" . $this->_fightInfo['money'] . "金币】";
				$this->_fightInfo['msgForOther'] .= "你获得【" . $this->_fightInfo['money'] . "金币】";
				//$this->_oTable->updateObject(array('money' => "money + " . $this->_fightInfo['money']), array('userId' => $userId));
			} else {
				//divide
				DbFactory::getInstance('MYZL')->update("update cUser set money=money + " . $this->_fightInfo['money'] / 2 . " where userId IN ('" . $this->_fightInfo['user1'] . "','" . $this->_fightInfo['user2'] . "')");
				//$this->_oTable->updateObject(array('money' => "money + " . $this->_fightInfo['money'] / 2), array('userId' => array($this->_fightInfo['user1'], $this->_fightInfo['user2'])));
				$this->_responseText .= "你获得【" . $this->_fightInfo['moeny'] / 2 . "金币】 ，对方获得【" . $this->_fightInfo['moeny'] / 2 . "金币】";
				$this->_fightInfo['msgForOther'] .= "你获得【" . $this->_fightInfo['moeny'] / 2 . "金币】 ，对方获得【" . $this->_fightInfo['moeny'] / 2 . "金币】";
			}
		}
	}
	
	
	private function setNextOpAndUser() {
	
		if($this->_fightInfo['historyOp'] == '') {
			return array('code' => 1, 'msg'  => 'bad step <empty>');
		} else {
			
			if($this->_fightInfo['current'] == $this->_fightInfo['first']) {
				$retOp = CHIP_IN ;
			} else {
				$retOp = PUT_MAGIC;	
			}
			
		}
		$operator = ($this->_args['userId'] == $this->_fightInfo['user1']) ? $this->_fightInfo['user2'] : $this->_fightInfo['user1'];
		$this->_fightInfo['operation'] = $retOp;
		$this->_fightInfo['operator'] = $operator;
	}
	
	private function calculateResult() {
		
		
		if($this->_fightInfo['current'] == $this->_fightInfo['user1']) {
			$magic1 = $this->_fightInfo ['magic1'];
			$magic2 = $this->_fightInfo ['magic2'];
		} else {
			$magic1 = $this->_fightInfo ['magic2'];
			$magic2 = $this->_fightInfo ['magic1'];
		}
		if($magic1 == '') {
			if($this->_responseText) {
				$this->_responseText .= '，你没有使用道具，';
			} else {
				$this->_responseText = '你没有使用道具，';
			}
			
			$this->_fightInfo['msgForOther'] .= '对方没有使用道具，';
		} else {
			if($this->_responseText) {
				$this->_responseText .= '，你使用了道具【' . $GLOBALS['constants']['magicName'][$magic1] . '】，';
			}else {
				$this->_responseText = '你使用了道具【' . $GLOBALS['constants']['magicName'][$magic1] . '】，';
			}
			
			$this->_fightInfo['msgForOther'] .= '对方使用了道具【' . $GLOBALS['constants']['magicName'][$magic1] . '】，';
		}
		if($magic2 == '') {
			
			$this->_responseText .= '对方没有使用道具，';
			$this->_fightInfo['msgForOther'] .= '你没有使用道具，';
		} else {
			$this->_responseText .= '对方使用了道具【' . $GLOBALS['constants']['magicName'][$magic2] . '】，';
			$this->_fightInfo['msgForOther'] .= '你使用了道具【' . $GLOBALS['constants']['magicName'][$magic2] . '】，';
		}
		$count = $this->_fightInfo ['count'];
		if ($magic1 == CHXS) {
			return array ('dead' => 0, 'out' => 0, 'magicEffect' => CHXS );
		} else if ($magic1 == SSZM) {
			return array ('dead' => 0, 'out' => 0, 'magicEffect' => SSZM );
		} else if ($magic1 == XSFT) {
			if ($magic2 != HDCX) {
				if ($count == 1) {
					return array ('dead' => 0, 'out' => 1 , 'magicEffect' => XSFT );
				} else {
					return array ('dead' => 0, 'out' => 0 );
				}
			} else {
				if ($count == 1) {
					return array ('dead' => 1, 'out' => 1, 'magicEffect' => HDCX );
				} else {
					return array ('dead' => 0,  'out' => 0);
				}
			}
		} else if ($magic1 == '') {
			if ($count == 1) {
				return array ('dead' => 1, 'out' => 1);
			} else {
				return array ('dead' => 0, 'out' => 0 );
			}
		}
	}
}

?>
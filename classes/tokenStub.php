<?php
require_once dirname(__FILE__) . '/../common/Common.php';

class tokenStub {
	public static function getToken($account, $force = false) {
		if(!($GLOBALS['APPID_APPSECRET'][$account]['appId'] && $GLOBALS['APPID_APPSECRET'][$account]['appSecret'])){
			interface_log(DEBUG, 0, "$account appId or appSecret not exists!");
			return false;
		}
		try {
			$STO = new SingleTableOperation();
			$STO->setTableName("ctoken");
			if($force == false) {
				$ret = $STO->getObject(array('appId' => $GLOBALS['APPID_APPSECRET'][$account]['appId']));
				interface_log(DEBUG, 0, "token data get from ctoken: " . json_encode($ret));
				if(count($ret) == 1) {
					$token = $ret[0]['token'];
					$expire = $ret[0]['expire'];
					$addTimestamp = $ret[0]['addTimestamp'];
					$current = time();
					if($addTimestamp + $expire - 30 > $current) {
						return $token;
					}	
				}
			}
			
			$para = array(
				"grant_type" => "client_credential",
				"appid" => $GLOBALS['APPID_APPSECRET'][$account]['appId'],
				"secret" => $GLOBALS['APPID_APPSECRET'][$account]['appSecret']
			);
			
			$url = WX_API_URL . "token";
			interface_log(DEBUG, 0, "url:" . $url . "  req data:" . json_encode($para));
			$ret = doCurlGetRequest($url, $para);
			interface_log(DEBUG, 0, "response data:" . $ret);
			
			$retData = json_decode($ret, true);
			if(!$retData || $retData['errcode']) {
				interface_log(ERROR, EC_OTHER, "requst wx to get token error");
				return false;
			}
			$token = $retData['access_token'];
			$expire = $retData['expires_in'];
			$STO->delObject(array('appId' => $GLOBALS['APPID_APPSECRET'][$account]['appId']));
			$STO->addObject(array('appId' => $GLOBALS['APPID_APPSECRET'][$account]['appId'], 'token' => $token, "expire" => $expire, "addTimestamp" => time()));
			
			return $token;
			
		} catch (DB_Exception $e) {
			interface_log(ERROR, EC_DB_OP_EXCEPTION, "operate ctoken error! msg:" . $e->getMessage());
			return false;
		}
		
		
	}
}

<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/tokenStub.php';
/**
 *  * 对数组和标量进行 urlencode 处理
 *   * 通常调用 wphp_json_encode()
 *    * 处理 json_encode 中文显示问题
 *     * @param array $data
 *      * @return string
 *       */
function wphp_urlencode($data) {
	if (is_array($data) || is_object($data)) {
		foreach ($data as $k => $v) {
			if (is_scalar($v)) {
				if (is_array($data)) {
					$data[$k] = urlencode($v);
				} else if (is_object($data)) {
					$data->$k = urlencode($v);
				}
			} else if (is_array($data)) {
				$data[$k] = wphp_urlencode($v); //递归调用该函数
			} else if (is_object($data)) {
				$data->$k = wphp_urlencode($v);
			}
		}
	}
	return $data;
}

/**
 *  * json 编码
 *   *
 *    * 解决中文经过 json_encode() 处理后显示不直观的情况
 *     * 如默认会将“中文”变成"\u4e2d\u6587"，不直观
 *      * 如无特殊需求，并不建议使用该函数，直接使用 json_encode 更好，省资源
 *       * json_encode() 的参数编码格式为 UTF-8 时方可正常工作
 *        *
 *         * @param array|object $data
 *          * @return array|object
 *           */
function ch_json_encode($data) {
	$ret = wphp_urlencode($data);
	$ret = json_encode($ret);
	return urldecode($ret);
}
class menuStub {
	public static function reqMenu($account, $interface, $data) {
		$token = tokenStub::getToken($account);
		//retry 3 times
		$retry = 3;
		while ($retry) {
			$retry --;
			if(false  === $token) {
				interface_log(DEBUG, EC_OTHER, "get token error!");
				return false;
			}
			$url = WX_API_URL . "$interface?access_token=" . $token;
			
			interface_log(DEBUG, 0, "req url:" . $url . "  req data:" . ch_json_encode($data));
			$ret = doCurlPostRequest($url, ch_json_encode($data));
			interface_log(DEBUG, 0, "response:" . $ret);
			
			$retData = json_decode($ret, true);
			if(!$retData || $retData['errcode']) {
				interface_log(DEBUG, EC_OTHER, "req create menu error");
				if($retData['errcode'] == 40014) {
					$token = tokenStub::getToken($account, true);
				}
			} else {
				return $retData;
			}
		}
		
		return false;
	}
	
	public static function create($account, $data) {
		$ret = menuStub::reqMenu($account, "menu/create", $data);
		if(false === $ret) {
			return false;
		}
		return true;
	}
	
	public static function get($account) {
		$ret = menuStub::reqMenu($account, "menu/get", array());
		if(false === $ret) {
			return false;
		}
		return $ret;
	} 
	
	public static function delete($account){
		$ret = menuStub::reqMenu($account, "menu/delete", array());
		if(false === $ret) {
			return false;
		}
		return true;
	}
}

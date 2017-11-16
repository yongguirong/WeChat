<?php
require_once dirname(__FILE__) . '/../common/Common.php';
require_once dirname(__FILE__) . '/../class/tokenStub.php';

interface_log(DEBUG, 0, "***start get token**");
$token = tokenStub::getToken('ABC');
if($token) {
	echo "token:" . $token ;
	interface_log(DEBUG, 0, "get token success! token:" . $token);
} else {
	interface_log(DEBUG, 0, "get token fail");
}
interface_log(DEBUG, 0, "***end get token***");

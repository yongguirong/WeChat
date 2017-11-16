<?php 
//require_once dirname(__FILE__).'/ErrorCode.php';
define('ROOT_PATH', dirname(__FILE__) . '/../');
define('DEFAULT_CHARSET', 'utf-8');
define('COMPONENT_VERSION', '1.0');
define('COMPONENT_NAME', 'wxmp');

//关闭NOTICE错误日志
error_reporting(E_ALL ^ E_NOTICE);

define('USERNAME_FINDFACE', 'gh_fd4633de8852');
define('USERNAME_MR', 'gh_a8b0ebbe91f5');
define('USERNAME_XIAONIU', "gh_a03f3ce19e35");
define('USERNAME_MYZL', "gh_f940fd3e7a41");

define('WX_API_URL', "https://api.weixin.qq.com/cgi-bin/");
$GLOBALS['APPID_APPSECRET'] = array(
	'MYZL' => array(
		'appId' => 'wx5dcde27cd32432456e9b0e52',
		'appSecret' =>'692034a222347a5fe9ccb89ae59aa5a8563b' 
	),
	'ABC' => array(
		'appId' => 'wx73474b3afd824d1b',
		'appSecret' =>'166fa0af2cbb96a67522ad84cac3b717' 
	),
	'FINDFACE' => array(
		'appId' => '',
		'appSecret' => ''
	),
	'MR' => array(
		'appId' => '',
		'appSecret' => ''
	)
);

define("WEIXIN_TOKEN", "xiaoniu");
define("HINT_NOT_IMPLEMEMT", "未实现");
define('HINT_TPL', "<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[%s]]></MsgType>
  <Content><![CDATA[%s]]></Content>
  <FuncFlag>0</FuncFlag>
</xml>
");

$GLOBALS['DB'] = array(
	'DB' => array(
		'HOST' => 'pacotestdb.mysql.rds.aliyuncs.com',
		'DBNAME' => 'findface_new',
		'USER' => 'pacozhong',
		'PASSWD' => '123456',
		'PORT' => 3306 
	),
	'DB_OLD' => array(
		'HOST' => 'pacotestdb.mysql.rds.aliyuncs.com',
		'DBNAME' => 'findface',
		'USER' => 'pacozhong',
		'PASSWD' => '123456',
		'PORT' => 3306 
	),
	'MR' => array(
		'HOST' => 'pacotestdb.mysql.rds.aliyuncs.com',
		'DBNAME' => 'mr',
		'USER' => 'pacozhong',
		'PASSWD' => '123456',
		'PORT' => 3306 
	),
	'xiaoniuDB' => array(
		'HOST' => '127.0.0.1',
		'DBNAME' => 'wx_xiaoniu_db',
		'USER' => 'root',
		'PASSWD' => 'hphewlett',
		'PORT' => 3306
	),
	'MYZL' => array(
		'HOST' => 'pacotestdb.mysql.rds.aliyuncs.com',
		'DBNAME' => 'myzl',
		'USER' => 'pacozhong',
		'PASSWD' => '123456',
		'PORT' => 3306
	)
);
/*
$GLOBALS['DB'] = array(
	'DB' => array(
		'HOST' => 'localhost',
		'DBNAME' => 'findface',
		'USER' => 'root',
		'PASSWD' => 'root',
		'PORT' => 3306 
	),
	'MR' => array(
		'HOST' => 'localhost',
		'DBNAME' => 'mr',
		'USER' => 'root',
		'PASSWD' => 'root',
		'PORT' => 3306 
	)
);
*/
/**config for meiri10futu**/
define('MR_HINT_HELLO', "***每日十幅内涵图
***meiri10futu
1.输入?获取下一张内涵图");
define('MR_HINT_INPUT', "***每日十幅内涵图
***meiri10futu
1.输入?获取下一张内涵图");
define('MR_HINT_NO_NEW_PIC', "你已经看完了所有的内涵图，请等待更新");
define('MR_HINT_LIMITED', "您是受限用户，一天只能看10幅内涵图。若要变成非受限用户:推荐好友添加本账号（meiri10futu），并让他发送以下验证码到本账号为您激活：");
define('MR_HINT_NO_QUOTA', "你的激活名额已经使用完，如需更多的名额，请联系微信号：pacozhong");
define('MR_HINT_ALREADY_ACTIVE', "该用户已经激活");
define('MR_HINT_ACTIVE_SUCC', "激活成功");
define('MR_HINT_INNER_ERROR', "内部错误");
define('MR_HINT_ACTIVE_SELF', "不能激活自己");

define('PIC_OF_DAY', 10);

define('SUCC_TPL_MR', "<xml>
 <ToUserName><![CDATA[%s]]></ToUserName>
 <FromUserName><![CDATA[%s]]></FromUserName>
 <CreateTime>%s</CreateTime>
 <MsgType><![CDATA[news]]></MsgType>
 <ArticleCount>1</ArticleCount>
 <Articles>
 <item>
 <Title><![CDATA[内涵图**序号:%d**]]></Title> 
 <Description><![CDATA[如果图片没有完全展示，轻触图片查看全图]]></Description>
 <PicUrl><![CDATA[%s]]></PicUrl>
 <Url><![CDATA[%s]]></Url>
 </item>
 </Articles>
 <FuncFlag>1</FuncFlag>
 </xml>");

define('URL_HEADER', 'http://42.96.142.129/mr_image/');
define('FF_URL_HEADER', 'http://42.96.142.129/ff_image/');



/**config for findface**/
define('API_KEY', '69727bbe247b28ee24f4d740213c9532963');
define('API_SECRET', 'skt2k655KSwE6gMDS2132145U_RBbqysIl_yLF');
define('FACE_URL', "https://api.faceplusplus.com/");
define('FACE_TIMEOUT', 5);
define('GROUP_NAME', 'findface');
define('SUCC_TPL_FINDFACE', "<xml>
 <ToUserName><![CDATA[%s]]></ToUserName>
 <FromUserName><![CDATA[%s]]></FromUserName>
 <CreateTime>%s</CreateTime>
 <MsgType><![CDATA[news]]></MsgType>
 <ArticleCount>1</ArticleCount>
 <Articles>
 <item>
 <Title><![CDATA[findface找到了！]]></Title> 
 <Description><![CDATA[如果照片没有完全展示，轻触图片查看全图]]></Description>
 <PicUrl><![CDATA[%s]]></PicUrl>
 <Url><![CDATA[%s]]></Url>
 </item>
 </Articles>
 <FuncFlag>1</FuncFlag>
 </xml>");
define('SUCC_TPL_DAFEIJI', "<xml>
 <ToUserName><![CDATA[%s]]></ToUserName>
 <FromUserName><![CDATA[%s]]></FromUserName>
 <CreateTime>%s</CreateTime>
 <MsgType><![CDATA[news]]></MsgType>
 <ArticleCount>1</ArticleCount>
 <Articles>
 <item>
 <Title><![CDATA[findface教你打飞机！]]></Title> 
 <Description><![CDATA[爱情就像打飞机，开启无敌模式，轻松突破一亿分，永远占据他/她的第一名！]]></Description>
 <PicUrl><![CDATA[http://mmsns.qpic.cn/mmsns/Azu3H59E5k9QFa4RZC2Mk5Spj4VY3lQPBW6G8knXbMfPLWI4zyPm6Q/0]]></PicUrl>
 <Url><![CDATA[http://42.96.142.129/dafeiji.html]]></Url>
 </item>
 </Articles>
 <FuncFlag>1</FuncFlag>
 </xml>");

/**
 * hints
 */
define('FF_HINT_HELLO', "请自拍一张您的正面大头照发给我们，我们将为您找到微信世界里和你最像的人。
请注意：自拍时不要佩戴眼镜并保持头部直立，否则我们不保证能完成任务。输入\"打飞机\"获取打飞机无敌教程！");
define('FF_HINT_INPUT_ERROR', "内部错误，请稍后再试。");
define('FF_HINT_TYPE_ERROR', "您发的不是照片。");
define('FF_HINT_FACE_ERROR', '内部错误，请稍后再试。');
define('FF_HINT_MULTIPLE_FACE', '请确保照片里只有您自己，否则我们无法确定要找和谁相似的脸。');
define('FF_HINT_NO_FACE', '未能在照片中检测到脸部信息。***请你自拍时保持头部直立，脸不要被眼镜或者其他杂物遮挡。');
define('FF_HINT_FACE_NO_CANDIDATE', '抱歉，在微信世界里还没有和您长得像的人。每秒有5个人加入微信，也许你要找的就是他们，请稍后再试。');
define('FF_HINT_INNER_ERROR', '内部错误，请稍后再试。');
define('FF_HINT_XIANGNILE', '我也想你了[呲牙]');

/**
	*  * myzl defines
	*   */

define("CHIP_IN", "CHIP_IN");
define("PUT_MAGIC", "PUT_MAGIC");
define("SHOOT", "SHOOT");
define("FIRST_END", "FIRST_END");
define("SECOND_END", "SECOND_END");
define("START", "START");


define("XSFT" , "XSFT");
define("HDCX" , "HDCX");
define("CHXS" , "CHXS");
define("SSZM" , "SSZM");
$GLOBALS['constants'] = array(
	"MAGIC_LIST" => array(XSFT, HDCX, CHXS, SSZM, ""),
	"stepName" => array(
		CHIP_IN =>  "下注",
		PUT_MAGIC => "使用道具",
		SHOOT => "开枪",
		FIRST_END =>"上半局结束",
		SECOND_END => "下半局结束",
		START => "开始游戏"
	),
	"magicName" => array(
		XSFT => "邪神附体",
		HDCX => "壶底抽薪",
		CHXS => "重获新生",
		SSZM => "死神之门"
	)
);


define('MYZL_HINT', "欢迎关注MYZL, 由于个人时间有限，游戏尚未完善，如有建议或者意见，可以联系微信pacozhong");
define('MYZL_HINT_ADDUSER_SUC', "添加用户成功");
define('MYZL_HINT_CHIPIN_SUC', "你下注【%d金币】，等待对方【%s】");
define('MYZL_HINT_PUTMAGIC_SUC', "道具【%s】已释放，等待对方【%s】");
define('MYZL_HINT_PUTMAGIC_SUC_NO', "你没有使用道具，等待对方【%s】");
define('MYZL_HINT_READY_SUC', "已加入等待队列");
define('MYZL_HINT_START_SUC', "成功开始%s半局游戏");
?>

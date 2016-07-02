<?php

/**
 * ECSHOP 中行支付第一次接受验证码响应页面
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: respond.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');

$boc_data = $msg['Signature']['Object']['Package']['SignedData']['Data'];
//$sql = "INSERT INTO".$GLOBALS['ecs']->table('boc_mb')." (user_id, msgcode)" .
//                  "VALUES ('$_SESSION[user_id]', '$boc_code')";
//$GLOBALS['db']->query($sql);
$msg = base64_decode($boc_data);
$msg = xml_to_array($msg);
$smarty->display('msg_response.dwt');
if($_REQUEST['step'] == 'msg_sendboc'){
	$CHECK_CODE = $_POST['code'];
}
$PLT_CODE = $msg['PERSONAL_ACTSYNC_RESPONSE']['PLT_CODE'];
$TRX_SERNO = $msg['PERSONAL_ACTSYNC_RESPONSE']['TRX_SERNO'];
$REQ_TIME = date('Y-m-d\TH:i:s',time());
$back_url = 'http://www.olalamall.com/respond.php?code=boc'
$url = "https://ebspay.bankofchina.com/ebcts/SendMqMsg.do";
$data = <<<str
<PERSONAL_CHECKCODE_REQUEST>
<PLT_CODE>{$PLT_CODE}</PLT_CODE>
<TRX_CODE>MB12</TRX_CODE>
<TRX_SERNO>{$TRX_SERNO}</TRX_SERNO>
<OTRX_SERNO>{$TRX_SERNO}</OTRX_SERNO>
<REQ_TIME>{$REQ_TIME}</REQ_TIME>
<CHECK_CODE>{$CHECK_CODE}</CHECK_CODE>
<SPT1>{$back_url}</SPT1>
</PERSONAL_CHECKCODE_REQUEST>
str;
request_by_curl($url,$data);

//assign_template();
//$position = assign_ur_here();
//$smarty->assign('page_title', $position['title']);   // 页面标题
//$smarty->assign('ur_here',    $position['ur_here']); // 当前位置
//$smarty->assign('page_title', $position['title']);   // 页面标题
//$smarty->assign('ur_here',    $position['ur_here']); // 当前位置
//$smarty->assign('helps',      get_shop_help());      // 网店帮助
//
//$smarty->assign('boc_code',    $boc_code);
//$smarty->assign('shop_url',   $ecs->url());
//
//$smarty->display('msg_response.dwt');
function request_by_curl($url, $xml_data) {
	if (!extension_loaded("curl")) {   
		trigger_error("对不起，请开启curl功能模块！", E_USER_ERROR);
	}
	$ch = curl_init ();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
	$response = curl_exec($ch);
	if(curl_errno($ch)){
    	print curl_error($ch);
	}
	curl_close($ch);
}
?>
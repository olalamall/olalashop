<?php
	/**
 * ECSHOP 中行支付公共响应页面
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
//require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_base.php');

/*接中行返回msg*/
$msg = $_REQUEST['msg'];
/*xml格式转为数组*/
$msg = xml_to_array($msg);
$boc_code = $msg['Signature']['Object']['Package']['EnvelopInfo']['MsgCode'];
file_put_contents("./log/log.txt", $boc_code);
/*根据msg_code断是否为用户第一次接入*/
if($boc_code == 'MB1101'){
	ecs_header("Location: msg_response.php\n");
    exit;
}

if($boc_code == 'MZ1101'){
	ecs_header("Location: http://123.56.78.18/respond.php?code=boc\n");
    exit;
}

/*xml转数组*/
function xml_to_array($xml){
	$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
	if(preg_match_all($reg, $xml, $matches)){
		$count = count($matches[0]);
		for($i = 0; $i < $count; $i++){
		$subxml= $matches[2][$i];
		$key = $matches[1][$i];
			if(preg_match( $reg, $subxml )){
				$arr[$key] = xml_to_array( $subxml );
			}else{
				$arr[$key] = $subxml;
			}
		}
	}
	return $arr;
}
?>
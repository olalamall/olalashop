<?php

/**
 * ECSHOP 用户备案信息
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: douqinghua $
 * $Id: flow.php 17218 2011-01-24 04:10:41Z douqinghua $
 */
 
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
include_once(ROOT_PATH . '/includes/cls_image.php');
$image = new cls_image();
$user_id = $_SESSION['user_id'];

//拼接图片地址
$imgDir = 'identityimg/'.$user_id;
//获得提交的数据
$realname = isset($_POST['realname']) ? $_POST['realname'] : '';
$identity = isset($_POST['identity']) ? $_POST['identity'] : '';

//判断用户是否登录
if($user_id > 0){
	//检索是否是正确的信息
	if(is_array(identityAPI($realname,$identity))){
		//检索是否能查到用户的备案
		if(match_refer($user_id)){//能检索到用户数据则为修改数据
			//如果没有上传图片
			if($_FILES['frontimg']['error'] == 4){
				
				//检索是否已经有图片,如果已经有图片则只改变身份证和姓名
				$matchimg = match_img($user_id,'frontimg');
				//调用之前的图片
				$res1 = $matchimg;
			}else{
				//上传图片方法
				$res1 = $image->upload_image($_FILES['frontimg'],$imgDir,'frontimg.jpg');
			}
			if($_FILES['contraryimg']['error'] == 4){
				//检索是否已经有图片,如果已经有图片则只改变身份证和姓名
				$matchimg = match_img($user_id,'contraryimg');
				//调用之前的图片
				$res2 = $matchimg;
			}else{
				//上传图片方法
				$res2 = $image->upload_image($_FILES['contraryimg'],$imgDir,'contraryimg.jpg');
			}
			$msg = $image->error_msg();
			//判断是否出错
			if(!$res1 || !$res2){
				show_message($msg);
			}
			
			//更新数据，操作数据库
			$sql = 'UPDATE ' . $ecs->table('user_info') .
			   ' SET realname="'.$realname.'",identity='.$identity.',frontimg="'.$res1.'",contraryimg="'.$res2.
			   '" WHERE user_id='.$user_id;
		
			$db->query($sql);
			
			show_message('更新成功');
			
		}else{//如果检索不到用户数据则为添加
			
			//检索判断是否填写全部内容
			if(empty($realname) || empty($identity) && ($_FILES['frontimg']['error'] == 4) && ($_FILES['contraryimg']['error'] == 4)){
				show_message('未填写完全');
			}
			
			$frontimg = $image->upload_image($_FILES['frontimg'],$imgDir,'frontimg.jpg');
			$contraryimg = $image->upload_image($_FILES['contraryimg'],$imgDir,'contraryimg.jpg');
			$msg = $image->error_msg();
			//判断是否出错
			if(!$frontimg || !$contraryimg){
				show_message($msg);
			}
			
			//操作数据库
			$sql = "INSERT INTO ". $GLOBALS['ecs']->table('user_info') ."(user_id,realname,identity,frontimg,contraryimg) VALUES(".
			$user_id.",'".$realname."','".$identity."','{$frontimg}','{$contraryimg}')";
			$GLOBALS['db']->query($sql);
			//成功
			ecs_header("Location: flow.php?step=checkout\n");
		}
	}
	show_message('身份证信息填写错误');
}

//正则匹配方法
function preg_mat($str){
	if(strlen($str) == 18){//是身份证
		$preg = '/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/i';
		
		if(preg_match($preg, $str)){
			return true;
		}
		
		return false;
	}
	return true;
}

function p($arr){
	echo '<pre>';
	print_r($arr);
}
//API接口调用方法(检索姓名与身份证号是否匹配)
function identityAPI($realname,$identity){
	//初始化接口
	$res = initAPI($identity);
	
	//返回API接口
	return $res;
}

//初始化API接口方法
function initAPI($identity){
	$url = "http://apis.juhe.cn/idcard/index";
	$params = array(
	      "cardno" => $identity,//身份证号码
	      "dtype" => "",//返回数据格式：json或xml,默认json
	      "key" => 'e8aea10bfc1c1d5b33ccb64bdd1ee67d',//你申请的key
	);
	$paramstring = http_build_query($params);
	$content = juhecurl($url,$paramstring);
	$result = json_decode($content,true);
	if($result){
	    if($result['error_code']=='0'){
	        return $result;
	    }else{
	        return $result['error_code'].":".$result['reason'];
	    }
	}else{
	    return "请求失败";
	}
}

/**
 * 检索是否填写过备案
 * @param int $user_id [用户id]
 */
function match_refer($user_id){
	$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('user_info') . ' where user_id=' . $user_id;

	$oldData = $GLOBALS['db']->getRow($sql);
	
	return $oldData;
}
 
/**
 * 请求接口返回内容
 * @param  string $url [请求的URL地址]
 * @param  string $params [请求的参数]
 * @param  int $ipost [是否采用POST形式]
 * @return  string
 */
function juhecurl($url,$params=false,$ispost=0){
    $httpInfo = array();
    $ch = curl_init();
 
    curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
    curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
    curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if( $ispost )
    {
        curl_setopt( $ch , CURLOPT_POST , true );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
        curl_setopt( $ch , CURLOPT_URL , $url );
    }
    else
    {
        if($params){
            curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
        }else{
            curl_setopt( $ch , CURLOPT_URL , $url);
        }
    }
    $response = curl_exec( $ch );
    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
    $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
    curl_close( $ch );
    return $response;
}


 /**
 * 请求接口返回内容
 * @param  string $user_id [请求的用户id]
 * @param  string $img 	   [请求的检索的字段]
 */
function match_img($user_id,$img){
	$sql = 'SELECT '.$img.' from '.$GLOBALS['ecs']->table('user_info').' WHERE user_id='.$user_id;
	$res = $GLOBALS['db']->getOne($sql);
	return $res;
}

?>
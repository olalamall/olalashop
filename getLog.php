<?php

/**
 * ECSHOP 接收物流信息生成EXCEL表
 * $author : junlinliu
*/
define('IN_ECS', true);
define('INIT_NO_SMARTY', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH.'/includes/lib_excel.php');



//接收post传递的数据
//判断是否是xml格式的数据
$data = file_get_contents('php://input');
if(strstr($data,'xml')){//是xml格式
	//把数据抓出来
	$data = simplexml_load_string($data);
	$data = json_encode($data);
	$data = json_decode($data,true);
	//重组数组
	$newArr = array();
	foreach($data as $key=>$value){
		foreach ($value as $k => $v) {
			$newArr['title'][] = $k;
			$newArr['content'][] = $v;
		}
	}
	create_excel($newArr,'EMSexcel/'.$newArr['content'][0].'.xls');
	
}else{
	//接收json数据
	$data = json_decode($data,true);
	//重组数组
	$newArr = array();
	foreach($data as $k=>$v){
		$newArr['title'][] = $k;
		$newArr['content'][] = $v;
	}
	//生成excel文件
	create_excel($newArr,'EMSexcel/'.$newArr['content'][0].'.xls');
}


	
function p($arr){
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

?>
<?php
require_once("java/Java.inc");  //注意运用路径
$system = new Java("java.lang.System"); //运用系统包
header("content-type:text/html; charset=utf-8");  
$s = new Java("java.lang.String", "我在php中执行java");
echo $s;
?>
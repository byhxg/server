<?php
//while (true) {
//
//}

$test = "hello worldfsdffsdfsdf";// gbk => 二进制方式
var_dump(strlen($test));
// var_dump($test);
$len = pack("N", strlen($test)); // 整个数据 => 转化为二进制数据
// $r = $len.$test;
var_dump($len);
var_dump(unpack("N", $len));

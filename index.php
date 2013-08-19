<?php
// 框架主入口
ini_set('display_errors',true);
error_reporting(E_ALL);

define('DEBUG',true);//打开debug，测试环境下能够查看错误
include 'ant/ant.php';

//启动框架

$ant = ant::getInstance();
antl::useAutoLoad();//开启自动加载
$ant->run();

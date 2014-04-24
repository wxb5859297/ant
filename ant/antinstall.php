<?php
/*
 * install
 * 标准文件夹安装
 * */
//INSTALL TAG
ini_set('display_errors', true);
error_reporting(E_ALL);

$root = dirname(dirname(__FILE__)) . '/';

$filename = 'antlock.txt';
if (file_exists($filename) && file_get_contents($filename) == '1') {
    die('the framework is install, please do not install int ,thank you!');
}

echo "create folder, the root is $root<br/>";
umask(0);
if (!file_exists($root . 'rs')) {
    if (!mkdir($root . 'rs', 0755)) {
        die($root . "rs : the dir can't created");
    }
}
if (!file_exists($root . 'request')) {
    if (!mkdir($root . 'request', 0755)) {
        die($root . "request : the dir can't created");
    }
}
if (!file_exists($root . 'view/html')) {
    if (!mkdir($root . 'view/html', 0755, true)) {
        die($root . "view/html : the dir can't created");
    }
}
if (!file_exists($root . 'view/css')) {
    if (!mkdir($root . 'view/css', 0755)) {
        die($root . "view/css : the dir can't created");
    }
}
if (!file_exists($root . 'view/js')) {
    if (!mkdir($root . 'view/js', 0755)) {
        die($root . "rs : the dir can't created");
    }
}

// createIndex()
$fn = $root . 'index.php';
if (!file_exists($fn)) {
    $fp = fopen($fn, 'w');
    fwrite($fp, '<?php
//start
include ("ant/ant.php");
$ant = new ant();
$ant->run();
?>
    ');
    fclose($fp);
}

$str = file_get_contents(__FILE__);
$str = preg_replace('/\/\/INSTALL TAG/', 'die("installed!");', $str);
file_put_contents(__FILE__, $str);

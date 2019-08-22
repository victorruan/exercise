<?php
// 如何启动？
// php -S 0.0.0.0:8090 /path/index.php
$domain = "http://mirror.azure.cn";
$url =  $domain.$_SERVER['REQUEST_URI'];
echo file_get_contents($url);
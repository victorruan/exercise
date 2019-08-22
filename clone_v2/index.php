<?php
$domain = "http://mirror.azure.cn";
$url =  $domain.$_SERVER['REQUEST_URI'];

$file_name = __DIR__."/cache/".md5($url);

if (file_exists($file_name)) {
    exit(file_get_contents($file_name));
}

$result = file_get_contents($url);

file_put_contents($file_name,$result);

exit($result);


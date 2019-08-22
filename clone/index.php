<?php
$domain = "http://mirror.azure.cn";
$url =  $domain.$_SERVER['REQUEST_URI'];
echo file_get_contents($url);
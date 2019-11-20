<?php
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.3','<=') || version_compare(PHP_VERSION,'7.1.0','>'))
{
    header("Content-type: text/html; charset=utf-8");  
    die('PHP 版本必须 5.3.4 至 7.0 !');
}
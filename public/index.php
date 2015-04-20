<?php

/**
 * Loading path constant
 */
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));

$start_boot_time = microtime(true);

/**
 * Get rewriting URL
 */
$url = null;
if(isset($_GET['url']))
{
    $url = $_GET['url'];
} 
$url = '/'.$url;

/**
 * Enable autoload
 */
require_once (ROOT . DS . 'library' . DS . 'autoload_composer.php');

/**
 * Bootstrap loading
 */
require_once (ROOT . DS . 'library' . DS . 'bootstrap.php');

<?php

/**
 * Loading path constant
 */
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
 

/**
 * Get rewriting URL
 */
$url = null;
if(isset($_GET['url']))
{
    $url = $_GET['url'];
} 
if(strlen($url)>0)
{
	if(substr($url,0,1)!="/")
	{
		$url = '/'.$url;
	}
}
else
{
	$url = '/';
}

/**
 * Enable autoload
 */
require_once ROOT . DS . 'plugins'. DS . 'autoload.php';

// start application
$app = new \GL\Core\Kernel\Application();
$app->handle($url);
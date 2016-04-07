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
// require Facade aliases
require_once ROOT . DS . 'library' . DS .'aliases.php';
// start application
$app = new \GL\Core\Kernel\Application();
// load Facades
\GL\Core\Facades\AliasLoader::getInstance($aliases)->register();
// Handle Url and render Response
$app->handle($url);
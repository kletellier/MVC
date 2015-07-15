<?php
/**
 * Loading path constant
 */
define('TEMPLATEPATH',ROOT . DS . 'app'.DS.'Application'.DS.'Views'); // Path to twig views
define('CONFIGDIR',ROOT.DS.'config'); // path to config directory
define('UPLOADPATH',ROOT . DS . 'uploads'); // Path to upload directory
define('PUBLICPATH',ROOT. DS . 'public'); // Path to public directory (directory open to the web)
define('TMPPATH',ROOT . DS . 'tmp'); // Temporary path (using for xls export)
define('CACHEPATH',ROOT . DS . 'cache'); // Cache path
define('SERVICEPATH',ROOT . DS . 'config'); // DI service definition directory  path
define('DICACHE',ROOT.DS.'cache'.DS.'DI'.DS.'Container'.DS.'ServiceContainer.php'); // DI container compiled file
define('MIGRATIONPATH',ROOT . DS . "app" . DS . "Migrations"); // Migration script path

/**
 * Bootstrap application , load config file, start Eloquent ORM,  parse Request
 */
require_once (ROOT . DS . 'library' . DS . 'config.php');
require_once (ROOT . DS . 'library' . DS . 'database.php');
require_once (ROOT . DS . 'library' . DS . 'shared.php');

<?php
/**
 * Loading path constant
 */
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('TWIGPATH',ROOT . DS . 'app'.DS.'Application'.DS.'Views'); // Path to twig views
define('CONFIGPATH',ROOT . DS . 'config'.DS.'config.yml'); // Path to config file
define('ROUTEPATH',ROOT . DS . 'config'.DS.'routes.yml'); // Path to route definition
define('MAILPATH',ROOT . DS . 'config'.DS.'mail.yml'); // Path to mail configuration
define('DATABASEPATH',ROOT . DS . 'config'.DS.'database.yml'); // Path to database configuration
define('UPLOADPATH',ROOT . DS . 'uploads'); // Path to upload directory
define('PUBLICPATH',ROOT. DS . 'public'); // Path to public directory (directory open to the web)
define('FPDF_FONTPATH',ROOT .DS . 'vendor' . DS . 'Fpdf' . DS .'font' .DS); // Font directory for FPDF
define('TMPPATH',ROOT . DS . 'tmp'); // Temporary path (using for xls export)
define('CACHEPATH',ROOT . DS . 'cache'); // Cache path
define('SERVICEPATH',ROOT . DS . 'config'); // DI service definition directory  path

require_once (ROOT . DS . 'library' . DS . 'autoload_composer.php');

/**
 * Bootstrap application , load config file, start Eloquent ORM,  parse Request
 */
require_once (ROOT . DS . 'library' . DS . 'config.php');
require_once (ROOT . DS . 'library' . DS . 'database.php');
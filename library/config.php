<?php

/**
 * Define application constant
 * DEVELOPMENT_ENVIRONMENT : boolean, to write error on output buffer or not
 * BASE_PATH : website base url
 * TWIG_CACHE : cache all twig templates for improving speed
 * AUTORELOADCACHE :recreate twig cache for each request
 */

use GL\Core\Config;

// loading configuration from config/config.yml
$loader = new Config('config');
$value = $loader->load();

define('DEVELOPMENT_ENVIRONMENT',$value['debug']);
define('BASE_PATH',$value['webpath']);
define('TEMPLATE_ENGINE',$value['template']['engine']);
define('TEMPLATE_CACHE',$value['template']['cache']);
define('AUTORELOADCACHE',$value['template']['alwaysreload']);
define('LOCALE',$value['locale']);
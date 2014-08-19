<?php

/**
 * Define application constant
 * DEVELOPMENT_ENVIRONMENT : boolean, to write error on output buffer or not
 * BASE_PATH : website base url
 * TWIG_CACHE : cache all twig templates for improving speed
 * AUTORELOADCACHE :recreate twig cache for each request
 */

use Symfony\Component\Yaml\Parser;

// loading configuration from config/config.yml
$yaml = new Parser();
$value = $yaml->parse(file_get_contents(CONFIGPATH));

define('DEVELOPMENT_ENVIRONMENT',$value['debug']);
define('BASE_PATH',$value['webpath']);
define('TWIG_CACHE',$value['twig']['cache']);
define('AUTORELOADCACHE',$value['twig']['alwaysreload']);
define('LOCALE',$value['locale']);
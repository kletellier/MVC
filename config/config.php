<?php

/**
 * Define application constant
 * DEVELOPMENT_ENVIRONMENT : boolean, to write error on output buffer or not
 * BASE_PATH : website base url
 * TWIG_CACHE : cache all twig templates for improving speed
 * AUTORELOADCACHE :recreate twig cache for each request
 */

define('DEVELOPMENT_ENVIRONMENT',true);
define('BASE_PATH','http://localhost/');
define('TWIG_CACHE',false);
define('AUTORELOADCACHE',true);
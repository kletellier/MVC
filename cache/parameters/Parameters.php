<?php

namespace Parameters;

class Parameters
{
    private $_parameters = array('config' => array('debug' => true, 'webpath' => '', 'template' => array('engine' => 'twig', 'cache' => false, 'alwaysreload' => true), 'locale' => 'en'), 'database' => array('default' => array('server' => '127.0.0.1', 'port' => 3306, 'user' => 'uid', 'password' => 'pwd', 'database' => 'dbname')), 'templates' => array('twig' => array('class' => '\\GL\\Core\\Twig\\TwigService'), 'blade' => array('class' => '\\GL\\Core\\Blade\\Blade5Service')), 'mail' => array('server' => 'smtp.acme.com', 'port' => 587, 'user' => 'user', 'password' => 'pwd', 'secure' => 1, 'encryption' => 'tls'), 'redis' => array('default' => array('server' => '127.0.0.1', 'port' => 6379, 'enable' => 0)), 'security' => array('security' => array('classes' => 'GL\\Core\\Security\\AuthenticationService', 'roles_table' => 'roles', 'users_table' => 'users', 'usersroles_table' => 'usersroles'), 'cookie' => array('token' => 'typeyoursecuritytokenhere', 'name' => 'REMEMBERME', 'duration' => 3600), 'session' => array('name' => 'kletellier')));
    public function getParameters()
    {
        return $this->_parameters;
    }
}
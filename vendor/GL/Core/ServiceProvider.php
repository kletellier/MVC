<?php

namespace GL\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;

class ServiceProvider
{

    /**
    * @var ContainerBuilder
    */
    private static $singleton;

     /**
    * @param bool $reset whether to forcibly rebuild the entire container
    * @return \Symfony\Component\DependencyInjection\ContainerBuilder
    */
    public static function GetDependencyContainer($controller = "",$reset = FALSE) {
    if ($reset || self::$singleton === NULL) {
    $c = new self();
    self::$singleton = $c->getContainer($controller);
    }
    return self::$singleton;
    }

    /**
     * Return dependency injection container
     * 
     * @params string $controller controller name 
     * 
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getContainer($controller)
    {
        $container = new ContainerBuilder();
        
        // Inject mailer
        $container->register('mailer', '\GL\Core\Mailer');
        // Inject Request
        $container->register('request', '\Symfony\Component\HttpFoundation\Request')
        ->setFactoryClass('\Symfony\Component\HttpFoundation\Request')
        ->setFactoryMethod('createFromGlobals');         
        // Inject Request helper
        $container->register('request_helper', '\GL\Core\RequestHelper')->addMethodCall('setRequest', array(new Reference('request')));
        // Inject Twig Service
        $container->register('twig','\GL\Core\TwigService');
        // Inject RouteCollection
        $container->register('routes', '\Symfony\Component\Routing\RouteCollection')
        ->setFactoryClass('\GL\Core\RouteProvider')
        ->setFactoryMethod('GetRouteCollection');   
        // Inject FPDF Wrapper
         $container->register('pdf', '\GL\Core\PDF');
        // Inject PHPExcel Wrapper
        $container->register('excel', '\GL\Core\Excel');
        // Inject Session
        $container->register('session','\Symfony\Component\HttpFoundation\Session\Session')->addMethodCall('start');
        // Inject Crsf verifier
        $container->register('crsf','\GL\Core\FormCrsf')->addArgument(new Reference('session'))->addArgument(new Reference('request'));
        // Inject translator service
        $container->register('translator','\GL\Core\Translator');
        // Inject Security Service
        $container->register('security','\GL\Core\SecurityService')->addArgument(new Reference('session'))->addArgument(new Reference('request'))->addMethodCall('autologin');
        // Inject DebugBar
        $container->register('debug','\GL\Core\KLDebugBar');

        // Inject services defined in config/services.yml
        $loader = new YamlFileLoader($container, new FileLocator(SERVICEPATH));
        $loader->load('services.yml');

        $container->compile();
        
        return $container;  
    }
}

<?php

namespace GL\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use GL\Core\Config;

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
    public static function GetDependencyContainer($reset = FALSE) {
    if ($reset || self::$singleton === NULL) {
    $c = new self();
    self::$singleton = $c->getContainer();
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
    public function getContainer()
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $container = $this->createContainer();
        }
        else
        {
            $file = DICACHE;
            if (file_exists($file)) 
            {     
                $container = new \DI\Container\ServiceContainer();
            } 
            else 
            {            
                $container = $this->createContainer();                 
                $dumper = new PhpDumper($container);
                file_put_contents($file, $dumper->dump(array('class'=>'ServiceContainer','namespace'=>'DI\Container')));
            }
        }        
        
        return $container;  
    }

    private function createContainer()
    {
        $container = new ContainerBuilder();

        // retrieve security class 
        $cfgsecu = new Config('security');
        $values = $cfgsecu->load();
        $class =   $values['security']['classes'];
        // test if class exist and implement interface
        if(!class_exists($class))
        {
            echo "security class : " . $class. " does not exist";
            die();
        }
        else
        {
            // test if class implement interface
            $classref = new \ReflectionClass($class);             
            if(!$classref->implementsInterface('\GL\Core\SecurityServiceInterface'))
            {
              echo "class ".$class." does not implement SecurityServiceInterface";
              die();
            }           
        }

        // Inject mailer
        $container->register('mailer', 'GL\Core\Mailer');
        // Inject Request
        $container->register('request', 'Symfony\Component\HttpFoundation\Request')->setFactory("Symfony\Component\HttpFoundation\Request::createFromGlobals");
        // Inject Request helper
        $container->register('request_helper', 'GL\Core\RequestHelper')->addMethodCall('setRequest', array(new Reference('request')));
        // Inject Twig Service
        $container->register('template','GL\Core\TemplateProvider');
        // Inject RouteCollection
        $container->register('routes', 'Symfony\Component\Routing\RouteCollection')->setFactory("GL\Core\RouteProvider::GetRouteCollection");          
        // Inject FPDF Wrapper
        $container->register('pdf', 'GL\Core\PDF');
        // Inject PHPExcel Wrapper
        $container->register('excel', 'GL\Core\Excel');
        // Inject Session
        $container->register('session','Symfony\Component\HttpFoundation\Session\Session')->addMethodCall('start');
        // Inject Crsf verifier
        $container->register('crsf','GL\Core\FormCrsf')->addArgument(new Reference('session'))->addArgument(new Reference('request'));
        // Inject translator service
        $container->register('translator','GL\Core\Translator');
        // Inject Security Service
        $container->register('security',$class)->addArgument(new Reference('session'))->addArgument(new Reference('request'))->addMethodCall('autologin');
        // Inject DebugBar
        $container->register('debug','GL\Core\KLDebugBar');
         // Inject Pdo Object
        $container->register('pdo', 'PDO')->setFactory("GL\Core\DbHelper::getPdo");         
        // Inject Config
        $container->register('config','GL\Core\Config');
        // Inject DbHelper
        $container->register('db','GL\Core\DbHelper');

        // Inject services defined in config/services.yml
        $loader = new YamlFileLoader($container, new FileLocator(SERVICEPATH));
        $loader->load('services.yml');

        $container->compile();

        return $container;
    }
}

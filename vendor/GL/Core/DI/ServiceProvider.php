<?php

namespace GL\Core\DI;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use GL\Core\Config\Config;
use Assert\Assertion;
use Assert\AssertionFailedException;
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
        $values = \Parameters::get('security');
        $class =   $values['security']['classes'];
        $session_name = $values['session']['name'];
        // test if class exist and implement interface
        try 
        {
            Assertion::ClassExists($class);
            Assertion::implementsInterface($class,'\GL\Core\Security\AuthenticationServiceInterface');   
        } 
        catch (AssertionFailedException $e) 
        {
            echo $e->getMessage();
            die();
        }          

        // Inject mailer
        $container->register('mailer', 'GL\Core\Tools\Mailer')->setPublic(true);
        // Inject Request
        $container->register('request', 'Symfony\Component\HttpFoundation\Request')->setFactory("Symfony\Component\HttpFoundation\Request::createFromGlobals")->setPublic(true);
        // Inject Request helper
        $container->register('request_helper', 'GL\Core\Helpers\RequestHelper')->addMethodCall('setRequest', array(new Reference('request')))->setPublic(true);
        // Inject Twig Service
        $container->register('template','GL\Core\Templating\TemplateProvider')->setPublic(true);
        // Inject RouteCollection
        $container->register('routes', 'Symfony\Component\Routing\RouteCollection')->setFactory("GL\Core\Routing\RouteProvider::GetRouteCollection")->setPublic(true);  
        if(class_exists("\PHPExcel"))
        {
            // Inject PHPExcel Wrapper
            $container->register('excel', 'GL\Core\Tools\Excel')->setPublic(true);
        }   
        if(class_exists("\TCPDF"))
        {
            // Inject FPDF Wrapper
            $container->register('pdf', 'GL\Core\Tools\PDF')->setPublic(true);
        }   
        // Inject Session
        $container->register('session','Symfony\Component\HttpFoundation\Session\Session')->addMethodCall('setName',array($session_name))->addMethodCall('start')->setPublic(true);
        // Inject Crsf verifier
        $container->register('crsf','GL\Core\Security\FormCrsf')->addArgument(new Reference('session'))->addArgument(new Reference('request'))->setPublic(true);
        // Inject translator service
        $container->register('translator','GL\Core\Config\Translator')->setPublic(true);
        // Inject Security Service
        $container->register('security',$class)->addArgument(new Reference('session'))->addArgument(new Reference('request'))->addMethodCall('autologin')->setPublic(true);
        // Inject DebugBar
        $container->register('debug','GL\Core\Debug\KLDebugBar')->setPublic(true);
         // Inject Pdo Object
        $container->register('pdo', 'PDO')->setFactory("GL\Core\Helpers\DbHelper::getPdo")->setPublic(true);         
        // Inject Config
        $container->register('config','GL\Core\Config\Config')->setPublic(true);
        // Inject DbHelper
        $container->register('db','GL\Core\Helpers\DbHelper')->setPublic(true);
        // Inject Redis
        $container->register('redis','GL\Core\Tools\Redis')->setPublic(true);
        // Inject Event Dispatcher
        $container->register('event','Symfony\Component\EventDispatcher\EventDispatcher')->setPublic(true);
        
        // Inject services defined in config/services.yml
        $loader = new YamlFileLoader($container, new FileLocator(SERVICEPATH));
        $loader->load('services.yml');

        $container->compile();
        
        return $container;
    }
}

<?php 

namespace GL\Core\Kernel;

use GL\Core\Kernel\Loader;
use GL\Core\Routing\RouteProvider;
use GL\Core\Controller\ControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Reference;
use GL\Core\Exception\NotFoundHttpException;
use GL\Core\Exception\MethodNotAllowedException;
use GL\Core\DI\ServiceProvider;
use GL\Core\Config\Config;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Symfony\Component\Routing\Loader\ClosureLoader;
use Symfony\Component\Stopwatch\Stopwatch;
use GL\Core\Controller\Filters;
use GL\Core\Controller\ResponseEvent;
use GL\Core\Security\SecurityEvent;
class Application 
{   
    protected $start_time;
    protected $container;
    protected $watch;
    protected $filters;
    protected $debug;

     private function getErrorResponse($code)
    {

        $action = "error";
        $cr_error = new ControllerResolver("error", $action, array("code" => $code),$this->container);
        return $cr_error->execute();      
    }

    private function getRouterInstance()
    {
        $parameters = \Parameters::get('router');
        $classes = "GL\Core\Routing\Router";
        $inst = null;
        if(isset($parameters["classes"]))
        {
            $classes = $parameters["classes"];
        }
        try
        {
            Assertion::classExists($classes);
            $inst = new $classes;
        } 
        catch (AssertionFailedException $e) 
        {
            echo "Routing classes " . $classes . " does not exist";
            die();
        }
        return $inst;
    }

    public function __construct()
    {   
        $this->watch = new Stopwatch();
        $this->watch->start('rendering');

        // initialize start time
        $this->start_time = microtime(true);
       
        // Initialize all parameters before parsing url
        Loader::Init();

        // get DI container
        if(DEVELOPMENT_ENVIRONMENT){$this->watch->start('enable_container');}
        $this->container = ServiceProvider::GetDependencyContainer(); 
        
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $event = $this->watch->stop('enable_container');
            $start_container = ( $event->getOrigin() + $event->getStartTime())/1000;
            $stop_container = ( $event->getOrigin() +$event->getEndTime())/1000;
            \Debug::addMeasure("Enable container", $start_container,$stop_container);
        }
        \Debug::startMeasure('load_filter','Load filters');
        // instantiate filters object
        $this->filters = new Filters();
        \Debug::stopMeasure('load_filter');

        // enable error reporting
        $this->setReporting();
    }

    /**
     * Enable/Disable error reporting to output buffer
     */
    private function setReporting() 
    {            
        error_reporting(E_ALL);
        ini_set('display_errors','Off');       
    }

    public function handle($url)
    {   
        \Debug::log("Url : " . $url);
        $route = "";
        \Debug::startMeasure('enable_error','Enable error system');
        $whoops = new \Whoops\Run;
        $handler = null;
        switch (DEVELOPMENT_ENVIRONMENT) {
            case true:
                $handler = new \Whoops\Handler\PrettyPageHandler;                
                break;
            
            case false:
                $handler = new \GL\Core\Debug\ErrorHandler;
                $handler->setContainer($this->container);
                break;
        }
     
        $whoops->pushHandler($handler);
        $whoops->register();
        \Debug::stopMeasure('enable_error');
        if(DEVELOPMENT_ENVIRONMENT)
        {        
            \Debug::log("PHP Version : " . phpversion());
            \Debug::log("OS Version : " . php_uname());
            $end_boot_time = microtime(true);
            $boot_time = $end_boot_time - $this->start_time;
            \Debug::log("Booting time : $boot_time sec");
            \Debug::addMeasure("Booting time",$this->start_time,$end_boot_time);
            $debug_boot_time = microtime(true);
            \Debug::addMeasure("Enable debug sytem",$end_boot_time,$debug_boot_time);
        }

        if(!DEVELOPMENT_ENVIRONMENT)
        {
            ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE  );        
        }
         
        $response = null;   

        // enable events system
        \GL\Core\Events\ListenerLoader::Init();

        // enable security system
        \Debug::startMeasure('security', 'Start security');
        $ss = $this->container->get('security');
        \Event::dispatch( SecurityEvent::SECURITY_STARTED, new SecurityEvent($ss->userLogged()));

        \Debug::stopMeasure('security');        
        \Debug::log("Security Session Id : " . $this->container->get('session')->get('session.id'));
        
        try 
        {    
            \Debug::startMeasure('routing', 'Routing'); 

            $router = $this->getRouterInstance();
            $ret = $router->route($url);
            if(!$ret)
            {
                throw new NotFoundHttpException();
            }
            $controller = $router->getController();
            $action = $router->getMethod();    
            $route = $router->getRoute();
            $parameters = $router->getArgs();

            // Fire Request Event
            $req = $this->container->get('request');
            \Event::dispatch( RequestEvent::NAME, new RequestEvent($req,$router));

            \Debug::stopMeasure('routing');  
            \Debug::startMeasure('resolving', 'Resolving controller');
            \Debug::log("Route : " . $route);  
                        
            $cr = new ControllerResolver($controller,$action,$parameters,$this->container);            
            \Debug::stopMeasure('resolving'); 
            \Debug::startMeasure('before', 'Execute before');                                   
            $this->filters->executeBefores($route); 
            \Debug::stopMeasure('before'); 
            \Debug::startMeasure('execute', 'Execute action');                        
            $response = $cr->execute(); 
            \Debug::stopMeasure('execute'); 
                               
        }
        catch(\GL\Core\Exception\HttpException $hex)
        {
            $sc = $hex->getStatusCode();
            $response = $this->getErrorResponse($sc);
        }
        catch(NotFoundHttpException $ex)
        {
            $response = $this->getErrorResponse(404);                 
        }  // catch all KLetellier Exception
        catch(\GL\Core\Exception\AccessDeniedHttpException $ad)
        {
            $response = $this->getErrorResponse(401);
        }
        catch(\GL\Core\Exception\AccessForbiddenHttpException $ad)
        {
            $response = $this->getErrorResponse(403);
        }
        catch(\GL\Core\Exception\NotFoundHttpException $nf)
        {
            $response = $this->getErrorResponse(404);
        }  
         catch(\GL\Core\Exception\MethodNotAllowedException $mna)
        {
            $response = $this->getErrorResponse(405);
        }  
        if ($response instanceof Response) {
            \Debug::log("HTTP code : " . $response->getStatusCode());            
            \Debug::startMeasure('filtering', 'Filtering response'); 
            $responseFiltered = \Event::dispatch( ResponseEvent::NAME, new ResponseEvent($response,$route))->getResponse();
            $this->watch->stop('rendering');
            $responseFiltered->send();             
        }     
        else
        {
            echo "Output must be an HttpFoundation Response object";
            die();
        }
    }
    
}
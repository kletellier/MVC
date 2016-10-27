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


class Application 
{   
    protected $start_time;
    protected $container;
    protected $watch;
    protected $filters;
    protected $debug;

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
            $this->debug = $this->container->get('debug');
        }
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $event = $this->watch->stop('enable_container');
            $start_container = ( $event->getOrigin() + $event->getStartTime())/1000;
            $stop_container = ( $event->getOrigin() +$event->getEndTime())/1000;
            $this->debug['time']->addMeasure("Enable container", $start_container,$stop_container);
        }
        $this->startMeasure('load_filter','Load filters');
        // instantiate filters object
        $this->filters = new Filters();
        $this->stopMeasure('load_filter');

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

    private  function startMeasure($id,$text)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->debug['time']->startMeasure($id, $text);
        }
    }

    private  function stopMeasure($id)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->debug['time']->stopMeasure($id);
        }
    }

    public function handle($url)
    {   
        $route = "";
        $this->startMeasure('enable_error','Enable error system');
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
        $this->stopMeasure('enable_error');
        if(DEVELOPMENT_ENVIRONMENT)
        {        
            $end_boot_time = microtime(true);
            $boot_time = $end_boot_time - $this->start_time;
            $this->debug['messages']->addMessage("Booting time : $boot_time sec");
            $this->debug['time']->addMeasure("Booting time",$this->start_time,$end_boot_time);
            $debug_boot_time = microtime(true);
            $this->debug['time']->addMeasure("Enable debug sytem",$end_boot_time,$debug_boot_time);
        }

        if(!DEVELOPMENT_ENVIRONMENT)
        {
            ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE  );        
        }
         
        $response = null;
       

        // enable security system
        $this->startMeasure('security', 'Start security');
        $ss = $this->container->get('security');
        $this->stopMeasure('security');        

        if(DEVELOPMENT_ENVIRONMENT)
        {               
           $this->debug["messages"]->addMessage("Security Session Id : " . $this->container->get('session')->get('session.id'));
        } 
        try 
        {    
            $this->startMeasure('routing', 'Routing'); 

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

            $this->stopMeasure('routing');  
            $this->startMeasure('resolving', 'Resolving controller');
           
            if(DEVELOPMENT_ENVIRONMENT)
            {
               $this->debug["messages"]->addMessage("Route : " . $route);                      
            } 

            $cr = new ControllerResolver($controller,$action,$parameters,$this->container);  
            $this->stopMeasure('resolving'); 
            $this->startMeasure('before', 'Execute before');                                   
            $this->filters->executeBefores($route); 
            $this->stopMeasure('before'); 
            $this->startMeasure('execute', 'Execute action');                        
            $response = $cr->execute(); 
            $this->stopMeasure('execute'); 
                               
        }
        catch(NotFoundHttpException $ex)
        {
            // return not found controller action
            $cr404 = new ControllerResolver("error", "error404", array(),$this->container);
            $response = $cr404->execute();            
        }  
        catch(MethodNotAllowedException $ema)   
        {
            // return methode not allowed controller action
            $cr405 = new ControllerResolver("error", "error405", array(),$this->container);
            $response = $cr405->execute();   
        }  

        $this->startMeasure('filtering', 'Filtering response');               
         
        if ($response instanceof Response) {
            // prepare response
            $filteredresponse =  $this->filters->filterResponse($response,$route);
            $event = $this->watch->stop('rendering'); 
            $filteredresponse->send();             
        }     
        else
        {
            echo "Output must be an HttpFoundation Response object";
            die();
        }
    }
    
}
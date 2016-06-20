<?php 

namespace GL\Core\Kernel;

use GL\Core\Kernel\Loader;
use GL\Core\Routing\RouteProvider;
use GL\Core\Controller\ControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route; 
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Reference;
use GL\Core\DI\ServiceProvider;
use GL\Core\Config\Config;
use Assert\Assertion;
use Symfony\Component\Routing\Loader\ClosureLoader;
use Symfony\Component\Stopwatch\Stopwatch;
use GL\Core\Controller\Filters;

class Application 
{   
    protected $start_time;
    protected $container;
    protected $watch;
    protected $filters;

    public function __construct()
    {   
        $this->watch = new Stopwatch();
        $this->watch->start('rendering');

        // initialize start time
        $this->start_time = microtime(true);
       
        // Initialize all parameters before parsing url
        Loader::InitPath();
        Loader::InitConfig();
        Loader::InitDatabase();
      
        // get DI container
        $this->container = ServiceProvider::GetDependencyContainer(); 

        // instantiate filters object
        $this->filters = new Filters();

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
            $this->container->get('debug')['time']->startMeasure($id, $text);
        }
    }

    private  function stopMeasure($id)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->container->get('debug')['time']->stopMeasure($id);
        }
    }

    public function handle($url)
    {   
        $route = "";
        $whoops = new \Whoops\Run;
        $handler = new \GL\Core\Debug\ErrorHandler;
        $handler->setContainer($this->container);
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $handler = new \Whoops\Handler\PrettyPageHandler;
        } 
        $whoops->pushHandler($handler);
        $whoops->register();

        if(DEVELOPMENT_ENVIRONMENT)
        {        
            $end_boot_time = microtime(true);
            $boot_time = $end_boot_time - $this->start_time;
            $this->container->get('debug')['messages']->addMessage("Booting time : $boot_time sec");
            $this->container->get('debug')['time']->addMeasure("Booting time",$this->start_time,$end_boot_time);
            $debug_boot_time = microtime(true);
            $this->container->get('debug')['time']->addMeasure("Enable debug sytem",$end_boot_time,$debug_boot_time);
        }

        if(!DEVELOPMENT_ENVIRONMENT)
        {
            ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE  );        
        }
        
        // enable routing context
        $this->startMeasure('initrouting', 'Init Routing');    
        $context = new RequestContext();    
        $context->fromRequest($this->container->get('request'));
        $response = null;
        $this->stopMeasure('initrouting');

        // enable security system
        $this->startMeasure('security', 'Start security');
        $ss = $this->container->get('security');
        $this->stopMeasure('security');        

        if(DEVELOPMENT_ENVIRONMENT)
        {
           $this->container->get('debug')['routes']->setRoutes($this->container->get('routes'));        
           $this->container->get('debug')["messages"]->addMessage("Security Session Id : " . $this->container->get('session')->get('session.id'));                      
        } 
        try 
        {    
            $this->startMeasure('routing', 'Routing'); 

            $closure = function () {
                return $this->container->get('routes');
            };

            $arrpar = array();
            if(!DEVELOPMENT_ENVIRONMENT)
            {
                $arrpar['cache_dir']  = ROUTECACHE;
            }
                         
            $router = new Router(new ClosureLoader(),
                $closure,
                $arrpar,  
                $context
            );
            $parameters = $router->match($url);
 
            $this->stopMeasure('routing');  
            $this->startMeasure('resolving', 'Resolving controller');
            $controller = $parameters['controller'];
            $action = $parameters['action'];    
            $route = $parameters["_route"];
            if(DEVELOPMENT_ENVIRONMENT)
            {
               $this->container->get('debug')["messages"]->addMessage("Route : " . $route);                      
            } 

            $cr = new ControllerResolver($controller,$action,$parameters);  
            $this->stopMeasure('resolving'); 
            $this->startMeasure('before', 'Execute before');                                   
            $this->filters->executeBefores($route); 
            $this->stopMeasure('before'); 
            $this->startMeasure('execute', 'Execute action');                        
            $response = $cr->execute(); 
            $this->stopMeasure('execute'); 
                               
        }
        catch(ResourceNotFoundException $ex)
        {
            // return not found controller action
            $cr404 = new ControllerResolver("error", "error404", array());
            $response = $cr404->execute();            
        }       

        $this->startMeasure('filtering', 'Filtering response'); 
         
        if ($response instanceof Response) {
            // prepare response
            $filteredresponse =  $this->filters->filterResponse($response,$route);
            // add rendering time comment if content type is html
            $event = $this->watch->stop('rendering');
            // $headers = $filteredresponse->headers;
            // $ct = $headers->get('Content-Type');
            
            // if(strtolower($ct)=="text/html")
            // {
            //     $html = $filteredresponse->getContent();
            //     $time = $event->getDuration();
            //     $html.= "<!-- generation time : " . $time . " ms -->";
            //     $filteredresponse->setContent($html);
            // }

            $filteredresponse->send();
             
        }     
        else
        {
            echo "Output must be an HttpFoundation Response object";
            die();
        }
    }
    
}
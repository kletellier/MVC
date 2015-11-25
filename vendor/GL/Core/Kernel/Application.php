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

class Application 
{   
    protected $start_time;
    protected $container;

    public function __construct()
    {   
        // initialize start time
        $this->start_time = microtime(true);
       
        // Initialize all parameters before parsing url
        Loader::InitPath();
        Loader::InitConfig();
        Loader::InitDatabase();

        // get DI container
        $this->container = ServiceProvider::GetDependencyContainer(); 

        // enable error reporting
        $this->setReporting();
    }

    /**
     * Enable/Disable error reporting to output buffer
     */
    private function setReporting() 
    {
        if (DEVELOPMENT_ENVIRONMENT == true) 
        {
        error_reporting(E_ALL & ~E_ERROR);
        ini_set('display_errors','On');
        } 
        else 
        {
        error_reporting(E_ALL & ~E_ERROR);
        ini_set('display_errors','Off');
        }
    }

   private function filterResponse(\Symfony\Component\HttpFoundation\Response $response)
    {
        $resp = $response;
        $ret = false;
        $cfg = new Config("functions");
        $fnArray = $cfg->load();
        if(isset($fnArray))
        {
            foreach ($fnArray as $key => $value) 
            {
                $route = $this->container->get('request_helper')->getCurrentRoute()['_route'];
                if($value["type"]=="filter")
                {
                    $bExecute = false;
                    // for each global function defined
                    $arrRoutes = (isset($value["routes"])) ? $value["routes"] : null;
                    $scope = (isset($value["scope"])) ? $value["scope"] : "all";
                    $class = $value["class"];
                    // test if class exist
                    Assertion::ClassExists($class);
                    Assertion::implementsInterface($class,'\GL\Core\Controller\FilterResponseInterface');                   
                    // test if route is allowed
                    if(isset($arrRoutes))
                    {
                        // function restricted to specified routes in arrRoutes
                        if(in_array($route, $arrRoutes))
                        {
                            $bExecute = true;
                        }
                    }
                    else
                    {
                        // function executed for all routes
                        $bExecute = true;
                    }

                    if($scope!="all" && $bExecute)                
                    {
                        $bExecute = false;
                        if($scope=="dev" && DEVELOPMENT_ENVIRONMENT)
                        {
                            $bExecute = true;
                        }
                        if($scope=="prod" && !DEVELOPMENT_ENVIRONMENT)
                        {
                            $bExecute = true;
                        }                                       
                    }
     
                    if($bExecute)
                    {
                        $exc = new $class($resp,$this->container);                    
                        $resp = $exc->execute();
                    }
                }  
            } 
        }
        return $resp;
    }

    private  function executeBefores($route)
    {
        $ret = false;
        $cfg = new Config("functions");
        $fnArray = $cfg->load();
        if(isset($fnArray))
        {
            $route = $this->container->get('request_helper')->getCurrentRoute()['_route'];           
            foreach ($fnArray as $key => $value) 
            {
                if($value["type"]=="before")
                {
                    $bExecute = false;
                    // for each global function defined
                    $arrRoutes = (isset($value["routes"])) ? $value["routes"] : null;
                    $scope = (isset($value["scope"])) ? $value["scope"] : "all";
                    $class = $value["class"];
                    // test if class exist and implements interface
                    Assertion::ClassExists($class);
                    Assertion::implementsInterface($class,'\GL\Core\Controller\BeforeFunctionInterface');    
                    // test if route is allowed
                    if(isset($arrRoutes))
                    {
                        // function restricted to specified routes in arrRoutes
                        if(in_array($route, $arrRoutes))
                        {
                            $bExecute = true;
                        }
                    }
                    else
                    {
                        // function executed for all routes
                        $bExecute = true;
                    }
                    if($scope!="all" && $bExecute)                
                    {
                        $bExecute = false;
                        if($scope=="dev" && DEVELOPMENT_ENVIRONMENT)
                        {
                            $bExecute = true;
                        }
                        if($scope=="prod" && !DEVELOPMENT_ENVIRONMENT)
                        {
                            $bExecute = true;
                        }                                       
                    }
                    if($bExecute)
                    {
                        $exc = new $class($this->container);
                        $ret = $exc->execute();
                    }
                }  
            } 
        }
          
        return $ret;
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

        if(DEVELOPMENT_ENVIRONMENT)
        {        
            $end_boot_time = microtime(true);
            $boot_time = $end_boot_time - $this->start_time;
            $this->container->get('debug')['messages']->addMessage("Booting time : $boot_time sec");
            $this->container->get('debug')['time']->addMeasure("Booting time",$this->start_time,$end_boot_time);
            $debug_boot_time = microtime(true);
            $this->container->get('debug')['time']->addMeasure("Enable debug sytem",$end_boot_time,$debug_boot_time);
        }

        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE  ); 
        
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
            if(DEVELOPMENT_ENVIRONMENT)
            {
               $this->container->get('debug')["messages"]->addMessage("Route : " . $parameters["_route"]);                      
            } 

            $cr = new ControllerResolver($controller,$action,$parameters);  
            $this->stopMeasure('resolving'); 
            $this->startMeasure('before', 'Execute before');                                   
            $this->executeBefores($parameters["_route"]); 
            $this->stopMeasure('before'); 
            $this->startMeasure('execute', 'Execute action');                        
            $response = $cr->execute(); 
            $this->stopMeasure('execute'); 
                               
        }
        catch(ResourceNotFoundException $ex)
        {
            ob_clean();
            // return not found controller action
            $cr404 = new ControllerResolver("error", "error404", array());
            $response = $cr404->execute();            
        }
        catch(Exception $e)
        {       
            ob_clean();
            // return error controller action
            $params = array('message'=>$e->getMessage(),'file'=>'','line'=>'','errors'=>array()); 
            $cr500 = new ControllerResolver("error", "error500", $params);
            $response = $cr500->execute();           
        }

        $this->startMeasure('filtering', 'Filtering response'); 
         
        if ($response instanceof Response) {
            // prepare response
            $this->filterResponse($response)->send();
        }     
        else
        {
            echo "Output must be an HttpFoundation Response object";
            die();
        }
    }
    
}
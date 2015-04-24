<?php 
  
use GL\Core\Routing\RouteProvider;
use GL\Core\Controller\ControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route; 
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Reference;
use GL\Core\DI\ServiceProvider;
use GL\Core\Config\Config;
/**
 * Enable/Disable error reporting to output buffer
 */
function setReporting() 
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

function filterResponse(\Symfony\Component\HttpFoundation\Response $response,\Symfony\Component\DependencyInjection\Container $container)
{
    $resp = $response;
    try {
        
        if(DEVELOPMENT_ENVIRONMENT)
        {
            AddDebugBar($response,$container);
        }
        
        
    } catch (Exception $e) {
        
    }
    return $resp;
}

function executeBefores(\Symfony\Component\DependencyInjection\Container $container,$route)
{
    $ret = false;
    $cfg = new Config("functions");
    $fnArray = $cfg->load();
 
    foreach ($fnArray as $key => $value) {

        if($value["type"]=="before")
        {
            $bExecute = false;
            // for each global function defined

            $arrRoutes = (isset($value["routes"])) ? $value["routes"] : null;
            $class = $value["class"];
            // test if class exist
            if(!class_exists($class))
            {
                echo "class " . $class . " does not exist";
                die();
            }
            // test if interface is implemented
             // test if class implement interface
            $classref = new \ReflectionClass($class);             
            if(!$classref->implementsInterface('\GL\Core\Controller\BeforeFunctionInterface'))
            {
              echo "class ".$class." does not implement BeforeFunctionInterface";
              die();
            }      
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
            if($bExecute)
            {
                $exc = new $class($container);
                $ret = $exc->execute();
            }
        }  
    }   
    return $ret;
}

function AddDebugBar(\Symfony\Component\HttpFoundation\Response $response,\Symfony\Component\DependencyInjection\Container $container)
{
    $resp = $response;
    try {
        
        $headers = $resp->headers;
        $ct = $headers->get('Content-Type');
        
        if(strtolower($ct)=="text/html")
        {
            $content = $resp->getContent();
            // add debugbar before <html> closure tag
            $debugbar = $container->get('debug');
            $renderer = $debugbar->getJavascriptRenderer();
            $url = BASE_PATH . "/dbg";
            $renderer->setBaseUrl($url);

            $dbghd = $renderer->renderHead();
            $dbgct = $renderer->render();
            $buf = "";
            // test if </head> if present
            if (strpos($content, '</head>') !== false)
            {
                $head = $dbghd."</head>";
                $content=str_replace("</head>", $head, $content);
            }
            else
            {
                $buf.=$dbghd;
            }
     
            $buf.=$dbgct."</html>";
            $content=str_replace("</html>", $buf, $content);

            $resp->setContent($content);
        }        
        
    } 
    catch (Exception $e) 
    {
        
    }
    return $resp;
}

function startMeasure($id,$text)
{
    if(DEVELOPMENT_ENVIRONMENT)
    {
        $container = ServiceProvider::GetDependencyContainer();
        $container->get('debug')['time']->startMeasure($id, $text);
    }
}

function stopMeasure($id)
{
    if(DEVELOPMENT_ENVIRONMENT)
    {
        $container = ServiceProvider::GetDependencyContainer();
        $container->get('debug')['time']->stopMeasure($id);
    }
}
/**
 * Routing function
 * 
 * Get route selected and execute controlle action
 * 
 * @param string $url URL extracted from url rewriting
 */
function HandleRequest($url)
{        
    $container = ServiceProvider::GetDependencyContainer();       
    startMeasure('getroutes', 'Get Routes');     
    $collection = $container->get('routes'); 
    stopMeasure('getroutes');
    startMeasure('initrouting', 'Init Routing');    
    $context = new RequestContext();    
    $context->fromRequest($container->get('request'));
    $matcher = new UrlMatcher($collection, $context);   
    $response = null;
    stopMeasure('initrouting');
    startMeasure('security', 'Start security');
    $ss = $container->get('security');
    stopMeasure('security');
    
    if(DEVELOPMENT_ENVIRONMENT)
    {
       $container->get('debug')['routes']->setRoutes($collection);        
       $container->get('debug')["messages"]->addMessage("Security Session Id : " . $container->get('session')->get('session.id'));                      
    } 
    try 
    {               
            startMeasure('routing', 'Routing');            
            $parameters = $matcher->match($url); 
            stopMeasure('routing');  
            startMeasure('resolving', 'Resolving controller');
            $controller = $parameters['controller'];
            $action = $parameters['action'];                    
            if(DEVELOPMENT_ENVIRONMENT)
            {
               $container->get('debug')["messages"]->addMessage("Route : " . $parameters["_route"]);                      
            } 
            $cr = new ControllerResolver($controller,$action,$parameters);  
            stopMeasure('resolving'); 
            startMeasure('before', 'Execute before');                                   
            executeBefores($container,$parameters["_route"]);  
            stopMeasure('before'); 
            startMeasure('execute', 'Execute action');                        
            $response = $cr->execute(); 
            stopMeasure('execute'); 
                           
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

    startMeasure('filtering', 'Filtering response'); 
     
    if ($response instanceof Response) {
        // prepare response
        filterResponse($response,$container)->send();
    }     
}  

ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE  ); 
setReporting();
if(DEVELOPMENT_ENVIRONMENT)
{        
    $container = ServiceProvider::GetDependencyContainer(); 
    $end_boot_time = microtime(true);
    $boot_time = $end_boot_time - $start_boot_time;
    $container->get('debug')['messages']->addMessage("Booting time : $boot_time sec");
    $container->get('debug')['time']->addMeasure("Booting time",$start_boot_time,$end_boot_time);
}
HandleRequest($url);  

?>
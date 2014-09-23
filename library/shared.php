<?php 
  
use GL\Core\RouteProvider;
use GL\Core\ControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route; 
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use GL\Core\ServiceProvider;

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

function filterResponse(\Symfony\Component\HttpFoundation\Response $response,\Symfony\Component\DependencyInjection\ContainerBuilder $container)
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

function AddDebugBar(\Symfony\Component\HttpFoundation\Response $response,\Symfony\Component\DependencyInjection\ContainerBuilder $container)
{
    $resp = $response;
    try {
        $content = $resp->getContent();
        $headers = $resp->headers;
        $ct = $headers->get('Content-Type');
        
        if(strtolower($ct)=="text/html")
        {
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
    $collection = RouteProvider::GetRouteCollection();  
    $context = new RequestContext();    
    $context->fromRequest($container->get('request'));
    $matcher = new UrlMatcher($collection, $context);   
    $response = null;

    try 
    {               
            $parameters = $matcher->match($url);        
            $controller = $parameters['controller'];
            $action = $parameters['action'];                    
            if(DEVELOPMENT_ENVIRONMENT)
            {
               $container->get('debug')["messages"]->addMessage("Route : " . $parameters["_route"]);                      
            } 
            $cr = new ControllerResolver($controller,$action,$parameters);    
                  
            $response = $cr->execute();     
            
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

    if ($response instanceof Response) {
        // prepare response
        filterResponse($response,$container)->send();
    }
}  

ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE  ); 
setReporting();
HandleRequest($url);  

?>
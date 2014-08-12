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


/**
 * Routing function
 * 
 * Get route selected and execute controlle action
 * 
 * @param string $url URL extracted from url rewriting
 */
function HandleRequest($url)
{        
	$collection = RouteProvider::GetRouteCollection();	
	$context = new RequestContext();
        $request = Request::createFromGlobals();
	$context->fromRequest($request);
	$matcher = new UrlMatcher($collection, $context);
        
	try 
	{				
            $parameters = $matcher->match($url); 		
            $controller = $parameters['controller'];
            $action = $parameters['action'];                    
		
            $cr = new ControllerResolver($controller,$action,$parameters);
            $cr->execute();		 
	}
	catch(ResourceNotFoundException $ex)
	{
            ob_clean();
            // return not found controller action
            $cr404 = new ControllerResolver("error", "error404", array());
            $cr404->execute();            
	}
	catch(Exception $e)
	{       
            ob_clean();
            // return error controller action
             $params = array('message'=>$e->getMessage(),'file'=>'','line'=>'','errors'=>array()); 
             $cr500 = new ControllerResolver("error", "error500", $params);
             $cr500->execute();           
	}
}  

ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE  ); 
setReporting();
HandleRequest($url);  

?>
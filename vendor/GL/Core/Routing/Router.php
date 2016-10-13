<?php

namespace GL\Core\Routing;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route; 
use Symfony\Component\Routing\Router as SymfonyRouter;
use Symfony\Component\Routing\Loader\ClosureLoader;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Router implements \GL\Core\Routing\RouterInterface
{
    private $_controller;
    private $_method;
    private $_route;
    private $_args;
    private $_request;
    private $_container;

    public function getRoute()
    {
        return $this->_route;
    }

    public function getController()
    {
        return $this->_controller;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function getArgs()
    {
        return $this->_args;
    }

    public function __construct(\Symfony\Component\HttpFoundation\Request $request=null)
    {
        $this->_route = "";
        $this->_controller="";
        $this->_method="";
        $this->_args=array();
    
        $this->_container = \GL\Core\DI\ServiceProvider::GetDependencyContainer();
        $req = ($request!=null) ? $request : $this->_container->get('request');
        $this->_request = $req;
    }

    public function setRequest(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->_request = $request;
    }

    public function route($url)
    {
        try 
        {
            $context = new RequestContext();    
            $context->fromRequest($this->_request);
            $closure = function () {
                    return $this->_container->get('routes');
                };

            $arrpar = array();
            if(!DEVELOPMENT_ENVIRONMENT)
            {
                $arrpar['cache_dir']  = ROUTECACHE;
            }
            else
            {
                $this->_container->get('debug')['routes']->setRoutes($this->_container->get('routes'));   
            }
                         
            $router = new SymfonyRouter(new ClosureLoader(),
                $closure,
                $arrpar,  
                $context
            );
            $parameters = $router->match($url);
            $this->_controller = $parameters["controller"];
            $this->_method = $parameters["action"];
            $this->_route = $parameters["_route"];
     
            unset($parameters["controller"]);
            unset($parameters["action"]);
            unset($parameters["_route"]);

            $this->_args = $parameters;
        } 
        catch (ResourceNotFoundException $e) 
        {
            $this->_router = "";
        }
        
        return ($this->_route!="");
    }    
}

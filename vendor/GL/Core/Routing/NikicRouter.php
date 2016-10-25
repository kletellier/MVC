<?php

namespace GL\Core\Routing; 

use FastRoute;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

class NikicRouter implements \GL\Core\Routing\RouterInterface
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
        $routes = $this->_container->get('routes');
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->_container->get('debug')["routes"]->setRoutes($routes);
        }
        $dispatcher = null;
        $callable = function(FastRoute\RouteCollector $r) use ($routes) 
        {
            foreach ($routes as $key => $value) {
                    $methodsall = $value->getMethods();
                    if(count($methodsall)==0)
                    {
                        $methodsall = array('GET','POST');
                    }
                    $chaine = $key . "::" . $value->getDefaults()['controller'] . "::" . $value->getDefaults()['action'];
                    $r->addRoute($methodsall,$value->getPath(),$chaine);
                }
        };
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $dispatcher = FastRoute\simpleDispatcher($callable);
        }
        else
        {
            $pathCache = CACHEPATH . DS . "route" . DS . "route.cache";
            $arrCache = array('cacheFile'=>$pathCache,'cacheDisabled'=>false);
            $dispatcher = FastRoute\cachedDispatcher($callable,$arrCache);
        }
               
        $method = $this->_request->getMethod();
        if (false !== $pos = strpos($url, '?')) 
        {
            $url = substr($url, 0, $pos);
        }
        $uri = rawurldecode($url);
        $routeInfo = $dispatcher->dispatch($method, $uri);
         
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                $this->_route = "";
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $this->_route = "";
                throw new \GL\Core\Exception\MethodNotAllowedException();
                break;
            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $params = explode("::",$handler);
                $this->_args = $vars;
                $this->_route = $params[0];
                $this->_controller = $params[1];
                $this->_method = $params[2];
                break;
        }
        
        return ($this->_route!="");
    }    
}

<?php

namespace GL\Core\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use GL\Core\Routing\RouteProvider;
use GL\Core\Config\Config;
use Assert\Assertion;
use Stringy\Stringy;
 

class Filters
{
    protected $_container;

    function __construct() 
    {         
        $this->_container = \GL\Core\DI\ServiceProvider::GetDependencyContainer();             
    }

    /**
     * Allow or not filtering execution
     * @param array $arrRoutes Array of allowed routes
     * @param string $route actual route
     * @param string $scope scope allowed (prod or dev)
     * @return boolean
     */
    private function IsAllowed($arrRoutes,$route,$scope)
    {
        $bExecute = false;
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
        return $bExecute;
    }

    /**
     * Global function called after action executing before rendering 
     * in template, using it for adding permanent variables to 
     * array passed to template
     * @param array $array 
     * @return array
     */
    public function GlobalFunction($array)
    {
        $ret = $array;
        // get actual route         
        $route_array  = $this->_container->get('request_helper')->getCurrentRoute();         
        $route = $route_array["_route"];

        $fnArray = \Functions::getAll();
        if(isset($fnArray))
        {
            foreach ($fnArray as $key => $value) 
            {
                if($value["type"]=="global")
                {                     
                    // for each global function defined
                    $arrRoutes = (isset($value["routes"])) ? $value["routes"] : null;
                    $scope = (isset($value["scope"])) ? $value["scope"] : "all";
                    $class = $value["class"];
                    // test if class exist and implements interface
                    Assertion::ClassExists($class);
                    Assertion::implementsInterface($class,'\GL\Core\Controller\GlobalFunctionInterface');                       
                    $bExecute = $this->IsAllowed($arrRoutes,$route,$scope);
                    if($bExecute)
                    {                    
                        $exc = new $class($ret,$this->_container);
                        $ret = $exc->execute();
                    }
                }     
            }    
        }
             
        return $ret;  
    }

    /**
     * Filtering response methode, executed just after template rendering before sending it to
     * browser
     * @param \Symfony\Component\HttpFoundation\Response $response 
     * @param actual route name
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function filterResponse(\Symfony\Component\HttpFoundation\Response $response,$route)
    {
        $resp = $response;
        $ret = false;
         
        $fnArray = \Functions::getAll();
        if(isset($fnArray))
        {
            foreach ($fnArray as $key => $value) 
            {
                 
                if($value["type"]=="filter")
                {                    
                    // for each global function defined
                    $arrRoutes = (isset($value["routes"])) ? $value["routes"] : null;
                    $scope = (isset($value["scope"])) ? $value["scope"] : "all";
                    $class = $value["class"];
                    // test if class exist
                    Assertion::ClassExists($class);
                    Assertion::implementsInterface($class,'\GL\Core\Controller\FilterResponseInterface');   
                    $bExecute = $this->IsAllowed($arrRoutes,$route,$scope);     
                    if($bExecute)
                    {
                        $exc = new $class($resp,$this->_container);                    
                        $resp = $exc->execute();
                    }
                }  
            } 
        }
        return $resp;
    }

    /**
     * Function execute before action execute
     * @param string $route actual route
     * @return boolean
     */
    public function executeBefores($route)
    {
        $ret = false;
        
        $fnArray = \Functions::getAll();
        if(isset($fnArray))
        {
            foreach ($fnArray as $key => $value) 
            {
                if($value["type"]=="before")
                {                   
                    // for each global function defined
                    $arrRoutes = (isset($value["routes"])) ? $value["routes"] : null;
                    $scope = (isset($value["scope"])) ? $value["scope"] : "all";
                    $class = $value["class"];
                    // test if class exist and implements interface
                    Assertion::ClassExists($class);
                    Assertion::implementsInterface($class,'\GL\Core\Controller\BeforeFunctionInterface');    
                    $bExecute = $this->IsAllowed($arrRoutes,$route,$scope);
                    if($bExecute)
                    {
                        $exc = new $class($this->_container);
                        $ret = $exc->execute();
                    }
                }  
            } 
        }
          
        return $ret;
    }
}
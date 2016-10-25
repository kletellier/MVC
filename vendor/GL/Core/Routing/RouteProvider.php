<?php

namespace GL\Core\Routing;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route; 
use GL\Core\Routing\RouteParser;
use GL\Core\Config\Config;
use GL\Core\Routing\RouteArray;

class RouteProvider
{
    
    /**
     * Extract route collection from config/routes.yml
     * 
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public static function GetRouteCollection()
    {
     
        // create Symfony routing route collection
        $collection = new RouteCollection();      
        $routearray = new RouteArray();
        $value = $routearray->getAll();

        // fill collection
        foreach($value as $name => $rte)
        {
                $rp = new RouteParser($rte,$name);
                if($rp->parse())
                { 
                    $defaults = $rp->getArrayParams();
                    $route = new Route($rp->getPattern(), $defaults);
                    $route->setMethods($rp->getMethods());
                    $collection->add($name,$route );  
                }        
        }
 
        return $collection;
    } 
    
}

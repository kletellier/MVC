<?php

namespace GL\Core;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route; 
use GL\Core\RouteParser;
use GL\Core\Config;

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
        // read routes.yml
        $config = new Config('routes');
        $value = $config->load();

        // fill collection
        foreach($value as $name => $rte)
        {
                $rp = new RouteParser($rte,$name);
                if($rp->parse())
                { 
                    $defaults = $rp->getArrayParams();    
                    $collection->add($name, new Route($rp->getPattern(), $defaults));  
                }		 
        }

        return $collection;
    } 
    
}

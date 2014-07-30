<?php

namespace GL\Core;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route; 
use GL\Core\RouteParser;
use Symfony\Component\Yaml\Parser;

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
        $yaml = new Parser();
        $value = $yaml->parse(file_get_contents(ROUTEPATH));

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

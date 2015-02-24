<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GL\Core;

use Symfony\Component\Routing\Route;

/**
 * Collects info about the current request
 */
class RouteDataCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable
{
    protected $_routes = null;

    public function setRoutes($routes)
    {
        $this->_routes = $routes;
    }

    public function collect()
    {
        $data = array(); 

         foreach ($this->_routes as $key => $value) {
            $defaults = $value->getDefaults();
            $path = $value->getPath();           
            $data[$key] = $path . "   " . $this->getDataFormatter()->formatVar($defaults);
         }
 

        return $data;
    }

    public function getName()
    {
        return 'routes';
    }

    public function getWidgets()
    {
        return array(
            "routes" => array(
                "icon" => "tags",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "routes",
                "default" => "{}"
            )
        );
    }
}

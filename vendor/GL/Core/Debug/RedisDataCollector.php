<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GL\Core\Debug;
 
/**
 * Collects info about redis commands
 */
class RedisDataCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable
{
   
    public function collect()
    {
        $redis = \GL\Core\DI\ServiceProvider::GetDependencyContainer()->get('redis');
        $commands = $redis->getCommandsHistory();

        $data = array(); 
        $i = 1;

         foreach ($commands as $value) {
                   
            $data[$i] = "Method " . strtoupper($value['command']) . "  :: Parameters " . $this->getDataFormatter()->formatVar($value['parameters']);
            $i++;
         }
 

        return $data;
    }

    public function getName()
    {
        return 'redis';
    }

    public function getWidgets()
    {
        return array(
            "redis" => array(
                "icon" => "tags",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "redis",
                "default" => "{}"
            )
        );
    }
}

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

use DebugBar\DebugBar;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\DataCollector\PDO\PDOCollector;
use PDO;
use Illuminate\Database\Capsule\Manager as Capsule;
use GL\Core\Debug\RouteDataCollector;

/**
 * Debug bar subclass which adds all included collectors
 */
class KLDebugBar extends DebugBar
{
    public function __construct()
    {
        $this->addCollector(new MessagesCollector());
        $this->addCollector(new RequestDataCollector());
        $this->addCollector(new TimeDataCollector());
        $this->addCollector(new RouteDataCollector());
        $this->addCollector(new ExceptionsCollector());
        
        try 
        {
            $conn= Capsule::connection("default");
            if($conn!=null)
            {               
                $db = $conn->getPdo();               
                $pdo = new TraceablePDO($db);
                $this->addCollector(new PDOCollector($pdo));                               
            }           
        } 
        catch(\Illuminate\Contracts\Container\BindingResolutionException $ex)
        {
            $this['exceptions']->addException($ex);
        }
        catch (\PDOException $e) 
        {
            $this['exceptions']->addException($e);            
        } 
       
    }
}

<?php

namespace GL\Core\Debug;

class Debugger
{
    private $container;
    private $debug;

    public function __construct()
    {
        $this->debug = null;
        $this->container = null;
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->container = \GL\Core\DI\ServiceProvider::GetDependencyContainer();
            $this->debug = $this->container->get('debug');
        }
    }

    public function log($message)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->debug['messages']->addMessage($message);
        }
    }       

    public function addMeasure($text, $start_time,$stop_time)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->debug['time']->addMeasure($text, $start_time,$stop_time);
        }
    }

    public  function startMeasure($key,$text)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->debug['time']->startMeasure($key, $text);
        }
    }

    public  function stopMeasure($key)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->debug['time']->stopMeasure($key);
        }
    }

    public function addRoutes($routes)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->debug["routes"]->setRoutes($routes);
        }

    }

    public function addException($exception)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->debug["exceptions"]->addException($exception);
        }

    }

    public function hasCollector($collector)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            return $this->debug->hasCollector($collector);
        }
        else
        {
            return true;
        }
    }

    public function addCollector($collector)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->debug->addCollector($collector);
        }
    }
}
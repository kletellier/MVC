<?php

namespace GL\Core\Events;
 
use GL\Core\Events\EventsLoader;
use Assert\Assertion;
use Assert\AssertionFailedException;

class ListenerLoader
{
 
    public static function Init()
    {
        $dispatcher = \GL\Core\DI\ServiceProvider::GetDependencyContainer()->get('event');
        $loader = new EventsLoader();
        $events = $loader->getAll();
        foreach ($events as $key => $value) 
        {
            Assertion::ClassExists($value["listener"]);
            $tmp = new $value["listener"];           
            $method = $value["method"];
            $event = $value["event"];
            $dispatcher->addListener($event, array($tmp, $method));
        }
    }         
}

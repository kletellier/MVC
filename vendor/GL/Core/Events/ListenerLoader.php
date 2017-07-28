<?php

namespace GL\Core\Events;
 
use GL\Core\Events\EventsLoader;
use Assert\Assertion;
use Assert\AssertionFailedException;

class ListenerLoader
{
 
    public static function Init()
    {
        $loader = new EventsLoader();
        $events = $loader->getAll();
        foreach ($events as $key => $value) 
        {
            Assertion::ClassExists($value["listener"]);
            $tmp = new $value["listener"];           
            $method = $value["method"];
            $event = $value["event"];
            \Event::addListener($event, array($tmp, $method));
        }
    }         
}

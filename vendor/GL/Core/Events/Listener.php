<?php 
namespace GL\Core\Events;
 
use GL\Core\Events\TestEvent;

class Listener
{   
    public function onTested(TestEvent $event)
    {
      	$message = $event->getMessage();
        \Debug::log($message); 
    }
}


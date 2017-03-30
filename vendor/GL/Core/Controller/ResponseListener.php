<?php 
namespace GL\Core\Controller;

use Symfony\Component\EventDispatcher\Event;
use GL\Core\Controller\ResponseEvent;
use GL\Core\Controller\Filters;

class ResponseListener
{
    
 
    public function onResponseCreated(ResponseEvent $event)
    {
       
        $resp = $event->getResponse();
        $route = $event->getRoute();
        $filters = new Filters();
        $filteredresponse = $filters->filterResponse($resp,$route); 
        $event->setResponse($filteredresponse);
        
        return $event;
    }
}


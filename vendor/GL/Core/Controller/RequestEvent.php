<?php 

namespace GL\Core\Controller;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use GL\Core\Routing\RouterInterface;

class RequestEvent extends Event
{
    const NAME = 'request.requested';

    protected $request; 
    protected $router;

    public function __construct(Request $request,RouterInterface $router)
    {
        $this->request = $request;
        $this->router = $router;
    }
     
    public function getRequest()
    {
        return $this->request;
    }

    public function getRouter()
    {
        return $this->router;
    }
     
}
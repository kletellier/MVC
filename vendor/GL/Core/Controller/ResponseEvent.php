<?php 

namespace GL\Core\Controller;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;


class ResponseEvent extends Event
{
    const NAME = 'response.created';

    protected $response;
    protected $route;

    public function __construct(Response $response,$route)
    {
        $this->response = $response;
        $this->route = $route;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}
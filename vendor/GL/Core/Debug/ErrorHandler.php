<?php
 

namespace GL\Core\Debug;

use Whoops\Exception\Formatter;


class ErrorHandler extends \Whoops\Handler\Handler
{    
    protected $container;
    
    public function setContainer($container)
    {
        $this->container = $container;
    }
    
    public function handle()
    {    
        $cr500 = new \GL\Core\Controller\ControllerResolver("error", "error", array("code"=>500) ,$this->container);
        $response = $cr500->execute();
        echo $response->getContent(); 
        die();
    }
}

<?php
 

namespace GL\Core\Debug;

use Whoops\Exception\Formatter;


class ErrorHandler extends \Whoops\Handler\Handler
{    
    
    public function handle()
    {
        $container = \GL\Core\DI\ServiceProvider::GetDependencyContainer();

        $message = $container->get('translator')->translate('error.oops',"Oops fatal error happens...");
        $params = array('message'=>$message,'file'=>'','line'=>'','errors'=>array()); 
        $cr500 = new \GL\Core\Controller\ControllerResolver("error", "error500", $params);

        $response = $cr500->execute();      
        echo $response->getContent(); 
        die();
    }
}

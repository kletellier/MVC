<?php 
namespace GL\Core\Controller;   
 

interface ControllerErrorInterface 
{ 	
    public function error500($message,$file,$line,$errors);

    public function error405();

    public function error404();

    public function error401();

    public function error403();
    
}
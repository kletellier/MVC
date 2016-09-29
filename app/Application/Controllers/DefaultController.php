<?php 
namespace Application\Controllers;

use GL\Core\Controller\Controller as Controller;

class DefaultController extends Controller
{
    public function hello($name)
    {                              
        $text = "Hello " .$name . " !";
        $date = new \DateTime();
        return $this->render('index',array('date'=>$date,'text'=>$text));   
    }
}

<?php 
namespace Application\Controllers;

use GL\Core\Controller\Controller as Controller;
use GL\Core\Events\TestEvent;

class DefaultController extends Controller
{
    public function hello($name)
    {                              
        $text = "Hello " .$name . " !";
        $date = new \DateTime();
        \Event::dispatch( TestEvent::NAME, new TestEvent("Unix Timestamp : " . $date->format('U')));
        return $this->render('index',array('date'=>$date,'text'=>$text));   
    }
}

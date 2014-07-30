<?php 
namespace Application\Controllers;

use GL\Core\Controller as Controller;

class DefaultController extends Controller
{
	public function hello($name)
	{                              
		$text = "Hello " .$name . " !";
		$this->render('index.html.twig',array('text'=>$text));	
	}
	
	public function testdb()
	{
		$test = \Application\Models\Test::all();
		$this->renderJSON($test);
	
	}
        
        
}

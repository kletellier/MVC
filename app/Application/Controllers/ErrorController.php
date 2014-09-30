<?php namespace Application\Controllers; use GL\Core\Controller as Controller;use Symfony\Component\HttpFoundation\Request as Request;  class ErrorController extends Controller{    public function error500($message,$file,$line,$errors)    {            return $this->renderTwig('500.html.twig',array('message'=>$message,'file'=>$file,'line'=>$line,'errors'=>$errors),500);		    }	        public function error404()    {        return $this->renderTwig('404.html.twig',array(),404);	    }        public function error403()    {        return $this->renderTwig('403.html.twig',array(),403);    }    private function renderTwig($tpl,$params,$code=200)    {        $tw = new \GL\Core\TwigService();        $html = $tw->render($tpl,$params,\GL\Core\ServiceProvider::GetDependencyContainer(),$this->_controller);        $response = new \Symfony\Component\HttpFoundation\Response($html, $code, array('Content-Type' => 'text/html'));                 return $response;    }}
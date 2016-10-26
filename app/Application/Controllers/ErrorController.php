<?php namespace Application\Controllers; use GL\Core\Controller\Controller as Controller;use Symfony\Component\HttpFoundation\Request as Request;  class ErrorController extends Controller implements \GL\Core\Controller\ControllerErrorInterface{    public function error500($message,$file,$line,$errors)    {            return $this->renderHtml('500',500);            }    public function error405()    {        return $this->renderHtml('405',405);        }           public function error404()    {        return $this->renderHtml('404',404);        }    public function error401()    {        return $this->renderHtml('401',401);    }        public function error403()    {        return $this->renderHtml('403',403);    }    private function renderHtml($tpl,$code=200)    {        $path = TEMPLATEPATH . DS . "Error" . DS . $tpl . ".html";                 $html = file_get_contents($path);        return new \Symfony\Component\HttpFoundation\Response($html, $code, array('Content-Type' => 'text/html'));     }}
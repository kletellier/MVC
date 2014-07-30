<?php

namespace GL\Core;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route; 

class Controller extends \Symfony\Component\DependencyInjection\ContainerAware
{
    protected $_controller;
    protected $_action;	 
    
    /**
     * Controller constructor
     * 
     * @param String $controller controller name
     * @param String $action action to execute
     */
    function __construct($controller, $action) 
    {
        $this->_controller = ucfirst($controller);
        $this->_action = $action;
    }
    
    /**
     * Function to get service from container
     * 
     * @param String $dependency
     * @return service
     */
    function get($dependency)
    {
        $ret = null;
        try
        {
            $ret = $this->container->get($dependency);
        } catch (Exception $ex) {
            $ret = null;
        }
        return $ret;
    }
    
    /**
     * Function to test if a service exist in the container
     * 
     * @param type $id
     * @return type
     */
    public function has($id)
    {
        return $this->container->has($id);
    }
    
    /**
     * Return the Request object stored in container
     * 
     * @return /Symfony/Component/HttpFoundation/Request
     */
    public function getRequest()
    {
        return $this->container->get('request');
    }    
    
    /**
     * Get all application routes in a RouteCollection
     * 
     * @return Symfony\Component\Routing\RouteCollection List all routes of application
     */
    public function getRoutes()
    {
        return $this->container->get('routes');
    }
         
    /**
     * Function render Html 
     * 
     * @param String $text Html to send to output Response
     * @param Integer $status Http Status
     * @param Array $headers Http-Headers in key value type
     */
    function renderText($text,$status = 200, $headers = array('Content-Type' => 'text/html'))
    {
        $response = new Response($text, $status, $headers);        
        $response->send();
    }

    /**
     * 
     * Function render Response with Twig template parsing 
     * 
     * @param string $template Twig template to parse
     * @param array $params Twig parameters array
     * @param integer $status Http Status
     * @param array $headers Http-Headers in key value type
     */
    function render($template,$params, $status = 200, $headers = array('Content-Type' => 'text/html') )
    {  
        $buf = $this->get('twig')->render($template,$params);
        //$buf = $this->getTwigEnvironment()->render($template,$params);
        $response = new Response($buf, $status, $headers);
        $response->send();
    }

    /**
     * Function render html text with Twig template parsing 
     * 
     * @param string $template Twig template to parse
     * @param array $params Twig parameters array
     * @return string Html string buffer
     */
    function renderHtmlTemplate($template,$params)
    {         
        return  $this->get('twig')->render($template,$params);
    } 
    
    /**
     * Return a Json Response
     * 
     * @param object $var object to be serialized in JSON
     */
    function renderJSON($var)
    {
        $response = new Response(json_encode($var));
        $response->headers->set('Content-Type', 'application/json');
        $response->send();
    }
    
    /**
     * Redirect with 302 response
     * 
     * @param string $routename route name defined in routes.yml
     * @param array $params parameters needed for the route
     */
    function redirect($routename,$params = array())
    {
        $url = "";
       
        try 
        {
             // get Symfony\Component\Routing\RouteCollection
            $rc = $this->get('routes');
            $route = $rc->get($routename);
            if($route!=null)
            {
                $pattern = $route->getPattern();
                $url = \GL\Core\Utils::url($pattern);
                // replace parameters by provided array
                foreach($params as $key => $value)
                {
                    $str = '{'.$key.'}';
                    $url = str_replace($str, $value, $url);
                }
            }
        }
        catch (Exception $e )
        {
            
        }
        
        if($url!="")
        {
            $response = new \Symfony\Component\HttpFoundation\RedirectResponse($url);
            $response->send();
        }
    }
    
    
    function __destruct() 
    {

    }
}
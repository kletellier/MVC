<?php

namespace GL\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;   
use GL\Core\RouteProvider;
use Symfony\Component\HttpFoundation\Cookie;

abstract class Controller extends \Symfony\Component\DependencyInjection\ContainerAware
{
    protected $_controller;
    protected $_action;  
    protected $_cookies;
    
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
        $this->_cookies = array();
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
     * Return the session object
     * 
     * @return Symfony\Component\HttpFoundation\Session\Session object 
     */
    public function getSession()
    {
        return $this->container->get('session');
    }

    /**
     * Add Log in debug bar
     * @param string $str string to be added by message
     * @return void
     */
    public function log($str,$withdate = false)
    {
        $message = "";
        if($withdate)
        {
            list($usec, $sec) = explode(' ', microtime());  
            $usec = str_replace("0.", ".", $usec);  
            $message = date('H:i:s', $sec) . $usec. " : ";
        }
        $message.=$str;
        $this->container->get('debug')["messages"]->addMessage($message);
    }
    
    /**
     * Get actual route name
     * 
     * @return string actual route
     */
    public function getRouteName()
      { 
            $name = "";
            $collection = RouteProvider::GetRouteCollection();  
            $request = $this->_container->get('request');
            $url = null;
            if($request->get('url'))
            {
                    $url = $request->get('url');
            } 
            $url = '/'.$url;        
            $context = new RequestContext();            
            $context->fromRequest($request);
            $matcher = new UrlMatcher($collection, $context);            
            try 
            {               
                $parameters = $matcher->match($url);                    
                $name = $parameters['_route'];                 
            }
            catch(ResourceNotFoundException $ex)
            {
                $name = "";          
            }
            catch(Exception $e)
            {       
               $name = "";      
            } 

            return $name;
      }
    
     /**
      * Add global parameters to parameters array passed to view
      * 
      * @param array $arr actual parameters array
      * @return array with global parameters added to input array
      */
    private function GetGlobalVariables($arr)
    {
            $exc = new \Application\Shared\GlobalFunction($arr,$this->container);
            return $exc->execute();
    }

    /**
     * Return instance of Symfony Response Component
     * @return type
     */
    private function getResponse($content,$status = 200, $headers = array('Content-Type' => 'text/html'))
    {
        $response = new Response($content, $status, $headers);
        foreach ($this->_cookies as $cookie) {
            $response->headers->setCookie($cookie);
        }         
        return $response;
    }

    /**
     * Add cookie to  response
     * @param type \Symfony\Component\HttpFoundation\Cookie $cookie 
     * @return void
     */
    public function addCookie( \Symfony\Component\HttpFoundation\Cookie $cookie)
    {
        array_push($this->_cookies, $cookie);
    }
    
    /**
     * Render view provided as PHP page
     * 
     * @param string $view view name to display
     * @param string $inc_parameters parameters array
     */
    function renderPHP($view,$inc_parameters = array())
    {           
        // inject all parameters in array
        // use extract function instead of manually extracting
        extract($this->GetGlobalVariables($inc_parameters));
        $ts = new PhpTemplateService($this->container);
        $ret = $ts->getPathTemplate($view);      
        require($ret);          
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
        $response = $this->getResponse($text,$status,$headers);             
        return $response;
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
        $buf = $this->get('twig')->render($template,$this->GetGlobalVariables($params),$this->container);
        $response = $this->getResponse($buf,$status,$headers);
        return $response;
    }
    
    /**
     * Throw unauthorized error 403
     */
    function isUnauthorized()
    {
        throw new \GL\Core\AccessDeniedHttpException;
    }

    /**
     * Function to allow access to logged user and test roles
     * @param array $roles array of roles allowed to access resource
     * @return void 403 error if not authorized
     */
    function AccessTest($roles = array())
    {
        $id = $this->get('session')->get('session.id');
        if($id=="")
        {
            $this->isUnauthorized();
        }
        else
        {
            if(count($roles)>0)
            {
                $allowed = false;
                $ss = $this->get('security');
                $userroles = $ss->userRoles();
                foreach ($roles as $role ) 
                {
                    if(in_array($role, $userroles))
                    {
                        $allowed = true;
                        break;
                    }
                }             
                if(!$allowed)
                {
                    $this->isUnauthorized();
                }   
            }
        }       
        
    }


    /**
     * Function render html text with Twig template parsing 
     * 
     * @param string $template Twig template to parse
     * @param array $params Twig parameters array
     * @return string Html string buffer
     */
    function renderHtmlTemplate($template,$params = array())
    {         
        return  $this->get('twig')->render($template,$this->GetGlobalVariables($params),$this->container);
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
        return $response;
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
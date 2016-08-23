<?php

namespace GL\Core\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;   
use GL\Core\Routing\RouteProvider;
use Symfony\Component\HttpFoundation\Cookie;
use GL\Core\Config\Config;
use Assert\Assertion;
use Stringy\Stringy;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use GL\Core\Controller\Filters;

abstract class Controller implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    
    protected $_controller;
    protected $_action;  
    protected $_cookies;
    protected $_cookiestodelete;
    
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
        $this->_cookiestodelete = array();
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
     * Return actual database connection PDO object 
     * @return PDO object
     */
    public function getPdo()
    {
        return $this->container->get('pdo');
    }

    /**
     * Return request is postback (form validation)
     * @return boolean
     */
    public function isPostBack()
    {
        return $this->getRequest()->getMethod()=="POST" ? true : false;
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
       * Return actual route
       * @return string
       */
      public function getActualRouteName()
      {  
        $route  = $this->get('request_helper')->getCurrentRoute();         
        return $route["_route"];
      }
        
    
     /**
      * Add global parameters to parameters array passed to view
      * 
      * @param array $arr actual parameters array
      * @return array with global parameters added to input array
      */
    private function GetGlobalVariables($arr)
    {
        $filters = new Filters();
        return $filters->GlobalFunction($arr);    
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
        foreach ($this->_cookiestodelete as $cookie) {
                $response->headers->clearCookie($cookie);
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
     * Cookie to remove in response
     * @param string $cookie cookie name to delete
     * @return void
     */
    public function removeCookie($cookie)
    {
        array_push($this->_cookiestodelete,$cookie);
    }
    
    /**
     * Render view provided as PHP page
     * 
     * @param string $view view name to display
     * @param string $inc_parameters parameters array
     */
    function renderPHP($view,$inc_parameters = array())
    {           
         extract($this->GetGlobalVariables($inc_parameters));
        $ts = new \GL\Core\Templating\PhpTemplateService($this->container,$this->_controller);
        $ret = $ts->getPathTemplate($view);  
            
        ob_start();
        try 
        {
            include $ret;
        } 
        catch (Exception $e) 
        {
            
        }  
        $buffer = ltrim(ob_get_clean());
        $response = $this->getResponse($buffer);
        return $response;        
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
        trigger_error("Deprecated function called, use renderRaw or renderDownload instead of renderText.", E_USER_DEPRECATED);
    }

     /**
     * Function render Html 
     * 
     * @param String $text Html to send to output Response
     * @param Integer $status Http Status
     * @param Array $headers Http-Headers in key value type
     */
    function renderRaw($text,$status = 200, $headers = array('Content-Type' => 'text/html'))
    {
        $response = $this->getResponse($text,$status,$headers);             
        return $response;
    }

    /**
     * Function to force download a file
     * @param type $buffer data to download
     * @param type $filename filename
     * @return response
     */
    function renderDownload($buffer,$filename)
    {  
        $ext = "";
        $mime = "application/octet-stream";
        $dotpos = Stringy::create($filename)->indexOfLast(".");

        if($dotpos!==FALSE)
        {           
            if($dotpos<strlen($filename))
            {
                $dotpos++;
            }
            $ext = Stringy::create($filename)->substr($dotpos)->__toString();
            $mime = \Hoa\Mime\Mime::getMimeFromExtension($ext);
        }
         
        $array = array();
        $array["Content-Type"] = $mime;
        $array["Content-Disposition"] = "attachment; filename=$filename";
        return $this->renderRaw($buffer,200,$array);
    }

    /**
     * 
     * Function render Response with Twig template parsing 
     * 
     * @param string $template Twig template to parse
     * @param array $params Twig parameters array
     * @param integer $status Http Status
     * @param array $headers Http-Headers in key value type
     * @param string template engine to use, blank use default defined in config.yml
     */
    function render($template,$params = array(), $status = 200, $headers = array('Content-Type' => 'text/html'),$engine="" )
    {  
        $debug = (DEVELOPMENT_ENVIRONMENT==TRUE) ? $this->get('debug') : null;
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $debug["time"]->startMeasure('gethtml','Get Html');
        }
        $buf = $this->getHtmlBuffer($template,$params);
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $debug["time"]->stopMeasure('gethtml');
            $debug["time"]->startMeasure('response','Prepare response');
        }
        $response = $this->getResponse($buf,$status,$headers);
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $debug["time"]->stopMeasure('response');
        }
        return $response;
    }
    
    /**
     * Throw unauthorized error 401
     */
    function isUnauthorized()
    {
        throw new \GL\Core\Exception\AccessDeniedHttpException;
    }

    /**
     * Throw unauthorized error 403
     */
    function isForbidden()
    {
        throw new \GL\Core\Exception\AccessForbiddenHttpException;
    }

    /*
    * Throw 404 error
    */
    function is404()
    {
        throw new \GL\Core\Exception\NotFoundHttpException;
    }

    /**
     * Function to allow access to logged user and test roles
     * @param array $roles array of roles allowed to access resource
     * @return void 403 error if not authorized
     */
    function AccessTest($roles = array())
    {
        $id = $this->get('session')->get('session.id');
        if(isset($id) && $id=="")
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
                    foreach ($userroles as $roleu) 
                    {
                        if($roleu==$role)
                        {
                            $allowed = true;
                            break;
                        }
                    }                    
                }             
                if(!$allowed)
                {
                    $this->isForbidden();
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
    function renderHtmlTemplate($template,$params = array(), $executeglobal=false)
    {         
        
        return $this->getHtmlBuffer($template,$params,$executeglobal,true);
    } 

    /**
     * Return HTML from template parsing
     * @param string $template file name
     * @param array $params parameters to submit at template
     * @param bool $htmlmode internal for rendering htmltemplate (render mode)
     * @return string html return of template parsing     * 
     */
    private function getHtmlBuffer($template,$params = array(), $executeglobal=true,$htmlmode=false)
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->get('debug')["time"]->startMeasure('global','Insert global variables');
        }
        $fnparams = null;
        if($executeglobal)
        { 
            $fnparams = $this->GetGlobalVariables($params);
        }
        else
        {
            $fnparams = $params;
        }
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $this->get('debug')["time"]->stopMeasure('global');
        }
        $tpl_service = $this->get('template')->getTemplateService();
        $tpl_service->setContainer($this->container);
        $tpl_service->setController($this->_controller);
        return $tpl_service->render($template,$fnparams,$htmlmode);
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

    private function redirecting($url)
    {
        $response = new \Symfony\Component\HttpFoundation\RedirectResponse($url);
         foreach ($this->_cookies as $cookie) {
            $response->headers->setCookie($cookie);
        } 
        foreach ($this->_cookiestodelete as $cookie) {
            $response->headers->clearCookie($cookie);
        } 
        $response->send();
    }

    function redirectUrl($url)
    {
        $this->redirecting($url);
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
            $url = \GL\Core\Helpers\Utils::route($routename,$params);            
        }
        catch (Exception $e )
        {
            
        }        
        if($url!="")
        {
            $this->redirecting($url);
        }
    }
    
    
    function __destruct() 
    {

    }
}
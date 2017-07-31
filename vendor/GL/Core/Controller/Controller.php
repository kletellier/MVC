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
use Carbon\Carbon;

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
        } catch (\Exception $ex) {
            \Debug::addException($ex);
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
     * Return hash of current url
     * @return string
     */
    public function getUrlKey()
    {
        return $this->get('request_helper')->getUrlKey();
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
            $message = Carbon::createFromFormat('U.u',microtime(true))->format('H:i:s.u') . " : ";            
        }
        $message.=$str;
        \Debug::log($message);
    }

    /**
     * Add measure to debugbar timeline 
     * @param string $key Key used for stopped it 
     * @param string $message Message diplayed in timeline
     * @return void
     */
    public function startMeasure($key,$message)
    {
        \Debug::startMeasure($key,$message);         
    }

    /**
     * Stop measure
     * @param string $key 
     * @return void
     */
    public function stopMeasure($key)
    {        
        try 
        {
            \Debug::stopMeasure($key);  
        } 
        catch (\Exception $e) 
        {  
            \Debug::addException($e);          
        }         
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
        catch (\Exception $e) 
        {
            \Debug::addException($e);
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
        \Debug::startMeasure('gethtml','Get html buffer');
        $buf = $this->getHtmlBuffer($template,$params);
        \Debug::stopMeasure('gethtml');
        \Debug::startMeasure('response','Prepare response');
        $response = $this->getResponse($buf,$status,$headers);
        \Debug::stopMeasure('response');         
        return $response;
    }

    /**
     * Throw new Exception with Http code provided
     * @param integer $code 
     * @return HttpException raised
     */
    function raiseHttpError($code)
    {
        throw new \GL\Core\Exception\HttpException($code);     
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
        if(!isset($id) || $id=="")
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
        \Debug::startMeasure('global','Insert global variables');        
        $fnparams = ($executeglobal==true) ? $this->GetGlobalVariables($params) : $params;        
        \Debug::stopMeasure('global');         
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
        catch (\Exception $e )
        {
            \Debug::addException($e);
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
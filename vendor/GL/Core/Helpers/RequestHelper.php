<?php

namespace GL\Core\Helpers;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route; 
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use GL\Core\DI\ServiceProvider;
use Assert\Assertion;
use Assert\AssertionFailedException;
/**
 * Request helper
 *
 * @author kletellier
 */
class RequestHelper
{
    protected $_request;
    
    function __construct()
    {
        
    }
    
    function setRequest(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->_request = $request;
    }

     /**
     * Return hash of current url
     * @return string
     */
    function getUrlKey()
    {
        $url = '/'.  $this->_request->get('url');
        return sha1($url);
    } 
    
    /**
     * Function for detecting mobile client with useragent
     * 
     * @return bool client is mobile device
     */
    function isMobile()
    {               
        $md = new \Detection\MobileDetect($this->_request->headers->all(),$this->_request->headers->get('User-Agent'));
        return $md->isMobile();
    }

    /**
     * Function for detecting tablet client with useragent
     * 
     * @return bool client is tablet device
     */
    function isTablet()
    {               
        $md = new \Detection\MobileDetect($this->_request->headers->all(),$this->_request->headers->get('User-Agent'));
        return $md->isTablet();
    }
    
    /**
     * Test if client is local
     * 
     * @return boolean If client is localhost
     */
    function isLocalClient()
    {
       return in_array($this->_request->getClientIp(), array('127.0.0.1', 'fe80::1', '::1'));
    }

    /**
     * Get current route parameters in array 
     * @return array (controller,action,parameters and _route)
     */
    function getCurrentRoute()
    {
        $ret = null;
        $inst = null;
        $url = null;

        if($this->_request->get('url'))
        {
            $url = $this->_request->get('url');
        } 
        $url = '/'.$url; 

        $parameters = \Parameters::get('router');
        $classes = "GL\Core\Routing\Router";
     
        if(isset($parameters["classes"]))
        {
            $classes = $parameters["classes"];
        }
        try
        {
            Assertion::classExists($classes);
            $inst = new $classes;
            $ret = $inst->route($url);         
            $args = $inst->getArgs(); 

            $ret = array();
            $ret["controller"] = $inst->getController();
            $ret["action"] = $inst->getMethod(); 
            $ret["_route"] = $inst->getRoute();
            
            $ret = array_merge($ret,$args);
        } 
        catch (AssertionFailedException $e) 
        {
            $ret = null();
        }
        return $ret;  
    }
}

<?php

namespace GL\Core\Helpers;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route; 
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use GL\Core\DI\ServiceProvider;

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
        
        $container = ServiceProvider::GetDependencyContainer();  
        $collection = $container->get('routes'); 
        $context = new RequestContext();    
        $context->fromRequest($this->_request);
        $matcher = new UrlMatcher($collection, $context);
        $url = null;
        if($this->_request->get('url'))
        {
            $url = $this->_request->get('url');
        } 
        $url = '/'.$url; 

        $parameters = null;
        try 
        {
            $parameters = $matcher->match($url);             
        } catch (ResourceNotFoundException $e) 
        {
            
        }
        return $parameters;  
    }
}

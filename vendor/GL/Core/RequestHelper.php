<?php

namespace GL\Core;
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
        $useragent = $this->_request->headers->get('User-Agent');
        //var_dump($useragent);
        $regex_match="/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
        $regex_match.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
        $regex_match.="blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
        $regex_match.="symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
        $regex_match.="jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";
        $regex_match.=")/i";
        return  preg_match($regex_match, strtolower($useragent));
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
}

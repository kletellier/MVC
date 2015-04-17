<?php

namespace GL\Core\Security;
 
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class FormCrsf
{
    protected $_session;
    protected $_request;
    
    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $session,\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->_session = $session;      
        $this->_request = $request;
    }
    
    public function InitCrsf()
    {         
        $session = $this->_session;
        $key = uniqid("crsf");        
        $session->set('crsftoken',$key);
        $session->save();
        return $key;
    }
    
    /**
     * Verify Crsf token
     *   
     * @param type $forminp The name of form input or if not found value to compare
     */
    public function VerifyCrsf($forminp = "crsftoken")
    {
        $session = $this->_session;
        $token = $session->get('crsftoken');
        $valeur = $forminp;
        if($this->_request->getMethod()=="POST")
        {
            if($this->_request->get($forminp)!='')
            {
                $valeur = $this->_request->get($forminp);
            }
            // clear the token
            $this->InitCrsf();
        }
        return ($valeur==$token);
    }
    
    /**
     * Return actual crsf token
     * 
     * @return string Actual Csrf toker
     */
    public function GetToken()
    {
        $session = $this->_session;     
        if($session!=null)
        {
            $token = $session->get('crsftoken');
        }
        return $token;        
    }
}

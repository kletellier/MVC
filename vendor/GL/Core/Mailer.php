<?php

namespace GL\Core;
 
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * SwiftMailer wrapper
 */
class Mailer
{
    private $_server;
    private $_user;
    private $_password;
    private $_port;
    private $_from;
    private $_to;
    private $_subject;
    private $_body;
    private $_isHtml;
    private $_attach;
 
    /**
     * Constructor
     */
    function __construct() 
    {
            $this->_server = "127.0.0.1";
            $this->_port = 25;
            $this->_user = "";
            $this->_password = "";
            $this->_from = array();
            $this->_to = array();
            $this->_subject = "";
            $this->_body = "";
            $this->_isHtml = false;
            $this->_attach = array();
            $this->getParams();
    }
    
    /**
     * Add all attachments in $message
     * 
     * @param Swift_Message $message Swift message instance
     */
    private function getAttachment($message)
    {
        foreach($this->_attach as $tmp)
        {
            $attachment = \Swift_Attachment::fromPath($tmp); 
            $message->attach($attachment);
        }
    }
    
    /**
     * 
     * Add all recipients in message
     * 
     * @param Swift_Message $message Swift message instance
     */
    private function getTo($message)
    {                
        foreach($this->_to as $mail)
        {
            $tmp = explode("::",$mail);
            {
                $mailtmp = $tmp[0];
                $nomtmp = "";
                if(isset($tmp[1]))
                {
                    $nomtmp= $tmp[1];                    
                    $message->addTo($mailtmp,$nomtmp);
                }
                else
                {
                    $message->addTo($mailtmp);
                }                 
            }
        }          
    }
    
    /**
     * Get all mail smtp parameters from config/mail.yml
     */
    private function getParams()
    { 
        $loader = new Config('mail');
        $value = $loader->load();
	    $arr = $value["mail"];
        if($arr!=null)
        {
            $this->_server = $arr["server"];
            $this->_port = $arr["port"];
            $this->_user = $arr["user"];
            $this->_password = $arr["password"];
        }
    }
    
    /**
     * Set mail from
     * 
     * @param string $mail from mail
     * @param string $from from name
     */
    public function setFrom($mail,$from = "")
    {
        $fromtmp = $from ?: $mail;
        $this->_from = array($mail => $fromtmp);
    }
    
    /**
     * Set mail subject
     * 
     * @param string $subject mail subject
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
    }
    
    /**
     * Set mail body
     * 
     * @param string $body mailbody
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }
    
    /**
     * Set if body is html
     * 
     * @param bool $ishtml
     */
    public function setIsHtml($ishtml)
    {
        $this->_isHtml = $ishtml;
    }
    
    /**
     * Add recipient 
     * @param string $to recipient mail
     * @param string $toname recipient name 
     */
    public function addTo($to,$toname="")
    {
        $val = $to;
        if($toname!="")
        {
            $val.="::".$toname;
        }
        array_push($this->_to,$val);
    }
   
    /**
     * Add attachment to mail
     * @param string $path path to attachment
     * @return boolean
     */
    public function addAttach($path)
    {
        $bret = false;
        $tmp = $path;        
        $fs = new Filesystem();
        try
        {
            if($fs->exists($tmp))
            {
               array_push($this->_attach,$tmp);
               $bret = true;
            }                    
        } 
        catch (IOException $ex) 
        {
               $bret = false;
        }
        return $bret;
    }
    
    /**
     * Send mail
     * 
     * @return bool mail sended
     */
    public function send()
    {       
        $transport = \Swift_SmtpTransport::newInstance($this->_server, $this->_port);
        if($this->_user!="")
        {
            $transport->setUsername($this->_user)
                    ->setPassword($this->_password);                             
        }

        $mailer = \Swift_Mailer::newInstance($transport);

        $message = \Swift_Message::newInstance() 
          ->setSubject($this->_subject)			 
          ->setFrom($this->_from)         
          ->setBody($this->_body)  ;
        if($this->_isHtml)
        {
            $message->setContentType("text/html");
        }
        // ajout des destinataires
        $this->getTo($message);
        // ajout des pièces jointes
        $this->getAttachment($message);   
        $result = $mailer->send($message);        
        return ($result>=1);
    }
        
    function __destruct()
    {
        
    }
}

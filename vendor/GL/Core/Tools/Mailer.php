<?php

namespace GL\Core\Tools;
 
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use GL\Core\Config\Config;

/**
 * PHPMailer wrapper
 */
class Mailer
{
    private $_server;
    private $_user;
    private $_password;
    private $_port;
    private $_from;
    private $_fromname;
    private $_to;
    private $_cc;
    private $_bcc;
    private $_replyto;
    private $_subject;
    private $_body;
    private $_isHtml;
    private $_attach;
    private $_disable;
 
    /**
     * Constructor
     */
    function __construct() 
    {
            $this->_server = "127.0.0.1";
            $this->_port = 25;
            $this->_user = "";
            $this->_password = "";

            $this->_fromname = "";
            $this->_from = "";
            
            $this->_replyto = array();
            $this->_to = array();
            $this->_cc = array();
            $this->_bcc = array();

            $this->_subject = "";
            $this->_body = "";
            $this->_isHtml = false;
            $this->_attach = array();
            $this->_disable = 0;

            $this->_encryption = "";
            $this->_secure = 0;

            $this->_charset = "UTF-8";
            
            $this->getParams();
    }
    
    /**
     * Add all attachments in $message
     * 
     * @param Swift_Message $message Swift message instance
     */
    private function getAttachment(\PHPMailer $message)
    {
        foreach($this->_attach as $tmp)
        {
            $message->addAttachment($tmp);
        }
    }


    /**
     * Clear attachment list
     * @return void
     */
    public function clearAttach()
    {
      $this->_attach = array();
    }

    /**
     * Clear recipients lists
     * @return void
     */
    public function clearRecipients()
    {
      $this->_to = array();
      $this->_bcc = array();
      $this->_cc = array();
      $this->_replyto = array();
    }

    /**
     * Reset all parameters (call after every send for batch sending)
     * @return void
     */
    public function reset($keep_subject_and_body=false)
    {
      $this->clearAttach();
      $this->clearRecipients();
      if(!$keep_subject_and_body)
      {
        $this->_body = "";
        $this->_subject = "";
      }
      
    }
    
    /**
     * 
     * Add all recipients in message
     * 
     * @param Swift_Message $message Swift message instance
     */
    private function getTo(\PHPMailer $message)
    {            
        // ajout destinataire    
        foreach($this->_to as $mail)
        {
            $tmp = explode("::",$mail);
            {
                $mailtmp = $tmp[0];
                $nomtmp = "";
                if(isset($tmp[1]))
                {
                    $nomtmp= $tmp[1];                    
                    $message->addAddress($mailtmp,$nomtmp);
                }
                else
                {
                    $message->addAddress($mailtmp);
                }                 
            }
        }  
        // add carbon copy
        foreach($this->_cc as $mail)
        {
            $tmp = explode("::",$mail);
            {
                $mailtmp = $tmp[0];
                $nomtmp = "";
                if(isset($tmp[1]))
                {
                    $nomtmp= $tmp[1];                    
                    $message->addCC($mailtmp,$nomtmp);
                }
                else
                {
                    $message->addCC($mailtmp);
                }                 
            }
        } 
        // add blind carbon copy
        foreach($this->_bcc as $mail)
        {
            $tmp = explode("::",$mail);
            {
                $mailtmp = $tmp[0];
                $nomtmp = "";
                if(isset($tmp[1]))
                {
                    $nomtmp= $tmp[1];                    
                    $message->addBCC($mailtmp,$nomtmp);
                }
                else
                {
                    $message->addBCC($mailtmp);
                }                 
            }
        }   
        // add reply to 
        foreach ($this->_replyto as $mail) {
              $tmp = explode("::",$mail);
            {
                $mailtmp = $tmp[0];
                $nomtmp = "";
                if(isset($tmp[1]))
                {
                    $nomtmp= $tmp[1];                    
                    $message->addReplyTo($mailtmp,$nomtmp);
                }
                else
                {
                    $message->addReplyTo($mailtmp);
                }                 
            }
            }    
    }
    
    /**
     * Get all mail smtp parameters from config/mail.yml
     */
    private function getParams()
    {          
        $arr = \Parameters::get('mail');         
        if($arr!=null)
        {
            $this->_server = $arr["server"];
            $this->_port = $arr["port"];
            $this->_user = $arr["user"];
            $this->_password = $arr["password"];
            if(isset($arr["disable"]))
            {
                $this->_disable = $arr['disable'];
            }
            if(isset($arr["secure"]))
               {
                $this->_secure = $arr['secure'];
            }
            if(isset($arr["encryption"]))
               {
                $this->_encryption = $arr['encryption'];
            }
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
        $this->_fromname = $from ?: $mail;
        $this->_from = $mail;
         
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
     * Add copy 
     * @param string $to recipient mail
     * @param string $toname recipient name 
     */
    public function addCc($cc,$ccname="")
    {
        $val = $cc;
        if($ccname!="")
        {
            $val.="::".$ccname;
        }
        array_push($this->_cc,$val);
    }

    /**
     * Add blinded copy 
     * @param string $to recipient mail
     * @param string $toname recipient name 
     */
    public function addBcc($bcc,$bccname="")
    {
        $val = $bcc;
        if($bccname!="")
        {
            $val.="::".$bccname;
        }
        array_push($this->_bcc,$val);
    }

     /**
     * Add reply to
     * @param string $to recipient mail
     * @param string $toname recipient name 
     */
    public function addReplyTo($rto,$rtoname="")
    {
        $val = $rto;
        if($rtoname!="")
        {
            $val.="::".$rtoname;
        }
        array_push($this->_replyto,$val);
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

    public function setCharset($charset)
    {
      $this->_charset = $charset;
    }

    public function getCharset()
    {
      return $this->_charset;
    }
    
    /**
     * Send mail
     * 
     * @return bool mail sended
     */
    public function send($call_reset_after=false)
    {       
        $mail = new \PHPMailer;
        $mail->isSMTP();
        $mail->CharSet = $this->_charset;
        $mail->Host = $this->_server;
        $mail->Port = $this->_port;
         if($this->_user!="")
        {
            $mail->SMTPAuth = true;                             
            $mail->Username = $this->_user;                 
            $mail->Password = $this->_password;  
        }
        if($this->_secure==1)
        {
          $mail->SMTPSecure  = $this->_encryption;
          if (version_compare(PHP_VERSION, '5.6.0') >= 0) 
          {
            // ssl check pb with php 5.6
                $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
          }
          
        }

        $mail->setFrom($this->_from,$this->_fromname);
        $mail->Subject = $this->_subject;
        $mail->Body = $this->_body;
        if($this->_isHtml)
        {
            $mail->isHTML(true);
        }         
        $this->getTo($mail);         
        $this->getAttachment($mail);   
        $result = 0;
        if($this->_disable==0)
        {
            $result = $mail->send();   
        }             
        if(!$result)
        {         
            throw new \Exception($mail->ErrorInfo);            
        }
        if($call_reset_after)
        {
          $this->reset();
        }
        return $result;
    }
        
    function __destruct()
    {
        
    }
}
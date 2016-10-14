<?php 
namespace Application\Shared;   

use Symfony\Component\DependencyInjection\ContainerInterface; 

/** Add rendering time after html closing tag in production version **/
class AddRenderingTime implements \GL\Core\Controller\FilterResponseInterface
{   
    protected $_response;
    protected $_container;       

    public function __construct(\Symfony\Component\HttpFoundation\Response $response,\Symfony\Component\DependencyInjection\Container $container)
    {
       $this->_response = $response;
       $this->_container = $container;          
    }

    public function execute()
    {
        $resp = $this->_response;
        try 
        {        
            $req =  $this->_container->get('request')->server->get('REQUEST_TIME_FLOAT');            
            $micro = microtime(true);
            $duree = ($micro-$req)*1000;
            $headers = $resp->headers;
            $ct = $headers->get('Content-Type');   
  
            if(strtolower($ct)=="text/html")
            {
                $html = $resp->getContent();    

                if (strpos($html, '</html>') !== false)
                {
                     
                    $buf = "</html>" . "\r\n<!-- generation time : " . $duree  . " ms -->";
                    $html=str_replace("</html>", $buf, $html);
                    $resp->setContent($html);
                }
            }       
        
        } 
        catch (Exception $e) 
        {
            
        }

         return $resp;
    }
}
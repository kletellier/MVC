<?php 
namespace GL\Core\Debug;   

use Symfony\Component\DependencyInjection\ContainerInterface; 

/**
 * Class to inject debugbar in dev environnment
 * Called by shared.php before rendering
 */
class DebugInject implements \GL\Core\Controller\FilterResponseInterface
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
            $headers = $resp->headers;
            $ct = $headers->get('Content-Type');
            
            if(strtolower($ct)=="text/html")
            {
                $content = $resp->getContent();                 
                // add debugbar before <html> closure tag
                $debugbar = $this->_container->get('debug');
                $renderer = $debugbar->getJavascriptRenderer();
                $url = \GL\Core\Helpers\Utils::url("/dbg");
                $renderer->setBaseUrl($url);

                $dbghd = $renderer->renderHead();
                $dbgct = $renderer->render();
                $buf = "";
                // test if </head> if present
                if (strpos($content, '</head>') !== false)
                {
                    $head = $dbghd."</head>";
                    $content=str_replace("</head>", $head, $content);
                }
                else
                {
                    $buf.=$dbghd;
                }
         
                $buf.=$dbgct."</html>";
                $content=str_replace("</html>", $buf, $content);

                $resp->setContent($content);
            }       
        
        } 
        catch (Exception $e) 
        {
            
        }

        // Warning don't forget to return a symfony response object !!
        return $resp;
    }
}
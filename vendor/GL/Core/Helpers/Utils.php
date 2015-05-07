<?php

namespace GL\Core\Helpers;

class Utils
{
    
     /**
     * Convert relative url to absolute url
     * 
     * @param string $partialurl relative url
     * @return string absolute url (use BASE_PATH define in config.php)
     */
    public static function url($partialurl)
    {
        $sep = "/";
        $basepath = BASE_PATH;
        if($basepath=="")
        {
            // search in request
            $req = \GL\Core\DI\ServiceProvider::GetDependencyContainer()->get('request');    
            $basepath = $req->getSchemeAndHttpHost().$req->getBasePath();             
        }
        if(substr($basepath, -1)!=$sep)
        {
            $basepath = $basepath.$sep;
        }
        $partial = $partialurl;
        if(substr($partial,0,1)==$sep)
        {
            $partial = substr($partial, 1);
        }
        $path = $basepath . $partial;
        return $path;
    }

     /**
     * Get Url from routename and parameters array
     * @param string $routename route name defined in routes.yml
     * @param array $params parameters array
     * @return string url
     */
    public static function route($routename,$params = array())
    {
        $url = "";
        try {
            $rc = \GL\Core\DI\ServiceProvider::GetDependencyContainer()->get('routes');
            $route = $rc->get($routename);

            
            if($route!=null)
            {
                $pattern = $route->getPath();                 
                $url = \GL\Core\Helpers\Utils::url($pattern);
                $defaults = $route->getDefaults();
                // replace parameters by provided array
                foreach($params as $key => $value)
                {
                    $str = '{'.$key.'}';
                    $url = str_replace($str, $value, $url);
                }
                // use defaults parameters
                foreach ($defaults as $key => $value) {
                   $str = '{'.$key.'}';
                   $url = str_replace($str, $value, $url);
                }
                 // in case of optionnal parameters in last, remove last slash
                $sep = "/";                
                if(substr($url, -1)==$sep)
                {
                    $url = substr($url,0, -1);
                }
            }
        } catch (Exception $e) {
            
        }
        
        return $url;
    }
    
}


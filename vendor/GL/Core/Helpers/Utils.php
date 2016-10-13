<?php

namespace GL\Core\Helpers;

use Stringy\Stringy as S;

class Utils
{
     /**
     * Return public path
     * @return string
     */
    public static function getPublicPath()
    {
        return PUBLICPATH;
    }
    
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
        return S::create($basepath)->ensureRight($sep)->__toString() . S::create($partialurl)->removeLeft($sep)->__toString();        
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
                $urlo = S::create($url);
                $sep = "/";
                $defaults = $route->getDefaults();
                $param_array = array_merge($defaults,$params);
                foreach ($param_array as $key => $value) {
                     $str = '{'.$key.'}'; 
                     $urlo = $urlo->replace($str,$value);                     
                }  
                $urlo->removeRight($sep);
            }
        } catch (Exception $e) {
            $urlo = S::create("");
        }
        
        return $urlo->__toString();
    }
    
}


<?php

namespace GL\Core;

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
            $rc = \GL\Core\ServiceProvider::GetDependencyContainer()->get('routes');
            $route = $rc->get($routename);

            
            if($route!=null)
            {
                $pattern = $route->getPattern();
                $url = \GL\Core\Utils::url($pattern);
                // replace parameters by provided array
                foreach($params as $key => $value)
                {
                    $str = '{'.$key.'}';
                    $url = str_replace($str, $value, $url);
                }
            }
        } catch (Exception $e) {
            
        }
        
        return $url;
    }
    
}


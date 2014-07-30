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
            $basepath.=$sep;
        }
        $partial = $partialurl;
        if(substr($partial,0,1)==$sep)
        {
            $partial = substr($partial, 1);
        }
        $path = BASE_PATH . $partial;
        return $path;
    }
    
}


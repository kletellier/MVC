<?php 
namespace GL\Core\Config;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class to translate text by using file definition
 */
class Translator  
{
    
  protected $locale;
  protected $arr;


    public function __construct()
    {        
        $this->locale = LOCALE;
        $this->arr = array();
        $this->loadFile();
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
        $this->loadFile();
    }

    public function loadFile()
    {
        $ret = false;
        try 
        {
            $path = ROOT . DS . "lang" .DS . $this->locale . ".yml"; 
            $fs = new Filesystem();
            $exist = $fs->exists(array($path));

            if($exist)
            {
               $yaml = new Parser();
               $this->arr = $yaml->parse(file_get_contents($path));             
               $ret = true;
            }           
        } 
        catch (IOException $e) {
            $ret = false;
        }
        catch (Exception $e) {
            $ret = false;
        }   
        return $ret;
    }

     
    /**
     * Get translation from language file
     * @param string $text  texte to translate
     * @return string text translated
     */
    public function translate($text)
    {
        $ret = "";
        $tmp = explode(".", $text);
        $section = $tmp[0];
        $key =$tmp[1];
        if(isset($this->arr[$section][$key]))
        {
            $ret = $this->arr[$section][$key];
        }
        else
        {
            $ret = "{{".$text."}}";
        }
                   
        return $ret;
    }
}
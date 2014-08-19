<?php 
namespace GL\Core;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Class to translate text by using file definition
 */
class Translator  
{
  protected $text;   
  protected $locale;


    public function __construct($text,$locale)
    {
        $this->text = $text;
        $this->locale = $locale;
    }

    /**
     * Get translation from language file
     * 
     * @return string string translated
     */
    public function translate()
    {
        $ret = "";

        try 
        {
            $path = ROOT . DS . "lang" .DS . $this->locale . ".yml"; 
            $fs = new Filesystem();
            $exist = $fs->exists(array($path));

            if($exist)
            {
                $yaml = new Parser();
                $value = $yaml->parse(file_get_contents($path));             
                $arr = explode(".", $this->text);
                $section = $arr[0];
                $key =$arr[1];
                if(isset($value[$section][$key]))
                {
                    $ret = $value[$section][$key];
                }
            }           
        } 
        catch (IOException $e) {
            //echo "An error occured";
        }
        catch (Exception $e) {
            
        }              
        return $ret;
    }
}
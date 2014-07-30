<?php
 
namespace GL\Core;

/**
 * Class PDF Wrapper extend FPDF
 */
class PDF extends \Fpdf\Fpdf
{
    /**
     * 
     * Generate string buffer of PDF created
     * 
     * @return string string buffer of PDF file generated
     */
    function GetBuffer()
    {
       return  $this->Output('', 'S');
    }
    
}

<?php

namespace GL\Core\Tools;
use TCPDF;

/**
 * Class PDF Wrapper extend TCPDF
 */
class PDF extends TCPDF
{
    /**
     * 
     * Generate string buffer of PDF created
     * 
     * @return string string buffer of PDF file generated
     */
    function GetOuput()
    {
       return  $this->Output('', 'S');
    }

    
    function __construct()
    {         
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
         
    }

}

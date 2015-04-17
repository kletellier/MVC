<?php

namespace GL\Core\Tools;

/**
 * Class extending PHPOffice PhpExcel
 */
class Excel extends \PHPExcel
{
    /**
     * Return Excel Workbook in buffer 
     * 
     * @return string Excel string buffer
     */
    function GetBuffer()
    {
        // store tmpfile in TMPPATH need accessright
        $objWriter = new \PHPExcel_Writer_Excel5($this);            
        $path = TMPPATH . DS . uniqid('xls_') . '.xls';
        $objWriter->save($path);
        $handle = fopen($path, "r");
        $contents = fread($handle, filesize($path));
        fclose($handle);
      
        return $contents;
    }
}

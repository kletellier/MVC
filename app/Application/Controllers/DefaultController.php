<?php 
namespace Application\Controllers;

use GL\Core\Controller\Controller as Controller;

class DefaultController extends Controller
{
    public function hello($name)
    {                              
        $text = "Hello " .$name . " !";
        return $this->render('index.html.twig',array('text'=>$text));   
    }
    
    public function testdb()
    {
        $test = \Application\Models\Test::all();
        return $this->renderJSON($test);       
    }
    
    public function xls()
    {
        // get Excel service from DI container
        $objPHPExcel = $this->get('excel');            
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2003 XLS Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2003 XLS Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2003 XLS, generated using PHP classes.");

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Hello World');
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        
        $contents = $objPHPExcel->GetBuffer();
        return $this->renderDownload($contents,"file.xls");
    }
    
    public function pdf()
    {
        // get PDF service from DI container
        $pdf = $this->get('pdf');
        $pdf->AddPage();            
        $pdf->Cell(40,10,'Hello World !');
        $buffer = $pdf->GetOuput(); 
        return $this->renderDownload($buffer,"file.pdf");
    }
    
}

<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Question\Question;
class ControllerCommand extends Command
{
	protected $model = '<?php 
namespace Application\Controllers;

use GL\Core\Controller\Controller as Controller;

class ##name##Controller extends Controller
{

}';

    protected function configure()
    {
        $this
            ->setName('controller:create')
            ->setDescription('Create controller class and view folder') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        try 
        {
             
            $controllername = "";

            $helper = $this->getHelper('question');
            $question = new Question('Please enter the controller name : ', '');
            $controller = $helper->ask($input, $output, $question);
            if($controller!="")
            {
                $controllername =  ucfirst($controller);
            }
            else
            {
                throw new \Exception("you must specify controller name");
            }
             
            $nmodel = $this->model;
            $nmodel = str_replace('##name##',$controllername,$nmodel);            			

            $path = ROOT . DS . "app" . DS . "Application" . DS . "Controllers". DS;
            // create controller file
            $filename = $path.$controllername."Controller.php";
            file_put_contents($filename,$nmodel);	
            // create view folder
            $pathdi = TEMPLATEPATH . DS . $controllername;
            $fs->mkdir($pathdi);
		 
        } 
        catch (\Exception $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }	
        catch (IOExceptionInterface $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }	 
        $output->writeln('finished');
    }
}
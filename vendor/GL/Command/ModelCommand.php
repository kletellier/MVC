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
class ModelCommand extends Command
{
	protected $model = '<?php
 
namespace Application\Models;

use Illuminate\Database\Eloquent\Model; 

class ##modelname## extends Model {
	protected $table = "##tablename##";
	protected $foreignKey = "##foreignkey##"
    public $timestamps = false;
	 
}';

    protected function configure()
    {
        $this
            ->setName('model:create')
            ->setDescription('Create model class for mysql table') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        try 
        {
            $table = "";
            $foreignkey = "id";
            $modelname = "";

            $helper = $this->getHelper('question');
            $question = new Question('Please enter the mysql table name : ', '');
            $table = $helper->ask($input, $output, $question);
            if($table!="")
            {
                    $modelname =  ucfirst($table);
            }
            else
            {
                    throw new \Exception("you must specify table name");
            }
            $question = new Question('Please enter the model name : ('. $modelname . ') : ', $modelname);
            $modelname = $helper->ask($input, $output, $question);
            if($modelname=="")
            {
                throw new \Exception("you must specify model name");
            }            
            $question = new Question('Please enter the foreign key name ('.$foreignkey.') : ', $foreignkey);
            $foreignkey = $helper->ask($input, $output, $question);
            if($foreignkey=="")
            {
                throw new \Exception("you must specify foreignkey");
            }

            $nmodel = $this->model;
            $nmodel = str_replace('##modelname##',$modelname,$nmodel);
            $nmodel = str_replace('##tablename##',$table,$nmodel);
            $nmodel = str_replace('##foreignkey##',$foreignkey,$nmodel);			

            $path = ROOT . DS . "app" . DS . "Application" . DS . "models". DS;

            $filename = $path.$modelname.".php";
            file_put_contents($filename,$nmodel);			
		 
        } 
        catch (\Exception $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
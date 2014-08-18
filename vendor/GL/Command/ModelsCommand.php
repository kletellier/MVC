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

use PDO;
use Illuminate\Database\Capsule\Manager as Capsule;

class ModelsCommand extends Command
{
	protected $model = '<?php
 
namespace Application\Models;

use Illuminate\Database\Eloquent\Model; 

class ##modelname## extends Model {
	protected $table = "##tablename##";
	protected $primaryKey = "##foreignkey##";
    public $timestamps = false;
	 
}';

    protected function configure()
    {
        $this
            ->setName('model:createall')
            ->setDescription('Create models class for all tables') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        try 
        {
			Capsule::connection("default")->setFetchMode(PDO::FETCH_NUM);
			$arr = Capsule::connection("default")->select('SHOW TABLES;');
			
			foreach($arr as $ts)
			{
				
				$name = $ts[0];
				$output->writeln($name);
				
				// search for primary			 
				$sql1 = "SHOW INDEX from " . $name . " WHERE Key_name='PRIMARY';";
				$pk1 = Capsule::connection("default")->select($sql1);
				if(count($pk1)>0)
				{
					$pk = $pk1[0][4];
				}
				else
				{
					// by first unique index
					$sql2 = "SHOW INDEX from " . $name . " WHERE Non_unique=0;";
					$pk2 = Capsule::connection("default")->select($sql2);
					if(count($pk2)>0)
					{
						$pk = $pk2[0][4];
					}
				} 
				
				$modelname =  ucfirst($name);

				$nmodel = $this->model;
				$nmodel = str_replace('##modelname##',$modelname,$nmodel);
				$nmodel = str_replace('##tablename##',$name,$nmodel);
				$nmodel = str_replace('##foreignkey##',$pk,$nmodel);			

				$path = ROOT . DS . "app" . DS . "Application" . DS . "models". DS;

				$filename = $path.$modelname.".php";
				file_put_contents($filename,$nmodel);		
			}		 
        } 
        catch (\Exception $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
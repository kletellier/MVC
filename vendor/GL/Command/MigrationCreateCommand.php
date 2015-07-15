<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use GL\Core\Migration\Migrator;
use Symfony\Component\Console\Question\Question;
 

class MigrationCreateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migration:create')
            ->setDescription('Create a migration file') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Create a migration file');
         
        try 
        {
            $helper = $this->getHelper('question');
            $question = new Question('Please enter migration key : ', '');
            $key = $helper->ask($input, $output, $question);
            if($key=="")
            {
                throw new \Exception("you must specify migration key");
            }
            $question = new Question('Please enter a description : ', '');
            $description = $helper->ask($input, $output, $question);
            if($description=="")
            {
                throw new \Exception("you must specify an description");
            }
            // test if key exist
            $migrator = new Migrator();
            if($migrator->testExist($key))
            {
                throw new \Exception("Key was already created");
            }
            $ret = $migrator->create($key,$description);   
            $keyslug = "";
            if($ret)
            {
               $keyslug = $migrator->getSlugKeyName($key);
            } 
            $message = ($ret==true) ? "Migration $keyslug created": "Error during creating";
            $output->writeln($message);
            
        } 
        catch (\Exception $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
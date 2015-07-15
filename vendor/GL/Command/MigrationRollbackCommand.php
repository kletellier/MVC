<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use GL\Core\Migration\Migrator;

class MigrationRollbackCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migration:rollback')
            ->addArgument('migration_key', InputArgument::REQUIRED, 'migration_key') 
            ->setDescription('Rollback a migration') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Rollback migration');
         
        try 
        {
            $key = $input->getArgument('migration_key');
            if($key=="")
            {
                throw new \Exception("You must specify a key");
            }
            $migrator = new Migrator();
            $migrator->rollback($key);        
        } 
        catch (\Exception $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
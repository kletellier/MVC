<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use GL\Core\Migration\Migrator;

class MigrationCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migration:execute')
            ->setDescription('Execute all migration files') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Execute all migration files');
         
        try 
        {
            $migrator = new Migrator();
            $migrator->migrate();        
        } 
        catch (\Exception $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
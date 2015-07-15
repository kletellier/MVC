<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use GL\Core\Migration\Migrator;
use Carbon\Carbon;

class MigrationListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('migration:list')
            ->setDescription('Give list of all keys') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Give list of all migrations');
         
        try 
        {
            $migrator = new Migrator();
            $keys = $migrator->MigrationList(); 
            foreach ($keys as $key ) 
            {
               $date = Carbon::createFromFormat("YmdHis",$key->getCreationDate())->format("d/m/Y H:i:s");
               $texte = $key->getUniqueTag() . " : " . $key->getDescription() . " -> " . $date;
               $output->writeln($texte);
            }       
        } 
        catch (\Exception $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
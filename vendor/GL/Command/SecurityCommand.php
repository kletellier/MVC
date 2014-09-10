<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use GL\Core\SecurityService;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class SecurityCommand extends Command
{	 

    protected function configure()
    {
        $this
            ->setName('security:create')
            ->setDescription('Create security users table and model') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        try 
        {
            $ss = new SecurityService(new Session(),new Request());
            $output->writeln('Create users table and model');
            $ss->createTable();			 
        } 
        catch (\Exception $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
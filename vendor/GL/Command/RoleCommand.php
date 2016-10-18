<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Question\Question;
use GL\Core\Security\AuthenticationService;
use GL\Core\Config\Config;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class RoleCommand extends Command
{
	 
    protected function configure()
    {
        $this
            ->setName('security:createrole')
            ->setDescription('Create role') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {       
        try 
        {

            $helper = $this->getHelper('question');
            $question = new Question('Please enter the role key : ', '');
            $role = $helper->ask($input, $output, $question);
            if($role=="")
            {
                throw new \Exception("you must specify role key");
            }
            $roles = \Stringy\Stringy::create($role)->slugify();
            $question = new Question('Please enter the role description : ', '');
            $description = $helper->ask($input, $output, $question);
            if($description=="")
            {
                throw new \Exception("you must specify a description");
            }            
             
            // récupération de la config securité
            $values = \Parameters::get('security');
            $class =   $values['security']['classes'];
            $ss = new $class(new Session(),new Request());
            $output->writeln('Create role');
            $ss->roleCreate($roles,$description);		
		 
        } 
        catch (\Exception $e) 
        {
            $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
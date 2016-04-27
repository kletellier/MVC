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

class UserCommand extends Command
{
	 
    protected function configure()
    {
        $this
            ->setName('security:createuser')
            ->setDescription('Create user') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {       
        try 
        {

            $helper = $this->getHelper('question');
            $question = new Question('Please enter the login : ', '');
            $login = $helper->ask($input, $output, $question);
            if($login=="")
            {
                    throw new \Exception("you must specify login");
            }
            $question = new Question('Please enter the mail : ', '');
            $email = $helper->ask($input, $output, $question);
            if($email=="")
            {
                throw new \Exception("you must specify a mail");
            }            
            $question = new Question('Please enter the password : ', '');
            $password = $helper->ask($input, $output, $question);
            if($password=="")
            {
                throw new \Exception("you must specify a password");
            }             
            // récupération de la config securité
            $values = \Parameters::get('security');
            $class =   $values['security']['classes'];
            $ss = new $class(new Session(),new Request());
            $output->writeln('Create user');
            $ss->userCreate($login,$email,$password);			
		 
        } 
        catch (\Exception $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
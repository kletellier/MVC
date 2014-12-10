<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Question\Question;
use GL\Core\SecurityService;
use GL\Core\Config;
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
            $question = new Question('Please enter roles separated by comma : (USER) ', 'USER');
            $roles = $helper->ask($input, $output, $question);
            if($roles=="")
            {
                throw new \Exception("you must specify one role at least");
            }
            else
            {
                $arroles = explode(",", $roles);
            }
            // récupération de la config securité
            $cfgsecu = new Config('security');
            $values = $cfgsecu->load();
            $class =   $values['security']['classes'];
            $ss = new $class(new Session(),new Request());
            $output->writeln('Create user');
            $ss->userCreate($login,$email,$password,$arroles);			
		 
        } 
        catch (\Exception $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
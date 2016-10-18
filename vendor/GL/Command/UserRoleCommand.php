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

class UserRoleCommand extends Command
{
	 
    protected function configure()
    {
        $this
            ->setName('security:addroleuser')
            ->setDescription('Add role to user') 
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
                    
            // récupération de la config securité
            $values = \Parameters::get('security');
            $class =   $values['security']['classes'];
            $ss = new $class(new Session(),new Request());

            // get user instance
            $user = $ss->userFromLogin($login);
            if($user==null)
            {
                throw new  \Exception("User $login doesn't exist");                
            }
            $iduser = $user->id;

            // get all roles
            $roles = $ss->getRoles();
            $strRole = "[";
            foreach ($roles as $role) 
            {
                if($strRole!="[")
                {
                    $strRole.=",";
                }
                $strRole.=$role->role;
            }
            $strRole.="]";
            $question = new Question('Add roles for ' . $login . ' ' . $strRole . ', type role separated by comma : ', '');
            $roles = $helper->ask($input, $output, $question);
            
            $output->writeln('Add roles to user');
            			
		    if($roles!="" && $iduser!=null)
            {           
                $rolea = \Stringy\Stringy::create($roles)->split(",");
                foreach ($rolea as $role) 
                {
                    $roles = $ss->getRolesFromName(array($role));
                    $role1 = $roles->first();
                    $ss->addUserToRole($iduser,$role1->id);
                }
            }  
        } 
        catch (\Exception $e) 
        {
            $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
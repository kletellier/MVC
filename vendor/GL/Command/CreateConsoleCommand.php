<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use PhpParser\Error;
use PhpParser\ParserFactory;
use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node;

use Stringy\Stringy as S;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class CreateConsoleCommand extends Command
{
    private $_output;

    protected function configure()
    {
        $this
            ->setName('console:create')
            ->setDescription('create console command and register it') 
        ;
    }

    private function createClass($commande,$description)
    {
        $output = $this->_output;

        $root = ROOT . DS;
        $app = $root . 'app' . DS . "Application";
        $lib = $root . 'library'. DS . "commands.php";

        // create command name
        $name = S::create($commande)->replace(':',' ')->toTitleCase()->replace(' ','')->append("Command")->__toString();
        // create FQN
        $fqn = "Application\\Commands\\" . $name;

        // check avaibality
        // load commands.php file
        $code = file_get_contents($lib);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP5);
        $prettyPrinter = new PrettyPrinter\Standard;
        $stmts = $parser->parse($code);
        foreach ($stmts[0]->expr as $express) 
        {
           $tmp =  $express[0]->value->value;
           if(S::create($tmp)->humanize()->__toString()==S::create($fqn)->humanize()->__toString())
           {
            $output->writeln("This command already exists in commands.php");
            die();
           }
        } 
                
        // commands not exists add it to commands.php
        $nb = count($stmts[0]->expr->items);
        $ligne  = 4 + $nb;
        $attributes = array("startLine"=>$ligne,"endLine"=>$ligne,"kind"=>2);
        $obj = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\String_($fqn,$attributes),null,false,$attributes);
        array_push($stmts[0]->expr->items,$obj);
        $code = $prettyPrinter->prettyPrint($stmts);
        $code = "<?php \r\n" . $code;

        $output->writeln("Create FQN commande ".$fqn);
       
        $path = $app . DS . "Commands" . DS . $name . ".php"; 

        $arg1 = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_($commande));
        $arg2 = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_($description));
        $arg3 = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_('Start process'));
        $arg4 = new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_('Finished'));

        $factory = new BuilderFactory;
        $node = $factory->namespace('Application\Commands')
                        ->addStmt($factory->use('Symfony\Component\Console\Command\Command'))
                        ->addStmt($factory->use('Symfony\Component\Console\Input\InputArgument'))
                        ->addStmt($factory->use('Symfony\Component\Console\Input\InputInterface'))
                        ->addStmt($factory->use('Symfony\Component\Console\Input\InputOption'))
                        ->addStmt($factory->use('Symfony\Component\Console\Output\OutputInterface')) 
                        ->addStmt($factory->class($name)->extend('Command')

                          ->addStmt($factory->method('configure')
                                ->makeProtected()                
                                ->addStmt(new Node\Expr\MethodCall(new Node\Expr\Variable('this'),"setName",array($arg1)))  
                                ->addStmt(new Node\Expr\MethodCall(new Node\Expr\Variable('this'),"setDescription",array($arg2)))  
                                )
                        ->addStmt($factory->method('execute')
                                ->makeProtected()       
                                ->addParam($factory->param('input')->setTypeHint('InputInterface'))
                                ->addParam($factory->param('output')->setTypeHint('OutputInterface'))         
                                ->addStmt(new Node\Expr\MethodCall(new Node\Expr\Variable('output'),"writeln",array($arg3)))  
                                ->addStmt(new Node\Expr\MethodCall(new Node\Expr\Variable('output'),"writeln",array($arg4)))
                                )
                       
                    )->getNode();

        $stmts = array($node);
        $prettyPrinter = new PrettyPrinter\Standard();
        $php = $prettyPrinter->prettyPrintFile($stmts); 
        file_put_contents($path,$php);

        $fs = new Filesystem();
        // if file exists add command to commands.php
        if($fs->exists($path))
        {
            $output->writeln("File saved in ".$path);
            $output->writeln("Register command to console");
            file_put_contents($lib,$code);
        }
        else
        {
            $output->writeln("File not created");
        }
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_output = $output;

        $output->writeln('Create new console command');

        $helper = $this->getHelper('question');
        $question = new Question('Please enter the command name : ', 'command:test');
        $name = $helper->ask($input, $output, $question);
        if($name!="")
        {
            if(S::create($name)->contains(":"))
            {
                $question2 = new Question('Please enter the command description : ', '');
                $description = $helper->ask($input, $output, $question2);                
                $this->createClass($name,$description);
            }
            else
            {
                $output->writeln("command name must contains : ");
            }
        }        
        $output->writeln('finished');
    }
}
<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Finder\Finder;

class DebugBarCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('debugbar:install')
            ->setDescription('Install all assets for debug bar') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {	
		$output->writeln('Install PhpDebugBar Assets');
		$fs = new Filesystem();
		$originDir = DEBUGBAR;
		$targetDir = ROOT . DS . "public" . DS . "dbg";
		
		try 
        {		
			$fs->mkdir($targetDir, 0777);
			$fs->mirror($originDir, $targetDir, Finder::create()->ignoreDotFiles(false)->in($originDir)); 		 
        } 
        catch (IOExceptionInterface $e) 
        {
            $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
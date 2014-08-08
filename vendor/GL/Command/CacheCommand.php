<?php

namespace GL\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class CacheCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clear all twig cache files') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
	$fs = new Filesystem();

		try {
			$path = CACHEPATH . DS . "twig";
			$fs->remove($path);			
			$fs->mkdir($path);
			$fs->chmod($path,0777,0000,true);
		} catch (IOExceptionInterface $e) {
			$output->writeln('Error');
		}		 
		$output->writeln('finished');
    }
}
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

class CacheCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clear all application cache files') 
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Clear all application cache files');
        $fs = new Filesystem();

        try 
        {
                $output->writeln("    Clear Twig Cache");

                $path = CACHEPATH . DS . "twig";
                $finder = new Finder();
                $iterator = $finder->files()->name('*.php')->in($path);
                $fs->remove($iterator);	
                $fs->remove($path);		
                $fs->mkdir($path);
                $fs->chmod($path,0777,0000,true);

                $output->writeln("    Clear DI Cache");

                $pathdi = CACHEPATH . DS . "DI" . DS . "Container";
                $finderdi = new Finder();
                $iteratordi = $finderdi->files()->name('*.php')->in($path);
                $fs->remove($iteratordi); 
                $fs->remove($pathdi);     
                $fs->mkdir($pathdi);
                $fs->chmod($pathdi,0777,0000,true);              
        } 
        catch (IOExceptionInterface $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }		 
        $output->writeln('finished');
    }
}
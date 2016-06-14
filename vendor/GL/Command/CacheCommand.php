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
                if($fs->exists($path))
                {
                    $fs->remove($iterator); 
                    $fs->remove($path);     
                }       
                $fs->mkdir($path);
                $fs->chmod($path,0777,0000,true);

                $output->writeln("    Clear Blade Cache");

                $path = CACHEPATH . DS . "blade";
                $finder = new Finder();
                $iterator = $finder->files()->name('*.php')->in($path);
                if($fs->exists($path))
                {
                    $fs->remove($iterator); 
                    $fs->remove($path);     
                }              
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

                 $output->writeln("    Clear route Cache");

                $pathroute = ROUTECACHE;
                $finderroute = new Finder();
                $iteratorroute = $finderroute->files()->name('*.php')->in($pathroute);
                $fs->remove($iteratorroute); 
                $fs->remove($pathroute);     
                $fs->mkdir($pathroute);
                $fs->chmod($pathroute,0777,0000,true);     

                 if(class_exists("\Kletellier\Assets\AssetsUtils"))
                {
                     $output->writeln("    Install Kletellier assets");
                    \Kletellier\Assets\AssetsUtils::install();
                     $output->writeln("    Install Kletellier assets Twig helper");
                    \Kletellier\Assets\AssetsUtils::verifyHelper();
                }     
                $output->writeln("    Create parameters cache file directory");
                $path = CACHEPATH . DS . "parameters";
                if(!$fs->exists($path))
                {
                    $fs->mkdir($path);
                }
                $fs->chmod($path,0777,0000,true);

        } 
        catch (IOExceptionInterface $e) 
        {
                $output->writeln('Error : ' . $e->getMessage());
        }        
        $output->writeln('finished');
    }
}
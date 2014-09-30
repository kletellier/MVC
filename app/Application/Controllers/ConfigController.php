<?php 
namespace Application\Controllers;

use GL\Core\Controller as Controller;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class ConfigController extends Controller
{
	public function database()
	{		  
		$request = $this->get('request');
		$helper = $this->get('request_helper');
		if($helper->isLocalClient()==false || DEVELOPMENT_ENVIRONMENT==false)
		{
			throw new \GL\Core\AccessDeniedHttpException;
		}
		 
		if($request->getMethod()=="POST")
		{
			 $server = $request->get('server');
			 $port = (int)$request->get('port');
			 $database = $request->get('database');
			 $user = $request->get('user');
			 $password = $request->get('password');
			 
			 $array  = array('server'=>$server,'port'=>$port,'database'=>$database,'user'=>$user,'password'=>$password);
			 $arr = array();
			 $arr["default"] = $array;
			 
			 $dumper = new Dumper();

			 $yamltxt = $dumper->dump($arr,2);
		 
			 if( file_put_contents(DATABASEPATH, $yamltxt)==FALSE)
			 {
				throw new \Exception('Error');
			 }
		}
		
		$yaml = new Parser();
		$value = $yaml->parse(file_get_contents(DATABASEPATH));		  
		return $this->render('database.html.twig',array('database'=>$value));
	}

	public function application()
	{
		$request = $this->get('request');
		$helper = $this->get('request_helper');
		if($helper->isLocalClient()==false || DEVELOPMENT_ENVIRONMENT==false)
		{
			throw new \GL\Core\AccessDeniedHttpException;
		}
		 
		if($request->getMethod()=="POST")
		{
			 $debug = ($request->get('debug')=='1');
			 $webpath = $request->get('webpath');
			 $locale = $request->get('locale') != "" ? $request->get('locale') : "en";
			 $cache = ($request->get('twigcache')=='1');
			 $engine = $request->get('engine') != "" ? $request->get('engine') : "twig";
			 $alwaysreload = ($request->get('alwaysreload')=='1');
			 
			 $twigarr = array('engine'=>$engine,'cache'=>$cache,'alwaysreload'=>$alwaysreload);			 
			 $array  = array('debug'=>$debug,'webpath'=>$webpath,'template'=>$twigarr,'locale'=>$locale);
			 
			 $dumper = new Dumper();

			 $yamltxt = $dumper->dump($array,2);
		 
			 if( file_put_contents(CONFIGPATH, $yamltxt)==FALSE)
			 {
				throw new \Exception('Error');
			 }
		}
		
		$yaml = new Parser();
		$value = $yaml->parse(file_get_contents(CONFIGPATH));		
		 
		return $this->render('application.html.twig',array('application'=>$value));	
	}
	
	public function mail()
	{
		$request = $this->get('request');
		$helper = $this->get('request_helper');
		if($helper->isLocalClient()==false || DEVELOPMENT_ENVIRONMENT==false)
		{
			throw new \GL\Core\AccessDeniedHttpException;
		}
		 
		if($request->getMethod()=="POST")
		{
			 $server = $request->get('server');
			 $port = (int)$request->get('port');			 
			 $user = $request->get('user');
			 $password = $request->get('password');
			 
			 $array  = array('server'=>$server,'port'=>$port,'user'=>$user,'password'=>$password);
			 $arr = array();
			 $arr["mail"] = $array;
			 
			 $dumper = new Dumper();

			 $yamltxt = $dumper->dump($arr,2);
		 
			 if( file_put_contents(MAILPATH, $yamltxt)==FALSE)
			 {
				throw new \Exception('Error');
			 }
		}
		
		$yaml = new Parser();
		$value = $yaml->parse(file_get_contents(MAILPATH));		
		 
		return $this->render('mail.html.twig',array('mail'=>$value));	
	}
        
}

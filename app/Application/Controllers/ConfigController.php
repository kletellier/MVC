<?php 
namespace Application\Controllers;

use GL\Core\Controller\Controller as Controller;
use GL\Core\Config\Config;

class ConfigController extends Controller
{
	public function database()
	{		  
		$request = $this->get('request');
		$helper = $this->get('request_helper');
		if($helper->isLocalClient()==false || DEVELOPMENT_ENVIRONMENT==false)
		{
			throw new \GL\Core\Exception\AccessDeniedHttpException;
		}
		$config = new Config('parameters'); 
		if($request->getMethod()=="POST")
		{
			$server = $request->get('server');
			$port = (int)$request->get('port');
			$database = $request->get('database');
			$user = $request->get('user');
			$password = $request->get('password');
			
			$tab = $config->load();
			$array  = array('server'=>$server,'port'=>$port,'database'=>$database,'user'=>$user,'password'=>$password);
			$tab["database"]["default"] = $array;			 
			
			$config->save($tab);			 
		}		
		 
		$value = \Parameters::get('database',true); //$config->load();	  
		return $this->renderTwig('database.html.twig',array('database'=>$value));
	}

	public function application()
	{
		$request = $this->get('request');
		$helper = $this->get('request_helper');
		if($helper->isLocalClient()==false || DEVELOPMENT_ENVIRONMENT==false)
		{
			throw new \GL\Core\Exception\AccessDeniedHttpException;
		}
		$config = new Config('parameters'); 
		if($request->getMethod()=="POST")
		{
			 $debug = ($request->get('debug')=='1');
			 $webpath = $request->get('webpath');
			 $locale = $request->get('locale') != "" ? $request->get('locale') : "en";
			 $cache = ($request->get('twigcache')=='1');
			 $engine = $request->get('engine') != "" ? $request->get('engine') : "twig";
			 $alwaysreload = ($request->get('alwaysreload')=='1');
			 
			 $tab = $config->load();
			 
			 $twigarr = array('engine'=>$engine,'cache'=>$cache,'alwaysreload'=>$alwaysreload);			 
			 $array  = array('debug'=>$debug,'webpath'=>$webpath,'template'=>$twigarr,'locale'=>$locale);
			 $tab["config"]  = $array;

			 $config->save($tab);	
		}
		
		$value = \Parameters::get('config',true);  		 
		return $this->renderTwig('application.html.twig',array('application'=>$value));	
	}
	
	public function mail()
	{
		$request = $this->get('request');
		$helper = $this->get('request_helper');
		if($helper->isLocalClient()==false || DEVELOPMENT_ENVIRONMENT==false)
		{
			throw new \GL\Core\Exception\AccessDeniedHttpException;
		}
		 $config = new Config('parameters'); 
		if($request->getMethod()=="POST")
		{
			 $server = $request->get('server');
			 $port = (int)$request->get('port');			 
			 $user = $request->get('user');
			 $password = $request->get('password');

			 $tab = $config->load();
			 
			 $array  = array('server'=>$server,'port'=>$port,'user'=>$user,'password'=>$password);
			 $arr = array();
			 $tab["mail"] = $array;
			 
			 $config->save($tab);
		}
		
		$value = \Parameters::get('mail',true); 
		return $this->renderTwig('mail.html.twig',array('mail'=>$value));	
	}
      
    private function renderTwig($tpl,$params,$code=200)
    {

        $tw = new \GL\Core\Twig\TwigService();
        $html = $tw->render($tpl,$params,\GL\Core\DI\ServiceProvider::GetDependencyContainer(),$this->_controller);
        $response = new \Symfony\Component\HttpFoundation\Response($html, $code, array('Content-Type' => 'text/html'));         
        return $response;
    }  
}

<?php

namespace GL\Core\Migration;

use GL\Core\Helpers\DbHelper as Db;
use Assert\Assertion;
use Assert\AssertionFailedException;
use GL\Core\Migration\MigrationModel;
use Symfony\Component\Finder\Finder;
use Stringy\Stringy;
use Carbon\Carbon;
use Pinq\ITraversable, Pinq\Traversable;
use Pinq;

class Migrator
{
	protected $migration_model = '<?php
 
namespace Migrations;

class ##classname## implements \GL\Core\Migration\MigrationInterface
{    
    public function getUniqueTag()
    {
        return "##key##";
    } 
   
    public function up()
    {
         
    }

    public function down()
    {
        
    }

    public function getCreationDate()
    {
        return "##date##";
    }
 
}';

	private function createBaseMigration()
	{
		$sch = DB::getSchema();         
        $tablename = "migrations";
             
        if(!$sch->hasTable($tablename))
        {
            $sch->create($tablename, function($table)
            {
                $table->increments('id');
                $table->string('class'); 
                $table->string('migration'); 
                $table->string('status');               
                $table->integer('db_version')->default(0);                 
                $table->timestamps();
            });
            
        }
	}

    private function getMigrationFiles()
    {
        $finder = new Finder();
        $finder->name('*Migration.php')->sortByName();
        return $finder->in(MIGRATIONPATH);
    }

    private function getClassName($filename)
    {
        return Stringy::create($filename)->removeRight(".php")->__toString();
    }

    public function getSlugKeyName($key)
    {
        return Stringy::create($key)->slugify()->upperCamelize()->__toString();
    }

	 public function create($key)
    {
    	$ret = false;
    	try 
    	{
    		$prefix = Carbon::now()->format('YmdHis');
    		$keys = $this->getSlugKeyName($key);
	        $classname = $keys."Migration";
	        $filename = MIGRATIONPATH . DS . $classname .".php";
	        $classtxt = Stringy::create($this->migration_model)->replace("##date##",$prefix)->replace('##classname##',$classname)->replace('##key##',$keys)->__toString();
	        file_put_contents($filename, $classtxt);
	        Assertion::file($filename);
	        $ret = true;
    	} catch (\Exception $e) {
    		$ret = false;
    	}
    	catch(AssertionFailedException $ex)
    	{
    		$ret = false;
    	}
       
    	return $ret;
    }

	public function testExist($key)
	{
		$test = MigrationModel::where("migration","=",$key)->get();
		return (count($test)>0);
	}
    

    public function migrateAll()
    {
    	 
    	$files = $this->getMigrationFiles();
        $classes = array();
		foreach ($files as $file) 
		{
			$filename = $file->getFilename();			 
			$classname = $this->getClassName($filename);           
		    $fqn =  "\Migrations\\$classname";	
            $classes[] = new $fqn;  
		}
        // tri en fonction de la fonction getCreationDate
        $classetrie = Traversable::from($classes)->orderByAscending(function ($row) {return $row->getCreationDate();});        
        foreach ($classetrie as   $value) 
        {
            $this->run($value,"up");          
        }

    }

    public function migrate($key)
    {
        try 
        {
            $files = $this->getMigrationFiles();
            foreach ($files as $file) 
            {
                $filename = $file->getFilename();            
                $classname = $this->getClassName($filename);           
                $fqn =  "\Migrations\\$classname";  
                $inst = new $fqn;
                if($inst->getUniqueTag()=="key")
                {
                     $this->run($inst,"up"); 
                     break; 
                }
            }     
             
        } 
        catch (Exception $e) 
        {
            
        }
    }

    public function rollback($key)
    {
    	$migration = MigrationModel::where('migration','=',$key)->first();
    	if($migration!=null)
    	{
    		$class = $migration->class;
            $instance = new $class;
    		$this->run($instance,"down");
    	}
    }

    private function run($instance,$type="up")
    {
    	// test if class exist and implement interface
        try 
        {
            $class = get_class($instance);

            Assertion::ClassExists($class);
            Assertion::implementsInterface($class,'\GL\Core\Migration\MigrationInterface');   
             
            $max = 0;
            
            // get all migrations in db
            $migrations = MigrationModel::all();

            // find max batch id version
            if(count($migrations)>0)
            {
            	$max = $migrations->max('batch');
            }

            $batchid = $max+1;
            // retrieve className
            $id = $instance->getUniqueTag();
            // find if this instance was not executed
            $exec = MigrationModel::where('migration','=',$id)->first();
            $bExec = true;
            if($exec!=null)
            {            	            	 
            	if($exec->status==$type)
            	{
            		// always executed
            		$bExec = false;
            	}   
            }             

            if($bExec)
            {
            	$instance->$type();
	        	// insert in db 
	        	if($exec==null)
	        	{
	        		$exec = new MigrationModel();	
	        	}        	
	        	$exec->class = $class;
	        	$exec->migration = $id;
	        	$exec->status = $type;
	        	$exec->db_version = $batchid;
	        	$exec->save();
            }           

        } 
        catch (\Exception $ex)
        {
        	echo $ex;             
        }
        catch (AssertionFailedException $e) 
        {
            echo $e;            
        }            
    }

    public function __construct()
    {
        $this->createBaseMigration();
    }
}
     
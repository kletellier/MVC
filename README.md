

## Introduction

A small PHP MVC Framework using Symfony components, Eloquent ORM, FPDF, Redis and PHPExcel Wrapper.

## Components

* Symfony
    * Http Foundation
    * Routing
    * Yaml
    * Dependency Injection
    * Filesystem
    * Finder
    * Stopwatch
    * Config
    * Console
* Illuminate
    * Eloquent
* Blade
* PHPMailer
* PSR Log
* Predis

## Objective

The objective was to create a small framework for understand how it works in internal, and also for using it in small projects.
 
 
## Installation


```
git clone https://github.com/kletellier/MVC.git /var/www
```   

1. Give write access to cache,upload and tmp folders

2. Launch ```composer update --prefer-dist``` for download all packages needed and create autoload script 

3. Create a new website on your webserver pointing on public folder

4. Change website configuration file

Open ```config/parameters.yml``` 

```yaml
config:
      debug: true
      webpath: ''
      template:
          engine: blade # section in template config below
          cache: false
          alwaysreload: true
      locale: en
database:
    default:
      server: 127.0.0.1
      port: 3306
      user: uid
      password: pwd
      database: dbname
templates: # classes must implement \GL\Core\Templating\TemplateServiceInterface
    twig:  
      class: \GL\Core\Twig\TwigService # need to install twig components
    blade:
      class: \GL\Core\Blade\Blade5Service
mail:
    server:  smtp.acme.com
    port: 587
    user: user
    password: pwd
    secure: 1
    encryption: "tls"
redis:
    default:
        server: 127.0.0.1
        port: 6379
        enable: 0
security:
    security:
         classes : GL\Core\Security\AuthenticationService
         roles_table : roles
         users_table : users
         usersroles_table : usersroles
    cookie:
         token : typeyoursecuritytokenhere
         name : REMEMBERME
         duration : 3600
    session:
         name: "kletellier"

```

The debug paramater display error description. Never use true in production website.

The webpath parameter is the root url of your website.

The template section set the engine used for rendering templates (default is blade).

The locale parameters is using for translator object (translation files are stored in lang folder).

in templates section, you can add your own tendering engine implementation, the class must implement \GL\Core\Templating\TemplateServiceInterface interface.

If you want using twig, just add twig in your composer.json and change type from blade to twig in config/template/engine section.

The mail section provide all parameters for mail sending. You can send mail by using mailer object in container. (See GL\Core\Tools\Mailer class). 

The redis section provide all parameters for using redis cache system.

The security section : 
    
    - specify a security class who implement GL\Core\Security\AuthenticationServiceInterface , by default GL\Core\Security\AuthenticationService. 
    - You must create table by using console security:create
    - Select a cookie session name, duration 

5. The route file configuration

Open ```config/routes.yml```

```yaml
 pdf:
    pattern:  /pdf
    controller: default
    action: pdf  
xls:
    pattern:  /xls
    controller: default
    action: xls    
testdb:
    pattern:  /testdb
    controller: default
    action: testdb
root:
    pattern: /{name}
    controller: default
    action: hello
    defaults : { name : "World" }

```

Each route must have a key name (the section name), and must include pattern,controller and action. You can add optional value bye adding variable between bracket in pattern section, and also you must add defaults section and define default value for each optional parameter. The position is very important, for determining selected route, the parser select the first url pattern matching !!!

## How it works

### routing

We type http://localhost/xls in our browser, the routing component detect you're using ```xls``` route, the controller was ```default``` and the action to execute ```xls```.

If no route matched, 404 action of ErrorController was executed.

### controller

All your controller are class that inherit from ```\GL\Core\Controller\Controller```.
You must store this file with a normalized name as ControllernameController.php in the ```/app/Application/Controllers``` folder.
 
In our example DefaultController.php. 

After this the controller resolver component will try to instanciate this controller and try execute the action , in our case the xls function.

The controller must return  ```Symfony/Component/HttpFoundation/Response``` via render methods proposed by abstract controller class.

By using 

```php
return $this->render(«Hello») ;

```

return a Http Response with 200 status code, with Hello text.

Controller embed many render functions, like renderJSON for rendering all objects as JSON string.

You can also redirect to other action/controller by using :

```php
$this->redirect(«routename»,array(« param »=> value)) ; // routename is a route name defined in routes.yml

```

### template

And also you have render function for using Blade Template engine.

```php
return $this->render('index',array(« params »=> « value »)) ;
```

The render function submit all params provided in array to the template file index.blade.php.

All templates are stored in ```app/Application/Views```

You can add some subfolders in Views for each controller, like Default folder for default controller.

By default, the template engine will take the template file in controller folder (in our case ```app/Application/Views/Default```) , if it will be not found, it will try to find them in Views root folder (```app/Application/Views``` ).

Render function return an ```Symfony/Component/HttpFoundation/Response```, you can specify Http Status Code (by default 200) , and add headers as key-value array with overloaded methods.

If you only want the Html, you can use :

```php
return $this->renderHtmlTemplate('index',array(« params »=> « value »)) ;
```

That return only raw Html.

All documentation about Blade are here [Blade] (https://laravel.com/docs/5.1/blade).

I've add an url function, for retrieve absolute url form relative url, based on webpath parameters in ```config/parameters.yml```

```
{{ Utils::url('/xls') }}
```

give http://localhost/xls

You can use console with this command for clearing cache: ```php console cache:clear```.

You can add your own method in Blade by adding @use(\Application\Classes\MyClass) in the template and calling  ```{{ MyClass:MyMethod($data) }}```.

If you doesn't want use Blade you can put PHP file in views folder and use 

```php
return $this->renderPHP('index.php',array(« params »=> « value »)) ;
```

it works like an include file.

### Dependency injection

Each controller instance own his DI container, you can retrieve each service on this container, by using get function :

```php $this->get('xls') // will give you PhpExcel Object ready to works ```

You have many services on each container :

* mailer : ```\GL\Core\Tools\Mailer``` instance, a wrapper of PHPMailer.
* request : ```Symfony\Component\HttpFoundation\Request``` instance.
* request_helper : ```GL\Core\Helpers\RequestHelper``` instance.
* template : ```GL\Core\Templating\TemplateProvider``` instance.
* routes : ```Symfony\Component\Routing\RouteCollection``` all routes defined in ```config/routes.yml```.
* pdf : ```GL\Core\Tools\PDF``` instance, wrapper of TCPDF.
* excel : ```GL\Core\Tools\Excel``` instance, wrapper of PhpExcel.
* session : ```Symfony\Component\HttpFoundation\Session\Session``` instance.
* crsf : ```GL\Core\Security\FormCrsf``` instance.
* translator : ```GL\Core\Config\Translator``` instance.
* security : ```GL\Core\Security\SecurityService``` instance.
* debug : ```GL\Core\Debug\KLDebugBar``` instance (debug bar).
* pdo : PDO instance of started database.
* config : ```GL\Core\Config\Config``` instance for reading yml config files.
* db : ```GL\Core\Helpers\DbHelper``` instance for database interactions.
* redis : ```GL\Core\Tools\Redis``` instance, wrapper of Predis.

You can add your own services in DI container by adding reference in ```config/services.yml```.

This is Symfony yml format more information here : 

http://symfony.com/doc/current/components/dependency_injection/introduction.html


### Eloquent ORM

You can create models in ```app/Application/Models``` folder.

In example, you have test table in your database.

You create a PHP Class Test who inherit from ```Illuminate\Database\Eloquent\Model```.

You store it in ```app/Application/Models/Test.php```

```php
namespace Application\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model {
    protected $table = 'test';
    public $timestamps = false;
}
```

In your controller you can access all data in this table,

```php

// retrieve all entries from test table 
$tests = Test::all() ;

// retrive only few entries
$test2 = Test::where('column','=','value') ;
```

For more information about Eloquent ORM :

[Eloquent ORM] (http://laravel.com/docs/eloquent)


## Documentation

For Symfony Component : 

[Symfony] (http://symfony.com/fr/components)

For Eloquent ORM : 

[Eloquent ORM] (http://laravel.com/docs/eloquent)

For Blade :

[Blade] (https://laravel.com/docs/5.1/blade)

For PHPMailer :

[PHPMailer] (https://github.com/PHPMailer/PHPMailer)


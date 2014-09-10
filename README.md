

## Introduction

A small PHP MVC Framework using Symfony components, Eloquent ORM, FPDF and PHPExcel Wrapper.

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
* PhpOfffice
	* PhpExcel
* Fpdf
* Twig
* Swiftmailer
* PSR Log

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

Open ```config/config.yml``` or type in your browser  ```http://yourwebsite/configuration/application``` (available only in local and debug mode)

```yaml
debug: true
webpath: http://localhost
twig:
    cache: false
    alwaysreload: true

```

The debug paramater display error description. Never use true in production website.

The webpath parameter is the root url of your website.

In twig section, enable cache in ```cache/twig``` folder, alwaysreload parameter  recreate cache for every request.

5. Change database configuration file

Open ```config/database.yml``` or type in your browser  ```http://yourwebsite/configuration/database``` (available only in local and debug mode)

```yaml
default:
    server: 127.0.0.1
    port: 3306
    user: usr
    password: pwd
    database: test
```

Actually framework will only work with MySQL/MariaDb Database.
You can connect many databases, just create a new section after default section in the config/database.yml

6. Change email configuration file or type in your browser  ```http://yourwebsite/configuration/mail``` (available only in local and debug mode)


Open ```config/mail.yml```

```yaml
mail:
    server:  smtp.acme.com
    port: 25
    user: user
    password: pwd
```

Setup your mail server configuration.

7. The route file configuration

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

All your controller are class that inherit from ```\GL\Core\Controller```.
You must store this file with a normalized name as ControllernameController.php in the ```/app/Application/Controllers``` folder.
 
In our example DefaultController.php. 

After this the controller resolver component will try to instanciate this controller and try execute the action , in our case the xls function.

The controller can return Html ou ```Symfony/Component/HttpFoundation/Response```.

By using 

```php
$this->render(«Hello») ;

```

return a Http Response with 200 status code, with Hello text.

Controller embed many render functions, like renderJSON for rendering all objects as JSON string.

You can also redirect to other action/controller by using :

```php
$this->redirect(«routename»,array(« param »=> value)) ; // routename is a route name defined in routes.yml

```

### template

And also you have render function for using Twig Template engine.

```php
$this->render('index.html.twig',array(« params »=> « value »)) ;
```

The render function submit all params provided in array to the template file index.html.twig.

All templates are stored in ```app/Application/Views```

You can add some subfolders in Views for each controller, like Default folder for default controller.

By default, the template engine will take the template file in controller folder (in our case ```app/Application/Views/Default```) , if it will be not found, it will try to find them in Views root folder (```app/Application/Views``` ).

Render function return an ```Symfony/Component/HttpFoundation/Response```, you can specify Http Status Code (by default 200) , and add headers as key-value array with overloaded methods.

If you only want the Html, you can use :

```php
$this->renderHtmlTemplate('index.html.twig',array(« params »=> « value »)) ;
```

That return only raw Html.

All documentation about Twig are here [Twig] (http://twig.sensiolabs.org/documentation).

I've add an url function, for retrieve absolute url form relative url, based on webpath parameters in ```config/config.yml```

```twig
{{ url('/xls') }}
```

give http://localhost/xls

You can also render controller action :

```twig
{ % render « controller::action »,{« params » : « value »} %}
```

Which include the Html provided by executing this action in this controller.

Your function in your controller must use ```php $this->renderHtmlTemplate ```

If you enable Twig cache, don't forget to delete all files in ```cache\twig``` for recreating cache version.

You can use console with this command for clearing cache: ```php app/console cache:clear```.

You can add your own method in Twig with ```app/Application/Shared/SharedTwigHelper.php```.

If you doesn't want use Twig you can put PHP file in views folder and use 

```php
$this->renderPHP('index.php',array(« params »=> « value »)) ;
```

it works as an include file.

### Dependency injection

Each controller instance own his DI container, you can retrieve each service on this container, by using get function :

```php $this->get('xls') // will give you PhpExcel Object ready to works ```

You have many services on each container :

* mailer : ```\GL\Core\Mailer``` instance, a wrapper of SwiftMailer.
* request : ```Symfony\Component\HttpFoundation\Request``` instance.
* request_helper : ```GL\Core\RequestHelper``` instance.
* twig : Twig Environnment instance.
* routes : ```Symfony\Component\Routing\RouteCollection``` all routes defined in ```config/routes.yml```.
* pdf : ```GL\Core\PDF``` instance, wrapper of fPDF.
* excel : ```GL\Core\Excel``` instance, wrapper of PhpExcel.
* session : ```Symfony\Component\HttpFoundation\Session\Session``` instance.
* crsf : ```GL\Core\FormCrsf``` instance.
* translator : ```GL\Core\Translator``` instance.
* security : ```GL\Core\SecurityService``` instance.

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

For Twig :

[Twig] (http://twig.sensiolabs.org/documentation)

For SwiftMailer :

[Swiftmailer] (http://swiftmailer.org/docs/introduction.html)

For Fpdf :

[fpdf] (http://www.fpdf.org/)

For PHPExcel :

[phpExcel] (https://github.com/PHPOffice/PHPExcel)


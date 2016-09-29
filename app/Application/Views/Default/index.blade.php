@use(GL\Core\Blade\Utils)
<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <title>Hello World</title>        
    </head>
    <body>         
       {{ $text }}  
       <br>
       {{ Utils::trans("section.test3") }} = {{ Utils::getFrenchDate($date) }}  
       <br>
       {{ Utils::trans("section.test") }}&nbsp;{{ Utils::trans("section.test2") }}<br>      
    </body>
</html>
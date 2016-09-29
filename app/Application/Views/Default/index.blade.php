@use(GL\Core\Blade\Utils)
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hello World</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" />
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <style>
  .ombre
  {         
      border: 1px solid #222222;
      box-shadow: 1px 1px 12px #555;
      background-color: #FFFFFF;
  }
  </style>
</head>
<body>        
<div class="container">
  <div class="row">
      <div class="col-xs-12 text-center">
          <h2>{{ Utils::trans("section.test") }}</h2>
      </div>
  </div>
  <div class="row">
      <div class="col-xs-12 text-center">
          @include('logo')
      </div>
  </div>
  <div class="row">
      <div class="col-xs-12 text-center">
          <h5>{{ Utils::trans("section.test2") }} = {{ Utils::getFrenchDate($date) }}  </h5>
      </div>
  </div>
</div> 
   
</body>
</html>
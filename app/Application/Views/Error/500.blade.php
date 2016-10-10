@use(GL\Core\Blade\Utils)
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{ "error.500title")}}</title>
<link rel="stylesheet" href="{{ Utils::url('error/css/bootstrap.min.css') }}">
    <script src="{{ Utils::url('error/js/jquery-1.9.1.js')}}"></script>
    <script src="{{ Utils::url('error/js/bootstrap.min.js')}}"></script>
</head>
<body>      
<div class="container-fluid">  
<div id="body" class="col-xs-offset-3 col-xs-6 col-sm-offset-3 col-sm-6">
@if($message!=null)
    <div class="row">&nbsp;</div>
    <div class="row col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1">
        <div class="panel panel-danger">
          <div class="panel-heading" >
            <h3 class="panel-title " id="titre" >{{ Utils::trans("error.500warnings")}}</h3>
          </div>
          <div id="panel-body" class="panel-body" >
              <h4 id="contenu">{{ $message }}</h4>
             @if($line!=null) <h5 id="contenu">{{ Utils::trans("global.linenumber")}} {{ $line }}</h5> @endif
             @if($file!=null) <h5 id="contenu">{{ Utils::trans("global.file")}} {{ $file }}</h5> @endif
          </div>
        </div>
    </div>
@endif
@if($errors!=null)
        <table class="table table-striped table-condensed table-bordered table-hover" id="liste">
        <thead>
              <tr>
                <td>{{ Utils::trans("global.message")}}</td> 
                <td>{{ Utils::trans("global.line")}}</td>   
                <td>{{ Utils::trans("global.file")}}</td>
              </tr>
        </thead>
        <tbody>
              @foreach($errors as $tmp)
              <tr>
                    <td>{!! $tmp["message"] !!}</td>
                    <td>{!! $tmp["line"] !!}</td>
                    <td>{!! $tmp["file"] !!}</td>
              </tr>             
             @endforeach
            </tbody>
	</table>
   @endif
</div>	 
</div>
</body>
</html>

 
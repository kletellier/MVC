<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>{{ $error_code }} - {{ $error_name }}</title>
		<style>.errorBox,a{color:#333}.errorBox,body{position:relative}.links ul li:before,.tips ul li:before{content:"\00BB\00A0"}*{margin:0;padding:0}body,html{min-height:100%}body{font-size:11px;font-family:Verdana,sans-serif;background:#0a2b4f;background:-moz-linear-gradient(top,#0a2b4f 0,#539ab8 100%);background:-webkit-gradient(linear,left top,left bottom,color-stop(0,#0a2b4f),color-stop(100%,#539ab8));background:-webkit-linear-gradient(top,#0a2b4f 0,#539ab8 100%);background:-o-linear-gradient(top,#0a2b4f 0,#539ab8 100%);background:-ms-linear-gradient(top,#0a2b4f 0,#539ab8 100%);background:linear-gradient(to bottom,#0a2b4f 0,#539ab8 100%)}.errorBody{min-height:780px;padding-top:120px}.errorBox{margin-left:auto;margin-right:auto;width:880px;height:300px;padding:40px;background-color:rgba(255,255,255,.9);border-radius:15px;box-shadow:3px 3px 10px #333}.statusCode,.statusText{width:320px;text-align:center;color:#555}.errorDescription,.todo{position:absolute;width:490px}.statusCode{margin-top:40px;font-size:148px;font-weight:700}.statusText{font-size:24px}.errorDescription{top:40px;right:40px;height:300px;padding-left:30px;border-left:1px solid #e0e0e0}.todo{bottom:0}@media (max-width:980px){.errorDescription,.todo{position:relative;width:auto}.errorBody{padding-top:0}.errorBox{width:auto;height:auto;margin:10px}.statusCode{margin-top:0}.description,h2{margin-top:16px}.statusCode,.statusText{width:auto;text-align:center}.errorDescription{top:inherit;right:inherit;height:auto;padding-left:0;border-left:0}.todo{bottom:inherit}.description{height:auto!important}}@media (max-width:430px){.statusCode{font-size:72px}}.description{line-height:16px;height:100px}h1.title{display:none}h2{font-size:11px;margin-bottom:20px}.links ul li,.tips ul li{line-height:27px;margin-right:20px;margin-bottom:-1px;border-top:1px solid #b3c6cc;border-bottom:1px solid #b3c6cc;list-style-type:none}.links ul li{float:left;height:27px;width:220px}.links ul li a{text-decoration:none;color:#333}.links ul li a:hover{text-decoration:underline}</style>
	</head>
	<body>
		<h1 class="title">			
			<span class="en" id="title-en">{{ $error_name }}</span>
		 
		</h1>
		<div class="errorBody">
			<div class="errorBox">
				 
				<div class="errorData">
					<div class="statusCode">{{ $error_code }}</div>
					<div class="statusText">
						
						<div class="en">{{ $error_status }}</div>
					  
					</div>
				</div>
				<div class="errorDescription">
					<div class="description">
						
						<div class="en">
							{{ $error_description }}
							<br>
							More details here : <a href="{{ $error_details }}">{{ $error_details }}</a>
						</div>
					 
					</div>
					 
				</div>
			</div>
		</div>
		 
	</body>
</html>
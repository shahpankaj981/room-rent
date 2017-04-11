<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
		Welcome to Room-rent app, {{$user->name}}!!!!! <br>
		Please follow the following link to activate your account:
		<a href='http://roomrent.dev/api/registration/{{$user->confirmationCode}}'>roomrent.dev/api/registration/{{$user->confirmationCode}}</a>
</body>
</html>
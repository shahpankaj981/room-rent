<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	Hi {{$user->name}}, <br>
	We received a request to reset your Room-rent app password. 


		Welcome to Room-rent app, {{$user->name}}!!!!! <br>
		Please follow the following link to reset your password:
		<a href="{{ route('recoverPassword', ['email'=>$user->email, 'forgotPasswordToken'=>$user->forgotPasswordToken ]) }}">Reset Your Password</a>
</body>
</html>
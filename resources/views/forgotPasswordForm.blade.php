<!DOCTYPE html>
<html>
<head>
	<title>Reset Password</title>
</head>
<body>
	<form method = "post" action="http://roomrent.dev:81/api/savePassword">
	{{csrf_token()}}
		<h1>Reset Your Password</h1>
		<pre>New Password: <input type="password" name="password" id="password"> </pre>
		<pre>Confirm Password: <input type ="password" name="confirmPassword" id="confirmPassword"></pre>
		<input type = "hidden" value = {{$user->email}} name="email">
		<input type = "submit" value = "Submit" onclick="return validate()" />
	</form>

	<script type="text/javascript"> 
	function validate(){
		var password = document.getElementById('password').value;
		var confirm = document.getElementById('confirmPassword'.value;
			if (password != confirmPassword) {
				alert ('Passwords do not match');
				return false;
			}
			return true;
	}
	</script>
</body>
</html>
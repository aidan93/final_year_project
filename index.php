<?php
include('login.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>UUJ Electronic Noticeboard</title>
	<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
	<div id="main">
		<h1>Login to UUJ Electronic Noticeboard</h1>
		<div id="login">
			<h2>Login Form</h2>
			<form action="" method="post">
				<label>Username:</label>
				<input id="username" name="username" placeholder="username" type="text">
				<label>Password:</label>
				<input id="password" name="password" placeholder="********" type="password">
				<input name="submit" type="submit" value="Login">
				<span><?php echo $error; ?></span>
			</form>
		</div>
	</div>
</body>
</html>
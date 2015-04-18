<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/project/controller/login.php');

?>

<!DOCTYPE html>
<html>
<head>
	<title>UU Electronic Noticeboard</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link href="/project/style/style.css" rel="stylesheet" type="text/css">
</head>
<body class="login">
	<div id="main">
		<div id="login_form">
			<img src="/project/images/logo.png" id="login_logo"/>
			<h1>Login to UU Electronic Noticeboard</h1>
			<?php echo $error; ?>
			<form action="" method="post">
				<input id="username" name="username" placeholder="Username" type="text" required>
				<input id="password" name="password" placeholder="********" type="password" required>
				<input name="submit" type="submit" value="Login">
			</form>

			<div class="login-help">
			   	<a href="https://login.ulster.ac.uk/password/">Forgot Password</a>
			</div>
		</div>
	</div>
</body>
</html>
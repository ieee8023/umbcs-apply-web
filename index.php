<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);



function generateVerify($username, $email, $password, $time){
	
	$salt = "UMBCS";
	
	return crypt($username."-".$email."-".$password."-".$time,$salt);
	
}


//from https://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
function startsWith($haystack, $needle) {
	// search backwards starting from haystack length characters from the end
	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
function endsWith($haystack, $needle) {
	// search forward starting from end minus needle length characters
	return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
}


function usernameValid($username){

	$pattern = "^[a-z0-9]+$";
	return ereg($pattern, $username);
}

function usernameTaken($username){
	
	if (usernameValid($username)){
	
		$pathToHome = "/home/";
		$userpath = $pathToHome.$username;
		
		$val = exec("ls -d ".$userpath);

		if (!empty($val)){
			return ($val == $userpath);
		}else{
			return false;
		}
	}
	
	return false;
}


if ($_GET['usernameok']){

	$username = $_GET['usernameok'];
	
	$taken = "false";
	$valid = usernameValid($username);
	if ($valid){
		$taken = usernameTaken($username);
	}
	
	// convert to strings
	$taken = $taken?"true":"false";
	$valid = $valid?"true":"false";
	
	echo '{"taken": '.$taken.', "valid": '.$valid.' }';
	
	
	return;

}


if ($_POST['email'] && $_POST['username'] && $_POST['password']){

	
	$email = $_POST['email'];
	$username = $_POST['username'];
	$password = $_POST['password'];
	$time = time();
	$verify = generateVerify($username, $email, $password, $now);
	
	
	// check if UMB email
	if (!endsWith($email,"@umb.edu")){
		die("Email must end in @umb.edu");
	}
	
	// check valid username
	if (!usernameValid($username)){
		die("Username is not valid");
	}
	
	// check username not taken
	if (usernameTaken($username)){
		die("Username is already taken");
	}
	
	
	$verify = "http://www.cs.umb.edu/~joecohen/apply/web/?email=$email&username=$username&password=$password&time=$time&verify=$verify";
	
	
	$to      = $email;
	$subject = 'Please Verify Your Account';
	
	$message = '<html><body>';
	$message .= 'Hello,<br>';
	$message .= "<a href='$verify'>Click here to verify your account</a> <br>or go to this URL:";
	$message .= "$verify";
	$message .= "</body></html>";
	
	$headers = 'From: UMB CS Apply System <joecohen@cs.umb.edu>' . "\r\n" .
			'Reply-To: joecohen@cs.umb.edu' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
	//echo $message;
	
	mail($to, $subject, $message, $headers);
	
	echo "Thanks, email is on its way. Just click the link inside.";
	return;
	
}

if ($_GET['email'] && $_GET['verify'] && $_GET['username'] && $_GET['password'] && $_GET['time']){

	$email = $_GET['email'];
	$username = $_GET['username'];
	$password = $_GET['password'];
	$time = $_GET['time'];
	$verify = $_GET['verify'];
	
	// check if too old
	
	
	
	//check if verify hash works
	
	$correctverify = generateVerify($username, $email, $password, $time);
	
	if ($verify != $correctverify){
		
		die("Verify missmatch");
	}
	
	
	// check for value username
	
	
	
	//check for valid email format
	
	
	try {
		$myfile = fopen("requests/".$username, "w") or die("Unable to write to filesystem!");;
		
		$txt = "$username\n$email\n$password\n$time\n";

		fwrite($myfile, $txt);
		fclose($myfile);
	} catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	
	
	echo "Verified account for ".$username;

	return;
}








?>
<html>
<head>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>


<script src="pwd.js"></script>
<script>

function processPassword(){

	var randomsalt = random_salt(8);
	var passwordraw = $("#passwordraw").val();
	
	//console.log(md5crypt("hello",randomsalt));
	$("#password").val(md5crypt(passwordraw,randomsalt));
}


function validateUsername(){
	username = $("#username").val();

	if (username == ""){

		$("#usernameinput").removeClass("has-error");
		$("#usernameerrortext").html();
		return;
	}

	
	$.getJSON( "?usernameok=" + username, function( data ) {

		console.log(data.taken);
		
			if (data.taken || !data.valid){
				$("#usernameinput").addClass("has-error");
			}else{
				$("#usernameinput").removeClass("has-error");
				$("#usernameerrortext").html('<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Looks Good!');
				
			}

			if (data.taken){
				$("#usernameerrortext").html('<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Taken');
			}

			if (!data.valid){
				$("#usernameerrortext").html('<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Not Valid');
			}
	});
}


function validateEmail(){
	email = $("#email").val();

	if (email == ""){
		
		$("#emailerrortext").html();
		$("#emailinput").removeClass("has-error");
		return;
	}

	if (endsWith(email,"@umb.edu")){
		$("#emailinput").removeClass("has-error");
		$("#emailerrortext").html('<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Looks Good!');
	}else{
		$("#emailinput").addClass("has-error");
		$("#emailerrortext").html('<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Must be a @umb.edu email');
	}
}

function endsWith(str, suffix) {
    return str.indexOf(suffix, str.length - suffix.length) !== -1;
}

$(function(){
	
	processPassword();
	validateUsername();
	validateEmail();
	
	$("#passwordraw").keyup(processPassword);

	$("#username").keyup(function(){

		validateUsername();
	});

	$("#email").keyup(function(){

		validateEmail();
	});
	
});

</script>

<title>UMB CS Account Management</title>

</head>
<body>

</body>
</html>
    <nav class="navbar navbar-default navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">UMB CS Account Management</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li class="active"><a href="#">Apply</a></li>
            <li><a href="#">Reset Password</a></li>
            <li><a href="mailto:operator@cs.umb.edu?subject=Help with UNIX account">Help</a></li>
          </ul>
        </div>
      </div>
    </nav>




<div class="container">

      <div class="jumbotron">
        <h2>How to apply for a UNIX account</h2>
        <p>
	        <ol>
		        <li>Pick a username and password.</li>
		        <li>Verify UMB email account. (this links the accounts)</li>
		        <li>Wait to receive confirmation email.</li>
		        <li>Symlinks for your courses will now appear in your home folder.</li>
		        <li>Learn everything and change the world.</li>
	        </ol>
        </p>
      </div>



	<form class="form-horizontal" role="form" action="#" method="POST">
	
	
	<div id="usernameinput" class="form-group has-feedback">
	    	<label class="control-label col-sm-2" for="email">Desired Username:</label>
	    <div class="col-sm-10">
	    	<div class="input-group">
		        <input  type="username" class="form-control" id="username" name="username" placeholder="Enter username here. It will become the email username@cs.umb.edu.">
		    	<span id="usernameerrortext" class="input-group-addon"></span>
	    	</div>
	    </div>
	</div>	  
	  
	  
	<div class="form-group">
	    <label class="control-label col-sm-2" for="pwdraw">Password:</label>
	    <div class="col-sm-10"> 
	      <input type="password" class="form-control" id="passwordraw" name="passwordraw" placeholder="Enter password">
	    </div>
	</div>
	
	<div class="form-group">
	    <label class="control-label col-sm-2" for="pwd">Password Hash:</label>
	    <div class="col-sm-10"> 
	      <input readonly=true  class="form-control" id="password" name="password" placeholder="Please enable JavaScript">
	    </div>
	</div>
	
	  <div id="emailinput" class="form-group">
	    <label class="control-label col-sm-2" for="email">UMB Email:</label>
	    <div class="col-sm-10">
	    	<div class="input-group">
	      	<input type="email" class="form-control" id="email" name="email" placeholder="Enter UMB email (including @umb.edu)">
			<span id="emailerrortext" class="input-group-addon"></span>
			</div>
	    </div>
	  </div>

	  <div class="form-group"> 
	    <div class="col-sm-offset-2 col-sm-10">
	      <button type="submit" class="btn btn-default">Submit</button>
	    </div>
	  </div>
	</form>
</div>


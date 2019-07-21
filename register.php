<?php require('server.php');?>
<?php require('header.php');?>
<?php
	//if register button is clicked
	if(isset($_POST['register'])) {
		$emptype = mysqli_real_escape_string($db, $_POST['emptype']);
		$empcode = mysqli_real_escape_string($db, $_POST['empcode']);
		$empname = mysqli_real_escape_string($db, $_POST['empname']);
		$empmail = mysqli_real_escape_string($db, $_POST['empmail']);
		$password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
  		$password_2 = mysqli_real_escape_string($db, $_POST['password_2']);


		//ensure form fields are properly filled
		if(empty($empcode) OR empty($empname) OR empty($empmail)){
			array_push($errors, "Code,Name and Mail are required");
		}

		if ($password_1 != $password_2) {
			array_push($errors, "The two passwords do not match");
		  }

		// a user does not already exist with the same code
		  $user_check_query = "SELECT * FROM users WHERE  empcode='$empcode' LIMIT 1";
		  $result = mysqli_query($db, $user_check_query);
		  $user = mysqli_fetch_assoc($result);
		  
		  if ($user) { // if user exists
		    if ($user['empcode'] === $empcode) {
		    	//header("location:register.php");
		      	array_push($errors, "User already exists");
		    }
		  }
		//if no error , save user to database
		if(count($errors) == 0){
			$password = md5($password_1);

			$sql = "INSERT INTO users VALUES ('','$emptype','$empcode','$empname','$empmail','$password')" or die("Db insert failed") ;
			mysqli_query($db,$sql);
			array_push($pass, "Successfully registered!");
			header('refresh:2; location:login.php');
		}
	}
	?>

<body class="reg">
	<div class="text-center regscreen container">
		<h1>Register</h1>
		<form method="post" action="register.php">
			<!--display validation errors-->
			<?php require('errors.php');?>

			<div class="input-group">
				<label>Employee Type</label>
				<input type="text" name="emptype" value="<?php echo $emptype;?>">
			</div>

			<div class="input-group">
				<label>Employee Code</label>
				<input type="text" name="empcode" value="<?php echo $empcode;?>">
			</div>

			<div class="input-group">
				<label>Employee Name</label>
				<input type="text" name="empname" value="<?php echo $empname;?>">
			</div>
			
			<div class="input-group">
				<label>Employee Email</label>
				<input type="text" name="empmail" value="<?php echo $empmail;?>">
			</div>

			<div class="input-group">
		  	  <label>Password</label>
		  	  <input type="password" name="password_1">
		  	</div>

		  	<div class="input-group">
		  	  <label>Confirm password</label>
		  	  <input type="password" name="password_2">
		  	</div>

			<div class="input-group">
				<button type="submit" name="register" class="btn btn-primary mx-auto">Register</button>
			</div>
			
			<p> Already a member? <a href="login.php">Log in</a></p>
		</form>
	</div>


<?php require('footer.php');?>

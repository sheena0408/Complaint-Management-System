	<?php
		//users table
		$emptype = "";
		$empcode = $admincode = "";
		$empmail = "";
		$empname = "";

		$errors = array();
		$pass = array();
		
		//connect to db
		$db = mysqli_connect('localhost', 'root', '', 'comp_master');

		// LOGIN USER
		if (isset($_POST['login'])) {
		  $empcode = mysqli_real_escape_string($db,$_POST['empcode']);
		  $password = mysqli_real_escape_string($db, $_POST['password']);

		  if (empty($empcode)) {
		  	array_push($errors, "Emp Code is required");
		  }
		  if (empty($password)) {
		  	array_push($errors, "Password is required");
		  }
		 
		  if (count($errors) == 0) {
		  	$password = md5($password);
		  	$query = "SELECT * FROM users WHERE empcode='$empcode' AND password = '$password' AND emptype='user'";
		  	$results = mysqli_query($db, $query);

		  	if (mysqli_num_rows($results) == 1) {  			
		  		//$_SESSION['success'] = "You are now logged in";
		  		$_SESSION['empcode'] = $empcode;
		  		
		  		$query="SELECT * FROM users WHERE empcode='$empcode' AND password = '$password' AND emptype='user'";
				$r1 = mysqli_query($db, $query);
				while($result=mysqli_fetch_assoc($r1)){
					$_SESSION['empname'] = $result['empname'];
					$_SESSION['empmail'] = $result['empmail'];
					$_SESSION['emptype'] = $result['emptype'];
				}
				
				header('location: index.php');
				$_SESSION['success'] = "You are now logged in";
		  	}else {
		  		array_push($errors, "Wrong emp code/password");
		  	}
		  }
		}


		if (isset($_POST['admin-login'])) {
		  $admincode = mysqli_real_escape_string($db,$_POST['empcode']);
		  $password = mysqli_real_escape_string($db, $_POST['password']);

		  if (empty($admincode)) {
		  	array_push($errors, "Admin Code is required");
		  }
		  if (empty($password)) {
		  	array_push($errors, "Password is required");
		  }
		 
		  if (count($errors) == 0) {
		  	$password = md5($password);
		  	$query = "SELECT * FROM users WHERE empcode='$admincode' AND password = '$password' AND emptype='admin' ";
		  	$results = mysqli_query($db, $query);

		  	if (mysqli_num_rows($results) == 1) {  			
		  		$_SESSION['success'] = "You are now logged in";
		  		$_SESSION['admincode'] = $admincode;
		  		
		  		$query="SELECT * FROM users WHERE empcode='$admincode' AND password = '$password' AND emptype='admin'";
				$r1 = mysqli_query($db, $query);
				while($result=mysqli_fetch_assoc($r1)){
					$_SESSION['empname'] = $result['empname'];
					$_SESSION['empmail'] = $result['empmail'];
					
				}
				header('location: admin-index.php');
				$_SESSION['success'] = "Admin is logged in";
		  	}else {
		  		array_push($errors, "Wrong admin code/password");
		  	}
		  }
		}

		//logout
		if(isset($_GET['logout'])) {
			unset($_SESSION['empcode']); //removes session variables
			unset($_SESSION['empmail']);
			unset($_SESSION['empname']);
			session_destroy(); //destroys seesion
			
			//going back to main page
			header('location: main.php');
		}
	?>

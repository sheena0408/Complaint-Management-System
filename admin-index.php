<?php 
	session_start();
	require('server.php');

	$res_code = $res_name = $cdesc = $msg = $attachment = ""; 

	if(!isset($_SESSION['admincode'])){
		array_push($errors, "You must login/register first");
		header('location:main.php');
	}

	// delete complaint
	if (isset($_GET['del_comp'])) {
		$id = $_GET['del_comp'];
		$q = "UPDATE complaints SET cl_date=now(), status='Closed' WHERE id=$id";
		mysqli_query($db, $q);
		array_push($errors, "Complaint Closed");
	}

	if(isset($_POST['submit'])) {
		$res_code = mysqli_real_escape_string($db,$_POST['empcode']);
		$cdesc = mysqli_real_escape_string($db,$_POST['cdesc']);
		$msg = mysqli_real_escape_string($db,$_POST['message']);

		//attach upload
		$file = $_FILES["tfile"];
		if(is_uploaded_file($_FILES["tfile"]["tmp_name"])){
			//$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
			//$allowed = array('doc','docx','pdf','xl','xlsx');

			//if(in_array($ext,$allowed)) {
				if($file["error"] === 0) {
					move_uploaded_file($file["tmp_name"],"admin-uploads/". $file["name"]);
					$attachment = "admin-uploads/". $file["name"];
					array_push($pass,"Upload success");
				} 
				else{
					array_push($errors,"Error uploading this file");
				}
			//} else if(!in_array($ext,$allowed)){
				//	array_push($errors,"You can't upload files of this type");
			//}
		}

		if(empty($cdesc) OR empty($res_code) OR empty($msg)){
			array_push($errors, "You must fill in the fields");
		} 
		else {
			if(count($errors) == 0){
				$send_name = $_SESSION['empname'];
				$send_code = $_SESSION['admincode'];
				$rdate= date('Y-m-d h:i:s');

				$x="SELECT * FROM users WHERE empcode=$res_code";
				$xx = mysqli_query($db, $x);
				while($y=mysqli_fetch_assoc($xx)){
					$res_name = $y['empname'];
				}
		
				$sql = "INSERT INTO message VALUES ('','$send_code','$send_name','$res_code','$res_name', '$rdate', '$cdesc','$attachment','$msg')";
				mysqli_query($db,$sql);

				array_push($pass,"Message sent successfully");

			}
		}
	}
?>
<?php require('header.php');?>
<body class="ind">

	<?php if (isset($_SESSION['success'])): ?>
		<div class="error success">
			<h3>
				<?php
					echo $_SESSION['success'];
					unset($_SESSION['success']);
				?>
			</h3>
		</div>
	<?php endif ?>
	<!--page content-->
	<div class="content col-12">
		<?php if (isset($_SESSION['admincode'])): ?>
			
			<p class="welcome">Welcome <?php echo $_SESSION['empname']; echo "(Admin)";?>
			<a href="admin-index.php?logout='1'" class="logout btn btn-danger">Log out</a></p>

			<hr/>
			<hr/>

			<ul class="navigation">
				<li><button class="btn btn-info activeC">Active Complaints</button></li>	
				<li><button class="btn btn-info closeC">Closed Complaints</button></li>
				<li><button class="btn btn-info seemsg">Sent Messages</button></li>
				<li><button onclick="document.getElementById('id01').style.display='block'" class="btn btn-info float-right" style="margin-right: 0.8em;">Send Message</button></li>
			</ul>

			<div id="id01" class="modal">
				<form class="modal-content animate" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">

					<div class="input-group">
						<label>Employee Code</label>
						<input type="text" name="empcode">
					</div>

					<hr style="border: 1px solid #999; width: 100%;" />

					<div class="input-group">
						<label>Subject</label>
						<input type="text" name="cdesc" class="">
					</div>

					<div class="input-group">
						<label for="fileToUpload">Select file to upload</label>
						<input type="file" name="tfile" class="file_upload">
					</div>

					<div class="input-group">
						<label>Message Description</label>
						<textarea name="message" class=""></textarea>
					</div>

					<div class="input-group">
						<button type="submit" name="submit" class="btn btn-info mx-auto">Submit</button>
						<button type="submit" name="cancel" class="btn btn-danger mx-auto" onclick="document.getElementById('id01').style.display='none'">Cancel</button>
					</div>
				</form>
			</div>

			<div class="row">
				<div class="left_side col-9">
					<div class="err">
						<?php include('errors.php')?>
					</div>
					<div id="activeComp"><!--current users to do tasks-->
						<table class="table table-striped">
							<thead>
								<tr>
									<th>C No.</th>
									<th>Reg Name</th>
									<th>Reg Mail</th>
									<th>Desc</th>
									<th>Attachment</th>
									<th>R Date</th>
									<th>Comments</th>
									<th>Close complaint</th>
								</tr>
							</thead>

							<tbody>
								<?php 
								$q = "SELECT * FROM complaints WHERE status= 'Open'";
								$comp = mysqli_query($db,$q );

								while ($row = mysqli_fetch_array($comp)) { ?>
									<tr>
										
										<td class="task"> <?php echo $row['id']; ?> </td>
										<td class="task"> <?php echo $row['reg_name']; ?> </td>
										<td class="task"> <?php echo $row['reg_mail']; ?> </td>
										<td class="task"> <?php echo $row['c_desc']; ?> </td>
										<td class="task">
											<?php if(!empty($row['attach'])):?>
												<a href="<?php echo $row['attach']; ?>" target="_new">Download</a>
											<?php endif?>
										</td>
										<td class="task"> <?php echo $row['reg_date']; ?> </td>
										<td class="task"> <?php echo $row['comments']; ?> </td>
										<td class="delete"><a href="admin-index.php?del_comp=<?php echo $row['id'] ?>">x</a> </td>
									</tr>
								 <?php } ?>	
							</tbody>
						</table>
					</div>

					<div id="closeComp"><!--current users completed tasks-->
						<table class="table table-striped">
							<thead>
								<tr>
									<th>C No.</th>
									<th>Reg Name</th>
									<th>Reg Mail</th>
									<th>Desc</th>
									<th>R Date</th>
									<th>Closed date</th>
								</tr>
							</thead>

							<tbody>
								<?php 
								$q = "SELECT * FROM complaints WHERE status = 'Closed' ORDER BY cl_date DESC";
								$comp = mysqli_query($db,$q);

								while ($row = mysqli_fetch_array($comp)) { ?>
									<tr>
										<td class="task"> <?php echo $row['id']; ?> </td>
										<td class="task"> <?php echo $row['reg_name']; ?> </td>
										<td class="task"> <?php echo $row['reg_mail']; ?> </td>
										<td class="task"> <?php echo $row['c_desc']; ?> </td>
										<td class="task"> <?php echo $row['reg_date']; ?> </td>
										<td class="task"> <?php echo $row['cl_date']; ?> </td>
									</tr>
								 <?php } ?>	
							</tbody>
						</table>
					</div>

					<div id="msg"><!--current users to do tasks-->
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Msg No.</th>
									<th>Reciever Code</th>
									<th>Reciever Name</th>
									<th>Sent on</th>
									<th>Subject</th>
									<th>Attachment</th>
									<th>Message Body</th>
								</tr>
							</thead>

							<tbody>
								<?php 
								$q = "SELECT * FROM message WHERE sender_code = '{$_SESSION['admincode']}'";
								$comp = mysqli_query($db,$q );

								while ($row = mysqli_fetch_array($comp)) { ?>
									<tr>
										<td class="task"> <?php echo $row['id']; ?> </td>
										<td class="task"> <?php echo $row['rec_code']; ?> </td>
										<td class="task"> <?php echo $row['rec_name']; ?> </td>
										<td class="task"> <?php echo $row['send_date']; ?> </td>
										<td class="task"> <?php echo $row['cdesc']; ?> </td>
										<td class="task">
											<?php if(!empty($row['attach'])):?>
												<a href="<?php echo $row['attach']; ?>" target="_new">Download</a>
											<?php endif?>
										</td>
										<td class="task"> <?php echo $row['msg']; ?> </td>
									</tr>
								 <?php } ?>	
							</tbody>
						</table>
					</div>

				</div>

				<div class="right_side col-3">
					<div class="infoBits text-center">
						<div class="cntAct">
							<d3>ACTIVE COMPLAINTS</d3>
							<h1><?php 
									$q = "SELECT * FROM complaints WHERE status= 'Open'";
									$res = mysqli_query($db,$q );
									echo mysqli_num_rows($res);
								?></h1>
						</div>
						<div class="cntCl ">
							<d3>CLOSED COMPLAINTS</d3>
							<h1><?php 
									$q = "SELECT * FROM complaints WHERE status= 'Closed'";
									$res = mysqli_query($db,$q );
									echo mysqli_num_rows($res);
								?></h1>
						</div>
						<div class="cntmsg ">
							<d3>MESSAGES</d3>
							<h1><?php 
									$q = "SELECT * FROM message WHERE sender_code='{$_SESSION['admincode']}'";
									$res = mysqli_query($db,$q );
									echo mysqli_num_rows($res);
								?></h1>
						</div>
					</div>
				</div>
			</div>
		<?php endif ?>
	</div>
				

	<script>
	// Get the modal
	var modal = document.getElementById('id01');

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
	    if (event.target == modal) {
	        modal.style.display = "none";
	    }
	}

	$(document).ready(
	    function(){
	        $(".activeC").click(function () {
	            $("#activeComp").fadeIn("slow");
	            $("#msg").hide();
	            $("#closeComp").hide();
	            
	        });

	    });

	$(document).ready(
	    function(){
	        $(".closeC").click(function () {
	            $("#closeComp").fadeIn("slow");
	            $("#msg").hide();
	            $("#activeComp").hide();
	            
	        });

	    });

	$(document).ready(
	    function(){
	        $(".seemsg").click(function () {
	            $("#msg").fadeIn("slow");
	            $("#closeComp").hide();
	            $("#activeComp").hide();
	            
	        });

	    });

	$(document).ready(function() {
	    $('#content').fadeIn();
	});
	</script>

<?php require('footer.php') ?>
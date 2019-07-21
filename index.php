<?php 
	session_start();
	require('server.php'); 

	//complaints variables
	$cdesc = $attach = $rname = $rcode = $rmail = $rdate  = $comments = '';
	$status = "Open";

	if(!isset($_SESSION['empcode'])){
		array_push($errors, "You must login/register first");
		header('location:main.php');
	}
	
	if(isset($_POST['submit'])) {
		$cdesc = mysqli_real_escape_string($db,$_POST['cdesc']);
		$comments = mysqli_real_escape_string($db,$_POST['comment']);

		//attach upload
		$file = $_FILES["tfile"];
		if(is_uploaded_file($_FILES["tfile"]["tmp_name"])){
			//$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
			//$allowed = array('doc','docx','pdf','xl','xlsx');

			//if(in_array($ext,$allowed)) {
				if($file["error"] === 0) {
					move_uploaded_file($file["tmp_name"],"uploads/". $file["name"]);
					$attach = "uploads/". $file["name"];
					array_push($pass,"Upload success");
				} 
				else{
					array_push($errors,"Error uploading this file");
				}
			//} else if(!in_array($ext,$allowed)){
				//	array_push($errors,"You can't upload files of this type");
			//}
		}

		if(empty($cdesc)){
			array_push($errors, "You must fill in the Description");
		} 
		else {
			if(count($errors) == 0){
				//register details
				$rname = $_SESSION['empname'];
				$rcode = $_SESSION['empcode'];
				$rmail = $_SESSION['empmail'];

				//tno details
				$rdate= date('Y-m-d');
		
				$sql = "INSERT INTO complaints VALUES ('','$cdesc','$attach','$rcode','$rname','$rmail','$rdate','','$status','$comments')";
				mysqli_query($db,$sql);

				array_push($pass,"Complaint Lodged successfully");

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
		<?php if (isset($_SESSION['empcode'])): ?>
			
			<p class="welcome">Welcome <?php echo $_SESSION['empname']; echo "(".ucfirst($_SESSION['emptype']).")";?>
			<a href="index.php?logout='1'" class="logout btn btn-danger">Log out</a></p>

			<hr/>
			<hr/>

			<ul class="navigation">
				<li><button class="btn btn-info activeC">Registered complaints</button></li>	
				<li><button class="btn btn-info closeC">Completed Complaints</button></li>
				<li><button class="btn btn-info seemsg">Messages from Admin</button></li>
				<li><button onclick="document.getElementById('id01').style.display='block'" class="btn btn-info float-right" style="margin-right: 0.8em;">Lodge Complaint</button></li>
			</ul>
	
			<div id="id01" class="modal">
				<form class="modal-content animate" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">
					
					<div class="input-group">
						<label>Complaint Desc</label>
						<input type="text" name="cdesc" class="">
					</div>

					<div class="input-group">
						<label for="fileToUpload">Select file to upload</label>
						<input type="file" name="tfile" class="file_upload">
					</div>

					<hr style="border: 1px solid #999; width: 100%;" />

					<div class="input-group">
						<label>Comments</label>
						<textarea name="comment" class=""></textarea>
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
									<th>Desc</th>
									<th>Attachment</th>
									<th>R Date</th>
									<th>Comments</th>
								</tr>
							</thead>

							<tbody>
								<?php 
								$q = "SELECT * FROM complaints WHERE reg_code = '{$_SESSION['empcode']}' AND status= 'Open'";
								$comp = mysqli_query($db,$q );

								while ($row = mysqli_fetch_array($comp)) { ?>
									<tr>
										
										<td class="task"> <?php echo $row['id']; ?> </td>
										<td class="task"> <?php echo $row['c_desc']; ?> </td>
										<td class="task">
											<?php if(!empty($row['attach'])):?>
												<a href="<?php echo $row['attach']; ?>" target="_new">Download</a>
											<?php endif?>
										</td>
										<td class="task"> <?php echo $row['reg_date']; ?> </td>
										<td class="task"> <?php echo $row['comments']; ?> </td>
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
									<th>Desc</th>
									<th>R Date</th>
									<th>Closed date</th>
								</tr>
							</thead>

							<tbody>
								<?php 
								$q = "SELECT * FROM complaints WHERE reg_code = '{$_SESSION['empcode']}' AND status = 'Closed'";
								$comp = mysqli_query($db,$q);

								while ($row = mysqli_fetch_array($comp)) { ?>
									<tr>
										<td class="task"> <?php echo $row['id']; ?> </td>
										<td class="task"> <?php echo $row['c_desc']; ?> </td>
										<td class="task"> <?php echo $row['reg_date']; ?> </td>
										<td class="task"> <?php echo $row['cl_date']; ?> </td>
									</tr>
								 <?php } ?>	
							</tbody>
						</table>
					</div>

					<div id="msg">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Msg No.</th>
									<th>Sender Code</th>
									<th>Sender Name</th>
									<th>Sent on</th>
									<th>Subject</th>
									<th>Attachment</th>
									<th>Message Body</th>
								</tr>
							</thead>

							<tbody>
								<?php 
								$q = "SELECT * FROM message WHERE rec_code = '{$_SESSION['empcode']}'";
								$comp = mysqli_query($db,$q);

								while ($row = mysqli_fetch_array($comp)) { ?>
									<tr>
										<td class="task"> <?php echo $row['id']; ?> </td>
										<td class="task"> <?php echo $row['sender_code']; ?> </td>
										<td class="task"> <?php echo $row['sender_name']; ?> </td>
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
									$q = "SELECT * FROM complaints WHERE reg_code = '{$_SESSION['empcode']}' AND status= 'Open'";
									$res = mysqli_query($db,$q );
									echo mysqli_num_rows($res);
								?></h1>
						</div>
						<div class="cntCl ">
							<d3>CLOSED COMPLAINTS</d3>
							<h1><?php 
									$q = "SELECT * FROM complaints WHERE reg_code = '{$_SESSION['empcode']}' AND status= 'Closed'";
									$res = mysqli_query($db,$q );
									echo mysqli_num_rows($res);
								?></h1>
						</div>
						<div class="cntmsg ">
							<d3>RECIEVED MESSAGES</d3>
							<h1><?php 
									$q = "SELECT * FROM message WHERE rec_code='{$_SESSION['empcode']}'";
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
	            $("#closeComp").hide();
	            $("#msg").hide();
	        });

	    });

	$(document).ready(
	    function(){
	        $(".closeC").click(function () {
	            $("#closeComp").fadeIn("slow");
	            $("#activeComp").hide();
	            $("#msg").hide();
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
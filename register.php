<?php
	session_start();
	$_SESSION['show_captcha'] = true;
	require_once 'main/functions.php';
	require_once 'config.php';
	$title='Wahoo! Registration Page';include('templates/header.php');
	if($_POST){
		if($_POST['password'] != $_POST['password_confirm'])
			$error_message = 'Password confirmation doesn\'t match.';
		else {
			if (register($_POST['username'], $_POST['password']))
				header('location:index.php');
			else{
				$error_message = 'Username is already taken.';
			}
		}
	}
?>

<!--<main role="main" class="container-lg" id="main_area">-->
	<div class="">
		<h1>Register</h1>
		<div id="alert_msg" class="col-md-5 pl-0 form-group alert-danger">
			<?php
			if(isset($error_message))
				echo "<div>$error_message</div>"
			?>
		</div>
		<div class="row">
			<form action="register.php" method="post" class="col-9">
				<?php echo generate_csrf(); ?>
				<div class="input-group mb-3 col-5">
					<div class="input-group-prepend">
						<label for="username" class="input-group-text">
							Username
						</label>
					</div>
					<input type="text"
						   class="form-control" name="username" id="username" placeholder="UserName" value="">
				</div>
				<div class="row col p-0">
					<div class="col-md-5 pr-0">
						<div class="input-group mb-3 col-md-12 pr-0">
							<div class="input-group-prepend">
								<label for="password" class="input-group-text">
									Password
								</label>
							</div>
							<input type="password"
								   class="form-control" name="password" id="password" placeholder="Enter Password" onchange="score_password('submission','username','password','password_confirm')" value="">

						</div>
						<div class="input-group mb-3 col-md-12 pr-0">
							<div class="input-group-prepend">
								<label for="password_confirm" class="input-group-text">
									Confirm
								</label>
							</div>
							<input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Password Confirmation" onkeyup="score_password('submission','username','password','password_confirm')" value="">
						</div>

					</div>
					<div class="col">
						Password Rating:
						<div id="score">

						</div>
						<span id="password_feedback"></span>
					</div>
				</div>
				<div class="input-group mb-3 row col-12" id="solved_it" hidden="true" aria-hidden="true">
					<div class="col-md-12">
						<h2>Captcha Already Solved</h2>
					</div>
				</div>

				<div id="alert_msg_captcha" class="col-md-5 pr-0 pl-0 form-group">

				</div>
				<div class="input-group mb-3 row col-md-12" id="year_container">
					<div class="row col-md-9 col-lg-7 pr-0">
						<div class="col-md-8 col-lg-7">
							<h3 id="content_msg"><?php echo generate_captcha(); ?></h3>
						</div>
						<div class="col-md-4 col-lg-3 input-group mb-3">
							<div class="input-group-prepend">
								<label for="year" class="input-group-text" style="max-height:3rem">=</label>
							</div>
							<input type="number" name="year" class="form-control" id="year" placeholder="?" />
						</div>

					</div>
					<div class="col-md-2 p-3">
						<button type="button" class="btn btn-secondary" id="check_captcha" >Check Captcha</button>
					</div>
				</div>

				<input type="text" name="password_score" id="password_score" hidden="true" aria-hidden="true" />
				<div class="align-center row pt-3">
					<div class="col-lg-auto">
						<button type="submit" class="btn btn-lg btn-primary" id="submission" disabled>Submit</button>
					</div>
				</div>
			</form>
		</div>
	</div>
<!--</main>-->
</main>
</div>
<script type="text/javascript" defer async src="js/zxcvbn.js"></script>
<script type="text/javascript">
	document.addEventListener("DOMContentLoaded",()=>{
		document.getElementById("check_captcha").addEventListener("click",event=>{check_captcha()});
	})
	function check_captcha(){
		const ans_el = document.getElementById('year');
		const ans = parseInt(ans_el.value);
		const content = {'captcha_ans':ans}
		ans_el.value = '';
		submit("/captcha.php",content,resp=>{
			if(resp.error){
				document.getElementById('content_msg').innerText = resp.captcha;
				document.getElementById('year_container').hidden = false;
				document.getElementById('solved_it').hidden = true;
				document.getElementById('alert_msg_captcha').innerHTML = `<div class="alert alert-warning alert-dismissible fade show" role="alert" id="alert_item"> ${resp.msg}</div>`;
				window.setTimeout(()=>{
					$('#alert_item').alert('close');
				},3500);
			}
			else{
				document.getElementById('year_container').hidden = true;
				document.getElementById('solved_it').hidden = false;
			}
		})
	}
</script>
<?php include("footer.inc"); ?>
</body>
</html>

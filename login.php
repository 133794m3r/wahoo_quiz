<?php
	session_start();
	require_once 'config.php';
	require_once 'main/functions.php';
	$_SESSION['show_captcha'] = false;
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if ($_SESSION['show_captcha'] && !isset($_POST['captcha'])) {
			$captcha_msg = 'No Captcha message provided.';
		}
		else if($_SESSION['show_captcha'] && $_SESSION['captcha'] !== $_POST['captcha']){
			$captcha_msg = 'Invalid captcha received.';
		}
		else {
			if (login($_POST['username'],$_POST['password'])) {
				header('Location: index.php');
				$_SESSION['show_captcha'] = false;
				$_SESSION['bad_logins'] = 0;
			}
			else {
				$error_message = "Username/Password is wrong";
				$_SESSION['bad_logins']++;
//				if ($_SESSION['bad_logins'] > 5)
//					$_SESSION['show_captcha'] = true;
			}
		}
	}
	else if($_SERVER['REQUEST_METHOD'] !== 'GET')
		header('HTTP/1.0 405 Method Not Allowed',405);
	else if ($_SERVER['REQUEST_METHOD'] == 'GET')
		if(!isset($_SESSION)) {
			session_start();
			$_SESSION['show_captcha'] = false;
			$_SESSION['bad_logins'] = 0;
		}
$title='Wahoo! Login Page';include('templates/header.php')
?>
	<div class="">
			<h2>Login</h2>

			<?php
			if(isset($error_message))
				echo "<div>$error_message</div>"
			?>
			<form action="login.php" method="post" id="login">
				<?php echo generate_csrf(); ?>
				<div class="form-group">
					<label>
						<input autofocus class="form-control" type="text" name="username" placeholder="Username">
					</label>
				</div>
				<div class="form-group">
					<label>
						<input class="form-control" type="password" name="password" placeholder="Password">
					</label>
				</div>
				<div class="input-group mb-3 row" id="solved_it" hidden="true" aria-hidden="true">
					<div class="col-md-12">
						<h3>Captcha Already Solved</h3>
					</div>
				</div>
				<div id="alert_msg_captcha" class="col-md-5 pr-0 pl-0 form-group">

				</div>
				<?php
				if($_SESSION['show_captcha']) {
					$captcha_msg = generate_captcha();
					echo '<div class="input-group mb-3 row col-md-12" id="year_container">';
				}
				else {
					$captcha_msg = '';
					echo '<div class="input-group mb-3 row col-md-12" id="year_container" hidden="true" aria-hidden="true">';
				}
				?>
						<div class="row col-md-9 col-lg-7 pr-0">
							<div class="col-md-8 col-lg-7">
								<h3 id="content_msg"><?php echo $captcha_msg ?></h3>
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
					<input class="btn btn-primary" type="submit" value="Login">
			</form>

			Don't have an account? <a href='register.php'>Register here.</a>

		</main>
	</div>
</div>
<?php include('footer.inc'); ?>
	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded",()=>{
			document.getElementById("check_captcha").addEventListener("click",event=>{check_captcha()});
		})
		function check_captcha(){
			const ans_el = document.getElementById('year');
			const letters_el = document.getElementById('letters');
			const ans = parseInt(ans_el.value);
			const letters = letters_el.value;
			const content = {'captcha_ans':ans,"letters":letters}
			letters_el.value = '';
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
			});
		}
	</script>
	</body>
</html>
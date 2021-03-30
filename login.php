<?php
	require_once 'config.php';
	require_once 'main/functions.php';
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if ($_SESSION['show_captcha'] && !isset($_POST['captcha'])) {
			$captcha_msg = 'No Captcha message provided.';
		}
		else if($_SESSION['show_captcha'] && $_SESSION['captcha'] !== $_POST['captcha']){
			$captcha_msg = 'Invalid captcha received.';
		}
		else {
			if ($_POST['username'] == 'user' && $_POST['password'] = 'password') {
				header('Location: index.php');
				$_SESSION['show_captcha'] = false;
				$_SESSION['bad_logins'] = 0;
			}
			else {
				$error_message = "Username/Password is wrong";
				$_SESSION['bad_logins']++;
				if ($_SESSION['bad_logins'] > 5)
					$_SESSION['show_captcha'] = true;
			}
		}
	}
	else if($_SERVER['REQUEST_METHOD'] !== 'GET')
		exit();
	else if ($_SERVER['REQUEST_METHOD'] == 'GET')
		if(!isset($_SESSION)) {
			session_start();
			$_SESSION['show_captcha'] = false;
			$_SESSION['bad_logins'] = 0;
		}
?>
<?php
$title='Wahoo! Login Page';include('templates/header.php')
?>
		<div class="container-lg container min-100-vh">
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
		</div>
<?php include('footer.inc'); ?>
	</body>
</html>
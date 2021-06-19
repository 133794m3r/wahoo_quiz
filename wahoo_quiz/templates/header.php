<!DOCTYPE html>
<html lang="en">
	<head>
		<title>
			<?php echo $title; ?>
		</title>
		<meta charset="UTF-8">
		<link href="/static/favicon.ico"  rel="shortcut icon">
		<link href="/static/css/master.css" rel="stylesheet" />
		<link href="/static/css/bs-darkly.min.css" rel="stylesheet" />

		<script src="/static/js/jquery.min.js" async></script>
		<script src="/static/js/master.js" async></script>
		<script src="/static/js/client.js" async></script>
		<script type="text/javascript">
			(function() {

				const bs_src = "/static/js/bootstrap.min.js";

				const async_load = function () {
					let first, s;
					s = document.createElement('script');
					s.src = bs_src;
					s.type = 'text/javascript';
					s.async = true;
					first = document.getElementsByTagName('script')[0];
					return first.parentNode.insertBefore(s, first);
				};

				if (window.attachEvent != null) {
					window.attachEvent('onload', async_load);
				} else {
					window.addEventListener('load', async_load, false);
				}

			}).call(this);
		</script>
	</head>
	<body>
		<div class="cover-container d-flex h-100 mx-auto flex-column">
			<nav class="navbar navbar-expand-md navbar-dark bg-dark">
				<span class="navbar-brand">Wahoo Quiz</span>
				<button class="navbar-toggler" type="button" data-toggle="collapse"
						data-target="#navbar_nav" aria-controls="navbar_nav"
						aria-expanded="false" aria-label="Toggle">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbar_nav">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item">
							<a class="nav-link" href="/index.php">Home</a>
						</li>

						<?php if(isset($_SESSION['admin']) && $_SESSION['admin']): ?>
							<li class="nav-item"><a class="nav-link" href="/admin/question_admin.php">Master Quiz Creator</a></li>
						<?php endif ?>
						<?php if(isset($_SESSION['username'])): ?>
							<li class="nav-item"><a class="nav-link" href="/main/quiz_creator.php">Quiz Creator</a></li>
							<li class="nav-item"><a class="nav-link" href="/main/user_quizzes.php"><?php echo $_SESSION['username'] ?>'s Quizzes</a>
							<li class="nav-item"><a class="nav-link" href="/logout.php">Log out</a></li>
						<?php else: ?>
						<li class="nav-item">
							<a class="nav-link" href="login.php">Login</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="register.php">Register</a>
						</li>
						<?php endif; ?>
					</ul>
				</div>
			</nav>
			<main class="text-center container-lg pt-4" id="main_area" role="main">
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>
			<?php echo $title; ?>
		</title>
		<meta charset="UTF-8">
		<link href="favicon.ico"  rel="shortcut icon">
		<link href="../css/master.css" rel="stylesheet" />
		<link href="../css/bs-darkly.min.css" rel="stylesheet" />

		<script src="../js/jquery.min.js" async></script>
		<script src="../js/master.js" async></script>
		<script src="../js/client.js" async></script>
		<script type="text/javascript">
			(function() {

				const bs_src = "../js/bootstrap.min.js";

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

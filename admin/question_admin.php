<?php
	session_start();
	if(!$_SESSION['admin'])
		exit();

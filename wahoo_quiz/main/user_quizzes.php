<?php
session_start();
if(!array_key_exists('id',$_SESSION))
	http_redirect('/login.php');
require_once '../config.php';
include '../templates/header.php';


include '../templates/footer.php';

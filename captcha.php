<?php
require_once 'main/functions.php';
session_start();
header('content-type:application/json');
if($_SERVER['REQUEST_METHOD'] === 'GET'){
	echo json_encode(['error'=>true]);
}
else if($_SERVER['REQUEST_METHOD'] === 'POST'){
//	error_log(var_export($_SESSION,true),4);
	$params = parse_json_post();
//	error_log(var_export($params,true),4);
	if($params === false)
		$msg = 'No data given';
	else if($params['captcha_ans'] == $_SESSION['captcha']){
		echo json_encode(['error'=>false]);
		$_SESSION['show_captcha'] = false;
		exit;
	}
	else{
		if(!isset($params['captcha_ans'])){
			$msg = 'No captcha given';
		}
		else{
			$msg = 'Invalid captcha received.';
		}
	}
	$_SESSION['show_captcha'] = true;
	echo json_encode(['error'=>true, 'captcha'=>generate_captcha(), 'msg'=>$msg]);
}

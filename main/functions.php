<?php
function generate_captcha(){
	$a = rand(0,12);
	$b = rand(0,12);
	$method = rand(0,3);
	switch($method){
		case 0:
			$ans = $a + $b;
			$op = (rand(1,3))?'plus':'+';
			break;
		case 1:
			$a = rand($b,12);
			$ans = $a-$b;
			$op = (rand(1,3))?'minus':'-';
			break;
		case 2:
			$ans = $a*$b;
			$op = (rand(1,3))?'times':'*';
			break;
		default:
			$c = $a*$b;
			if($b > $a) {
				$ans = $a;
				$a = $c;
			}
			else{
				$ans = $b;
				$b = $a;
				$a = $c;
			}
			$op = (rand(1,3))?"divided by":'/';
			break;
	}
	$_SESSION['captcha'] = $ans;
	$num_str = array(0=>'Zero', 1=>'One', 2=>'Two', 3=>'Three', 4=>'Four', 5=>'Five', 6=>'Six', 7=>'Seven', 8=>'Eight', 9=>'Nine', 10=>'Ten', 11=>'Eleven', 12=>'Twelve');
	$b = (rand(1,5)==1)?$num_str[$b]:$b;
	return "$a $op $b";
}
function generate_csrf(){
	$r = session_id().mt_rand().microtime();
	$hash = hash('sha256',$r,true);
	$token = hash('sha256',CSRF_TOKEN_SALT,true);
	$token = hash('sha256',$token.$hash);
	return "<input id='csrf' name='csrf' type='hidden' value='$token' />";
}

function parse_json_post() {
	$json_params = file_get_contents('php://input');
	if(strlen($json_params) > 0)
		$json_data = json_decode($json_params,true);
	else
		return false;

	if(json_last_error() !== JSON_ERROR_NONE)
		return false;
	else
		return $json_data;
}

function login($username,$password): bool {
	global $QUIZ;
	$stmt = $QUIZ->prepare("select password,password_upper,`role` from users where username=? LIMIT 1");
	$username = $QUIZ->real_escape_string($username);
	$stmt->bind_param('s', $username);
	$stmt->bind_result($res_password,$res_upper,$role);
	$res = $stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 1){
		$stmt->fetch();
		$password_upper = ucfirst($password);
		if(password_verify($password,$res_password) || password_verify($password_upper, $res_upper)){
			$_SESSION['username'] = $username;
			if($role < 2)
				//for now it is letting all of them be admin but that'll change later.
				$_SESSION['admin'] = true;
			return true;
		}
		else{
			return false;
		}
	}
	else{
		error_log('no result',4);
		return false;
	}
}

function register($username,$password): bool {
	global $QUIZ;
	$stmt = $QUIZ->prepare('Select username from users where username = ? limit 1;');
	$QUIZ->real_escape_string($username);
	$stmt->bind_param('s', $username);
	$stmt->bind_result($result);
	if($stmt->fetch()){
		return false;
	}
	else {
		$password_upper = ucfirst($password);
		$password = password_hash($password,PASSWORD_BCRYPT);
		$password_upper = password_hash($password_upper, PASSWORD_BCRYPT);
		$stmt = $QUIZ->prepare('INSERT INTO users(username,password,password_upper) values (?,?,?)');
		$stmt->bind_param('sss', $username, $password, $password_upper);
		$stmt->execute();
		return true;
	}
}
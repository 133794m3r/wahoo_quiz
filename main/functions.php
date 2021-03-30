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
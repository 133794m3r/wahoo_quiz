<?php
if($_SERVER['REQUEST_METHOD'] != 'POST') {
	header('HTTP/1.0 405 Method Not Allowed', 405);
	exit();
}
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
	header('HTTP/1.0 400 Bad Request', 400);
	exit();
}

session_start();
if(!isset($_SESSION['lock']))
	$_SESSION['lock'] = false;
require_once('../main/functions.php');
//we only accept JSON apis
$json_params = parse_json_post();
//this means they didn't give us valid json. Time to die.
if($json_params === false)
	exit();


require_once('../config.php');
$final_result = array('ok'=>true,'error'=>'');
switch($json_params['cmd']){
	//gets the quiz for a user who is about to take it.
	case 'get_quiz':
		if(!isset($json_params['quiz_pass']))
			raise_http_error(400);

		//the quiz pass is actually the quiz_owner_id+quiz_id but b64 encoded and packed.
		$stmt = $QUIZ->prepare( 'select quizzes.name as quiz_name, questions.text as question_text, questions.id as question_id, GROUP_CONCAT( (select id,text from question_answers where question_id = questions.id) ) as answers from quizzes inner join questions on quizzes.id = questions.quiz_id where quizzes.id = ? and quizzes.owner_id = ? LIMIT 1');
		$quiz_parts = unpack('NN',base64_decode($json_params['quiz_pass']));
		$stmt->bind_param('ii',$quiz_parts[1],$quiz_parts[0]);

		$stmt->execute();
		$res = $stmt->get_result();
		$row = $res->fetch_array(MYSQLI_ASSOC);
		$stmt->close();
		$_SESSION['correct'] = 0;
		$_SESSION['total'] = 0;
		if($row) {
			$_SESSION['quiz_id'] = $quiz_parts[1];
			$_SESSION['question_id'] = $row['question_id'];
			$res = $QUIZ->query('select id from question_answers where correct = 1 and question_id = '.$row['question_id']);
			$_SESSION['correct_answer'] = $res->fetch_array()[0];
			$final_result = array('ok' => true, 'error' => '', 'results' => $row);
		}
		else
			$final_result =  array('ok'=>false,'error'=>'A quiz with that identifier doesn\'t exist','results'=>'');
		break;
;
	case 'get_next_question':
		$res = $QUIZ->query('select id from questions where id >'.$_SESSION['question_id'].' and quiz_id ='.$_SESSION['quiz_id'].' LIMIT 1');
		if($res->num_rows > 0) {
			$_SESSION['question_id'] = $res['id'];
			$res = $QUIZ->query('select id,text,correct from question_answers where question_id = '.$res['id']);
			$final_result = array('ok'=>true,'error'=>'','answers'=>array());
			$_SESSION['correct_answers'] = array();
			while($row = $res->fetch_array()) {
				array_push($final_result['answers'], "$row[0],$row[1]");
				if($row[2])
					array_push($_SESSION['correct_answers'], $row[2]);
			}
			$_SESSION['total'] += 1;
		}
		else{
			$final_result = array('ok'=>false,'error'=>'Final question reached.');
		}
		break;

	case 'check_answer':
		if(!isset($json_params['answer']))
			raise_http_error(400);

		if(array_search($json_params['answer'],$_SESSION['correct_answer'])) {
			$_SESSION['correct'] += 1;
			$final_result = array('ok'=>true,'error'=>'');
		}
		else{
			$final_result = array('ok'=>false,'error'=>'Answer was wrong');
		}
		break;
	default:
		//otherwise they're trying something that we're no supporting so bail.
		raise_http_error(400);
		break;
}
header('Content-type: application/json; charset=utf-8');
echo json_encode($final_result);

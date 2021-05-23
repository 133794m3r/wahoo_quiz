<?php
if($_SERVER['REQUEST_METHOD'] != 'POST') {
	header('HTTP/1.0 405 Method Not Allowed', 405);
	exit();
}
session_start();

require_once('../main/functions.php');
//we only accept JSON apis
$json_params = parse_json_post();
//this means they didn't give us valid json. Time to die.
if($json_params == false)
	exit();

require_once('../config.php');
$final_result = array();
switch($json_params['cmd']){
	//gets the quiz for a user who is about to take it.
	case 'get_quiz':
		if(!isset($json_params['quiz_pass']))
			raise_http_error(400);
		//the quiz pass is actually the quiz_owner_id+quiz_id but b64 encoded and packed.
		$stmt = $QUIZ->prepare( 'select quizzes.name as quiz_name, questions.text as question_text, questions.id as question_id, GROUP_CONCAT( (select id,text from question_answers where question_id = questions.id) ) as answers from quizzes inner join questions on quizzes.id = questions.quiz_id where quizzes.id = ? and quizzes.owner_id = ? LIMIT 1');
		$quiz_parts = unpack('NN',base64_decode($json_params['quiz_pass']));
		$stmt->bind_param('dd',$quiz_parts[1],$quiz_parts[0]);
		$stmt->execute();
		$res = $stmt->get_result();
		$row = $res->fetch_array(MYSQLI_ASSOC);
		$stmt->close();
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
	//This also gets the quiz but gets all fields.
	case 'edit_quiz':
		if(!isset($_SESSION['username']))
			raise_http_error(401);
		if($_SESSION['role'] > 2)
			raise_http_error(403);
		$stmt = $QUIZ->prepare('select quizzes.id as quiz_id, quizzes.name as quiz_name, GROUP_CONCAT( (select id,text from questions where quiz_id = quizzes.id) ) as questions from quizzes inner join questions on quizzes.id = questions.quiz_id  where quizzes.owner_id = ?');
		$stmt->bind_param('d',$_SESSION['id']);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();
		$final_result = array('ok'=>true, 'error'=>'','rows'=>$res->num_rows,'results'=>$res->fetch_all(MYSQLI_ASSOC));
		break;
	//this will be a post into the query.
	case 'update_quiz':
		if(!isset($json_params['quiz_id']) || !isset($json_params['quiz_name']))
			raise_http_error(400);

		$query = 'update quizzes set name = ? where id = ?';
		if($_SESSION['role'] > 3)
			$query .= 'and owner_id = ?';

		$stmt = $QUIZ->prepare($query);
		if($_SESSION['role'] > 3)
			$stmt->bind_param('sdd',$json_params['quiz_name'],$json_params['quiz_id'],$_SESSION['id']);

		if(!$stmt->execute())
			raise_http_error(500);
		if($stmt->affected_rows == 0)
			$final_result = array('ok'=>false, 'error'=>'No rows changed. Improper values received.');
		else
			$final_result = array('ok'=>true, 'error'=>'Updated successfully');
		$stmt->close();
		break;
	case 'get_next_question':
		$res = $QUIZ->query('select id from questions where id >'.$_SESSION['question_id'].' and quiz_id ='.$_SESSION['quiz_id'].' LIMIT 1');
		if($res->num_rows > 0) {
			$_SESSION['question_id'] = $res['id'];
			$res = $QUIZ->query('select id,text,correct from question_answers where question_id = '.$res['id']);
			$final_result = array('ok'=>true,'error'=>'','answers'=>array());
			while($row = $res->fetch_array()) {
				array_push($final_result['answers'], "$row[0],$row[1]");
				if($row[2])
					$_SESSION['correct_answer'] = $row[0];
			}
		}
		else{
			$final_result = array('ok'=>false,'error'=>'Final question reached probably.');
		}
		break;
	case 'check_answer':
		break;
	case 'update_question':
		$stmt = $QUIZ->prepare('select * from question_answers');
		break;
	case 'edit_question':
		$stmt = $QUIZ->prepare('select * from question_analytics');
		break;
	default:
		raise_http_error(400);
		break;
}

if(isset($stmt))
	$stmt->close();
header('Content-type: application/json; charset=utf-8');
echo json_encode($final_result);

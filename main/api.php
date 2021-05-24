<?php
if($_SERVER['REQUEST_METHOD'] != 'POST') {
	header('HTTP/1.0 405 Method Not Allowed', 405);
	exit();
}
if(!isset($_SERVER['X-Requested-With']) && $_SERVER['X-Requested-With'] != 'XMLHttpRequest') {
	header('HTTP/1.0 400 Bad Request', 400);
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
$final_result = array('ok'=>true,'error'=>'');
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

	//This also gets the quiz but gets all fields.
	case 'edit_quiz':
		//must be authenticated first.
		if(!isset($_SESSION['username']))
			raise_http_error(401);
		//must also be able to edit some quiz.
		if($_SESSION['role'] > 2)
			raise_http_error(403);

		$stmt = $QUIZ->prepare('select quizzes.id as quiz_id, quizzes.name as quiz_name, GROUP_CONCAT( (select id,text from questions where quiz_id = quizzes.id) ) as questions from quizzes inner join questions on quizzes.id = questions.quiz_id  where quizzes.owner_id = ?');
		$stmt->bind_param('d',$_SESSION['id']);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();
		$res_arr = $res->fetch_all(MYSQLI_ASSOC);
		$_SESSION['quiz_ids'] = array();
		foreach($res_arr as $row){
			array_push($_SESSION['quiz_ids'],$row['quiz_id']);
		}
		$final_result = array('ok'=>true, 'error'=>'','rows'=>$res->num_rows,'results'=>$res_arr);
		break;

	case 'update_quiz':
		if(!isset($json_params['quiz_id']) || !isset($json_params['quiz_name']))
			raise_http_error(400);
		//they are trying to access a route they shouldn't be able to.
		if($_SESSION['role'] > 2)
			raise_http_error(403);

		$query = 'update quizzes set name = ? where id = ?';
		if($_SESSION['role'] == 2)
			$query .= 'and owner_id = ?';

		$stmt = $QUIZ->prepare($query);
		if($_SESSION['role'] == 2)
			$stmt->bind_param('sdd',$json_params['quiz_name'],$json_params['quiz_id'],$_SESSION['id']);
		else
			$stmt->bind_param('sd',$json_params['quiz_name'],$json_params['quiz_id']);

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
			$_SESSION['total'] += 1;
		}
		else{
			$final_result = array('ok'=>false,'error'=>'Final question reached.');
		}
		break;

	case 'check_answer':
		if(!isset($json_params['answer']))
			raise_http_error(400);

		if($json_params['answer'] == $_SESSION['correct_answer']) {
			$_SESSION['correct'] += 1;
			$final_result = array('ok'=>true,'error'=>'');
		}
		else{
			$final_result = array('ok'=>false,'error'=>'Answer was wrong');
		}
		break;

	case 'update_question':
		if(!isset($json_params['quiz_id']) || !isset($json_params['question_id']) || !isset($json_params['text'])){
			$final_result = array('ok'=>false,error=>'Not all required parameters sent.');
		}
		else if($_SESSION['role'] == 2 && !array_search($json_params['quiz_id'],$_SESSION['quiz_ids'])){
			$final_result = array('ok'=>false,'error'=>'Quiz not found.');
		}
		else {
			$stmt = $QUIZ->prepare('update questions set text = ? where id = ? and quiz_id = ?');
			$stmt->bind_param('sdd', $json_params['text'], $json_params['question_id'], $json_params['quiz_id']);
			$res = $stmt->execute();
			if($stmt->affected_rows != 1){
				$final_result['ok'] = false;
				$final_result['error'] = 'You did not provided proper data.';
			}
			$stmt->close();
		}
		break;

	//eventually this'll let admins see all quizzes and who owns them and then select
	// one to get questions via other routes but not right now.
	case 'admin_get_quizzes':
		break;

	case 'edit_question':
		if($_SESSION['role'] > 2)
			raise_http_error(403);

		if(!isset($json_params['quiz_id']) || !isset($json_params['question_id'])){
			$final_result['ok'] = false;
			$final_result['error'] = 'Not all params set.';
		}
		else{
			//absolutely god awful code but oh well.
			$q = 'select qa.id as id ,qa.text as text ,qa.correct as correct from question_answers qa inner join questions q inner join quizzes where question_id = ? and quiz_id = ?';
			//this is ungodly ugly but it works.
			if($_SESSION['role'] == 2)
				$q .= ' and owner_id = ?';
			$stmt = $QUIZ->prepare($q);
			if ($_SESSION['role'] == 2)
				$stmt->bind_param('ddd',$json_params['quiz_id'],$json_params['question_id'],$_SESSION['id']);
			else
				$stmt->bind_param('dd',$json_params['quiz_id'],$json_params['question_id']);
			$stmt->execute();
			$res = $stmt->get_result();
			$stmt->close();
			if($res->num_rows == 0){
				$final_result['ok'] = false;
				$final_result['error'] = 'No questions found.';
			}
			else{
				$final_result['answers'] = $res->fetch_all(MYSQLI_ASSOC);
			}
		}
		break;

	default:
		//otherwise they're trying something that we're no supporting so bail.
		raise_http_error(400);
		break;
}
header('Content-type: application/json; charset=utf-8');
echo json_encode($final_result);

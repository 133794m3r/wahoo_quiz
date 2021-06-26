<?php

if($_SERVER['REQUEST_METHOD'] != 'POST') {
	header('HTTP/1.0 405 Method Not Allowed', 405);
	exit();
}
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
	header('HTTP/1.0 400 Bad Request', 400);
	exit();
}
require_once('../main/functions.php');
//we only accept JSON apis
$json_params = parse_json_post();
//this means they didn't give us valid json. Time to die.
if($json_params === false)
	exit();

session_start();

require_once('../config.php');
if(!array_key_exists('role',$_SESSION))
	raise_http_error(401);
else if($_SESSION['role'] > 2)
	raise_http_error(403);

$final_result = array('ok'=>true,'error'=>'');
switch($json_params['cmd']){

	//This also gets the quiz but gets all fields.
	case 'edit_quiz':
//		$stmt = $QUIZ->prepare('select qz.id as quiz_id, qz.name as quiz_name, GROUP_CONCAT( (select id,text from questions where quiz_id = qz.id) ) as questions from quizzes qz where qz.owner_id = ?');
		$stmt  = $QUIZ->prepare('select qz.id as quiz_id, qz.name as quiz_name from quizzes qz where qz.owner_id = ?');
		$stmt->bind_param('i',$_SESSION['id']);
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

		$query = 'update quizzes set name = ? where id = ?';
		if($_SESSION['role'] === 2)
			$query .= 'and owner_id = ?';

		$stmt = $QUIZ->prepare($query);
		if($_SESSION['role'] === 2)
			$stmt->bind_param('sii',$json_params['quiz_name'],$json_params['quiz_id'],$_SESSION['id']);
		else
			$stmt->bind_param('si',$json_params['quiz_name'],$json_params['quiz_id']);

		if(!$stmt->execute())
			raise_http_error(500);
		if($stmt->affected_rows === 0)
			$final_result = array('ok'=>false, 'error'=>'No rows changed. Improper values received.');
		else
			$final_result = array('ok'=>true);
		$stmt->close();
		break;

	case 'update_question':
		if(!isset($json_params['quiz_id']) || !isset($json_params['question_id']) || !isset($json_params['text'])){
			$final_result = array('ok'=>false,'error'=>'Not all required parameters sent.');
		}
		else if(!array_search($json_params['quiz_id'],$_SESSION['quiz_ids'])){
			$final_result = array('ok'=>false,'error'=>'Quiz not found.');
		}
		else {
			$stmt = $QUIZ->prepare('update questions set text = ? where id = ? and quiz_id = ?');
			$stmt->bind_param('sii', $json_params['text'], $json_params['question_id'], $json_params['quiz_id']);
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
	case 'get_questions':
		if(!isset($json_params['quiz_id']))
			raise_http_error(399);

		if($_SESSION['role'] == 2 && array_search($json_params['quiz_id'],$_SESSION['quiz_ids']) === false)
			raise_http_error(400);

		$stmt = $QUIZ->prepare('select id,text from questions where quiz_id = ?');
		$stmt->bind_param('d',$json_params['quiz_id']);
		$stmt->execute();
		$res = $stmt->get_result();
		if($res->num_rows == -1) {
			$final_result['ok'] = false;
			$final_result['error'] = 'No questions created.';
		}
		else{
			$final_result['num'] = $res->num_rows;
			$final_result['results'] = $res->fetch_all(MYSQLI_ASSOC);
			$final_result['quiz_id'] = $json_params['quiz_id'];
		}
		break;
	case 'edit_question':
		if(!isset($json_params['quiz_id']) || !isset($json_params['question_id'])){
			$final_result['ok'] = false;
			$final_result['error'] = 'Not all params set.';
		}
		else{
			//absolutely god awful code but oh well.
			$q = 'select qa.id as id ,qa.text as text ,qa.correct as correct from question_answers qa inner join questions q inner join quizzes where question_id = ? and quiz_id = ?';
			//this is ungodly ugly but it works.
			if($_SESSION['role'] === 2)
				$q .= ' and owner_id = ?';
			$stmt = $QUIZ->prepare($q);
			if ($_SESSION['role'] === 2)
				$stmt->bind_param('iii',$json_params['quiz_id'],$json_params['question_id'],$_SESSION['id']);
			else
				$stmt->bind_param('ii',$json_params['quiz_id'],$json_params['question_id']);
			$stmt->execute();
			$res = $stmt->get_result();
			$stmt->close();
			if($res->num_rows === 0){
				$final_result['ok'] = false;
				$final_result['error'] = 'No questions found.';
				$final_result['num'] = 0;
			}
			else{
				$final_result['num'] = $res->num_rows;
				$final_result['answers'] = $res->fetch_all(MYSQLI_ASSOC);
			}
		}
		break;
	case 'update_answers':
		//check that we have the correct parameters
		if(!array_key_exists('quiz_id',$json_params) || !array_key_exists('question_id',$json_params) || !array_key_exists('answers',$json_params))
			raise_http_error(400);

		$bound_params = array();
		$param_str = '';
		$correct = 0;
		$q = 'update question_answers set correct = (case question_id ';
		foreach($json_params['answers'] as $answer ){
			array_push($bound_params,$answer['id'],$answer['correct']);
			$q.= 'when ? then ?';
			if($answer['correct'])
				$correct++;
			$param_str .= 'ii';
		}
		$q.= 'end) where question_id = ?';
		if($correct === 0) {
			$final_result['ok'] = false;
			$final_result['error'] = 'Must have at least one of them as correct';
		}
		else {
			$stmt = $QUIZ->prepare($q);
			$stmt->bind_param(param_str, ...$bound_params);
			$stmt->execute();
			if($res->num_rows === 0) {
				$final_result['ok'] = false;
				error_log(print_r($stmt->error_list));
				$final_result['error'] = 'Some query error occurred';
			}
			$stmt->close();
		}
		break;
	case 'update_answer':
		if(!array_key_exists('answer_id',$json_params) ||
			!array_key_exists('question_id',$json_params) ||
			!array_key_exists('text',$json_params) ||
			!array_key_exists('correct',$json_params) )
			raise_http_error(400);

		//this query is bad but I don't know of a better way to make it harder for someone to guess the correct ID of a question and it's matching answers.
		$stmt = $QUIZ->prepare('update question_answers set text = ?,correct = ? where id = ? and question_id = ?');
		$stmt->bind_param('siii',$json_params['text'],$json_params['correct'],$json_params['answer_id'],$json_params['question_id']);
		if(!$stmt->execute()){
			error_log(print_r($stmt->error_list));
			$final_result['ok'] = false;
			$final_result['error'] = 'Something went wrong';
		}
		$stmt->close();
		break;
	case 'create_quiz':

		if($_SESSION['lock']) {
			$final_result['ok'] = false;
			$final_result['error'] = 'Not done with previous tx yet.';
		}
		else if(!isset($json_params['quiz_name'])){
			$final_result['ok'] = false;
			$final_result['error'] = 'No data given.';
		}
		else{
			$_SESSION['lock'] = true;
			$stmt = $QUIZ->prepare('insert into quizzes(name,owner_id) values(?,?)');
			$stmt->bind_param('si',$json_params['quiz_name'],$_SESSION['id']);
			$stmt->execute();
			$final_result['quiz_id'] = $stmt->insert_id;
			array_push($_SESSION['quiz_ids'],$stmt->insert_id);
			$_SESSION['lock'] = false;
			$stmt->close();
		}
		break;
	case 'create_question':
		//the answers is an array of all of the answers so we make sure that it's there.
		//we also make sure that there's at least 1 marked correct and the length is more than 0.
		if(!isset($json_params['text']) || !isset($json_params['quiz_id'])
		)
			raise_http_error(400,'text or quiz_id not set.');

		//we allow SATA as an option by them just simply selecting more than 1 answer as correct.
		//$correct_count = 0;
		$query_values = '';
		$param_types = '';

			$stmt = $QUIZ->prepare('insert into questions(text,quiz_id) values(?,?)');
			$stmt->bind_param('si',$json_params['text'],$json_params['quiz_id']);
			$stmt->execute();
			$question_id = $stmt->insert_id;
			$stmt->close();
			$final_result['ok'] = true;
			$final_result['id'] = $question_id;
		break;
	case 'create_answer':
		//check that we have the correct parameters
		if(!array_key_exists('question_id',$json_params) || !array_key_exists('text',$json_params) || !array_key_exists('correct',$json_params))
			raise_http_error(400);

		$param_str = '';
		$correct = 0;
		$stmt = $QUIZ->prepare('insert into question_answers(question_id,text,correct) values(?,?,?)');
		$stmt->bind_param('isi',$json_params['question_id'],$json_params['text'],$json_params['correct']);
		$stmt->execute();

		if($stmt->affected_rows == 0) {
			$final_result['ok'] = false;
			$final_result['msg'] = 'Insert Failed';
		}
		else{
			$final_result['id'] = $stmt->insert_id;
		}
		$stmt->close();
		break;
	case 'get_question_analytics':
		//will eventually just give errors to the person.
		if(!array_key_exists('quiz_id',$json_params)){
			$final_result['ok'] = false;
			$final_result['error'] = 'Quiz id not set.';
		}
		else if(array_search($json_params['quiz_id'],$_SESSION['quiz_ids'])===false){
			$final_result['ok'] = false;
			$final_result['error'] = 'Quiz isn\'t yours.';
		}
		else {
			$stmt = $QUIZ->prepare('select text,answered,correct,quiz_id as id from question_analytics qa inner join questions q on question_id where q.quiz_id = ?');
			$stmt->bind_param('d', $json_params['quiz_id']);
			if (!$stmt->execute()) {
				$final_result['ok'] = false;
				$final_result['error'] = 'Quiz id was no valid.';
			}
			else {
				$res = $stmt->get_result();
				if ($res->num_rows === 0) {
					$final_result['ok'] = false;
					$final_result['error'] = "No questions with analytics found.";
				}
				else {
					$final_result['rows'] = $res->num_rows;
					$final_result['result'] = $res->fetch_all(MYSQLI_ASSOC);
				}
			}
		}
		break;
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($final_result);
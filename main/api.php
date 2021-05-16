<?php
if($_SERVER['REQUEST_METHOD'] != 'POST') {
	header('HTTP/1.0 405 Method Not Allowed', 405);
	exit();
}
require_once('../main/functions.php');
//we only accept JSON apis
$json_params = parse_json_post();
//this means they didn't give us valid json. Time to die.
if($json_params == false)
	exit();

include('../config.php');

switch($json_params['cmd']){
	//gets the quiz for a user who is about to take it.
	case 'get_quiz':
		if(!isset($json_params['quiz_pass']))
			raise_http_error(400);
		//the quiz pass is actually the quiz_owner_id+quiz_id but b64 encoded and packed.
		$QUIZ->prepare( 'select quizzes.name, questions.question_text, questions.id, GROUP_CONCAT( question_answers.question_id,question_answers.answer_text,correct) as answers from quizzes inner join questions on quizzes.id = questions.id inner join question_answers where quizzes.id = ? limit 1;');
		$quiz_parts = unpack('NN',base64_decode($json_params['quiz_pass']));
		$QUIZ->bind_param('d',$quiz_parts[1]);
		break;
	//This also gets the quiz but gets all fields.
	case 'edit_quiz':
		break;
	//this will be a post into the query.
	case 'update_quiz':
		break;
	case 'get_question':
		break;
	case 'update_question':
		break;
	case 'edit_question':
		break;
}


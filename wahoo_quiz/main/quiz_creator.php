<?php
	session_start();
	require 'functions.php';
	if(!array_key_exists('role',$_SESSION) || $_SESSION['role'] > 2)
		raise_http_error(403);

	//should never be posted to. We always will hit the API.
	if($_SERVER['REQUEST_METHOD'] != 'GET')
		raise_http_error(400);

	$title = "Quiz Editor";
	require_once('../templates/header.php');

?>
	<div clas="col-md-12" id="alert-msg"></div>
	<div class="row">
		<div class="col">
			<!-- Eventually I'm going to do bootstrap select here to make it look better.	-->
			<label for="quizzes">Quiz</label>
			<select id="quizzes" class="form-select bg-dark text-white">
			</select>
		</div>
		<div class="col">
			<button id="get_quiz" type="button" name="retrieve button" class="btn btn-dark" aria-details="Press to retrieve selected quiz.">
				Retrieve Quiz
			</button>
		</div>
	</div>
	<div class="row">
		<div class="col">

		</div>
	</div>

	<div class="modal fade" id="quiz_modal" role="dialog" aria-labelledby="quiz_modal_title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h2 class="modal-title text-center w-100" id="quiz_modal_title">
						<div id="quiz_desc_container">
							<span id="quiz_description">Click to Enter Quiz Title</span>
							<input id="quiz_desc_input" hidden="true" value="" maxlength="100"/>
						</div>
					</h2>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row mt-1 mb-2">
						<div class="col-12">
							<table class="table table-striped">
								<thead>
									<tr>
										<td class="text-center font-weight-bold w-10">
											Question Number
										</td>
										<td class="text-center font-weight-bold w-80">
											Question Title
										</td>
										<td class="text-center font-weight-bold w-10">
											Edit
										</td>
									</tr>
								</thead>
								<tbody id="questions">

								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="row w-80 d-flex flex-row">
						<div class="p-2 mr-auto">
							<button id="add_question" type="button" class="btn btn-primary align-left" >Add Question</button>
						</div>
						<div class="p-2">
							<button id="update_quiz" type="button" class="btn btn-primary align-left" data-id="-1">Update Quiz Description</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="question_modal" role="dialog" aria-labelledby="question_modal_title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h2 class="modal-title text-center w-100" id="quiz_modal_title">
						<div id="quiz_desc_container">
							<span id="question_description">Click to Enter Question Text</span>
							<input id="question_desc_input" hidden="true" value="" maxlength="100"/>
						</div>
					</h2>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row mt-1 mb-2">
						<div class="col-12">
							<table class="table table-striped">
								<thead>
								<tr>
									<td class="text-center font-weight-bold w-10">
										Answer Number
									</td>
									<td class="text-center font-weight-bold w-65">
										Answer Title
									</td>
									<td class="text-center font-weight-bold w-15">
										Correct
									</td>
									<td class="text-center font-weight-bold w-10">
										Edit
									</td>
								</tr>
								</thead>
								<tbody id="answers">

								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="row w-80 d-flex flex-row">
						<div class="p-2 mr-auto">
							<button id="add_answer" type="button" class="btn btn-primary align-left" >Add Answer</button>
						</div>
						<div class="p-2">
							<button id="update_question" type="button" class="btn btn-primary align-left" data-id="-1" data-quizid="-1">Update Question Data</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="answer_modal" role="dialog" aria-labelledby="answer_modal_title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h2 class="modal-title text-center w-100" id="answer_modal_title">
						<div id="quiz_name">
							<span id="question_answer_title" data-id="-1">Question Title</span>
						</div>
					</h2>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
				   <div class="row mt-1 mb-2">
							<span id="answers_id">-1</span>
				 	  <div class="col-6">
						  <label for="answer_text">Answer: </label>
						  <input type="text" maxlength="255" id="answer_text"/>
					   </div>
					   <div class="col-2">
						   <label for="answer_correct">Correct</label>
						   <input type="checkbox" class="custom-checkbox" id="answer_correct" />
					   </div>
				   </div>
			    </div>
				<div class="modal-footer">
					<div class="row w-80 d-flex flex-row">
						<div class="p-2">
							<button id="update_answer" type="button" class="btn btn-primary align-left" data-id="-1">Update Answer Description</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<!--
UX flow
First I'm going to have them select from their list of quizzes. Then after selecting one a modal dialog would show up and then it'd list the answers that are currently part of that quiz. With an "add quiz" button.
Clicking that button or clicking the "edit" button(will be a glyph from fontawesome), it'll open up the answer with the answers letting them edit the answers and etc in a new modal.

After closing the  answer modal then it'll show the original answer modal with the state in the original position.
-->
<script type="text/javascript" src="/js/authed.js"></script>
<script type="text/javascript">
	"use strict";
	document.getElementById('get_quiz').addEventListener("click", event=>{
		const el = document.getElementById('quizzes');
		const option = el.options[el.selectedIndex];
		if(option.id !== '-1')
			document.getElementById('quiz_description').innerText = option.innerText;
		else{
			document.getElementById('quiz_description').innerText = 'Click to Change Quiz Title';
		}
		document.getElementById('quiz_desc_input').hidden = true;
		document.getElementById('quiz_description').hidden = false;
		get_quiz(parseInt(option.id));
	});

	document.getElementById('quiz_description').addEventListener('click',e=>{
		const desc = document.getElementById('quiz_description');
		const input = document.getElementById('quiz_desc_input');
		input.value = desc.innerText;
		input.addEventListener('keydown',event=>{
			if(event.keyCode === 13 || event.key === "Enter"){
				const quiz_input = document.getElementById('quiz_desc_input');
				const quiz_desc = document.getElementById('quiz_description');
				quiz_input.hidden = true;
				quiz_desc.hidden = false;
				quiz_desc.innerText = quiz_input.value;
			}
		})
		input.hidden = false;
		desc.hidden = true;
	});

	document.getElementById('add_question').addEventListener('click',e=>{
	  document.getElementById('update_question').dataset.quizid = document.getElementById('add_question').dataset.id;
		modal_question(-1)
	});

	document.getElementById('add_answer').addEventListener('click',e=>{

	});

	document.getElementById('update_quiz').addEventListener('click',event=>{
		const quiz_id = document.getElementById('update_quiz').dataset.id;
		let name = document.getElementById('quiz_description').innerText;
		let data = {
			'quiz_name': name,
			'cmd':'update_quiz'
		};
		if(quiz_id === '-1') {
			data['cmd'] = 'create_quiz';
		}
		else
			data['quiz_id'] = quiz_id;
		submit('/main/api.php',data,res=>{
			if(res['ok'] && res['quiz_id']) {
				document.getElementById('update_quiz').dataset.id = res['quiz_id'];
			}
		});
	});

	document.getElementById('update_question').addEventListener('click',e=>{
		const update_el = document.getElementById('update_question');
		const quiz_id = update_el.dataset.quizid;
		const question_id = update_el.dataset.id;

		let answers_changed = [];

		document.querySelectorAll('.question_answer_correct').forEach(e=>{
			//since the correct dataset attribute is a 1 digit int we have to let javascript do type-coercion.
			let correct = parseInt(el.dataset.correct);
			if(el.checked != correct){
				answers_changed.append({
						'id':el.dataset.id,
						'correct':correct
				});
			}
		});
		submit('/admin/api.php',{
			'cmd':'update_question',
			'quiz_id':quiz_id,
			'question_id':question_id,
			'text':document.getElementById('question_description').innerText
		});
		if(answers_changed.length !== 0){
			submit('/admin/api.php',{
				'cmd':'update_answers',
				'changed_answers':answers_changed
			});
		}

	});
	get_quizzes();
</script>
<?php include('../templates/footer.php'); ?>

"use strict";
/**
 *
 * @param quiz_id {number}
 */
function get_quiz(quiz_id){
	if(quiz_id === -1){
		$('#quiz_modal').modal('toggle');
	}
	else{
		document.getElementById('add_question').dataset.id = quiz_id;
		submit('/admin/api.php',{
			'cmd':'get_questions',
			'quiz_id':quiz_id,
		},parse_questions);
	}
}
function parse_questions(result){
	if(!result['ok'])
		console.log(result['error']);

		let content = '';
		for (let i = 0; i < result['num']; i++) {
			content += `<tr>
						<td>${i}</td>
						<td id="question-${result['results'][i].id}-name">${result['results'][i].text}</td>
						<td><a href="#" data-id="${result['results'][i].id}" class="edit_question">Edit</a></td>
					</tr>`
		}

		document.getElementById('questions').innerHTML = content;
		document.querySelectorAll('.edit_question').forEach(el => {
			el.addEventListener('click', e => {
				e.preventDefault();

				modal_question(el.dataset.id);
			});
		});
		$('#quiz_modal').modal('toggle');
}

function get_quizzes(){
	submit('/admin/api.php',{'cmd':'edit_quiz'},resp=>{
		if(resp.ok){
			let options = '';
			if(resp.rows === 0) {
				options = `<option id='-1'>There are no quizzes currently please create one</option>`
				document.getElementById('get_quiz').innerText = 'Create Quiz';
			}
			else{
				for(let i=0;i<resp.rows;i++){
					let row = resp.results[i];
					options += `<option id='${row.quiz_id}'>${row.quiz_name}</option>`;
				}
				options += '<option id="-1">Create new Quiz</option>';
			}
			document.getElementById('quizzes').innerHTML = options;
		}
		else{
			document.getElementById('alert_msg').innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert" id="alert">${resp.error}</div>`;
		}
	});
}

function modal_question(question_id){
	const el  = document.getElementById('update_question');
	el.dataset.id = question_id;
	el.dataset.quizid = document.getElementById('update_quiz').dataset.id;
	if(question_id === -1)
		$('#question_modal').modal('toggle');
	else{
		document.getElementById('question_description').innerText = document.getElementById(`question-${question_id}-name`).innerText;
		submit('/admin/api.php',{
			'cmd':'edit_question',
			'quiz_id':el.dataset.quizid,
			'question_id':el.dataset.id
		},result=>{

			let content = '';
			for (let i = 0; i < result['num']; i++) {
				let correct = result['answers'][i].correct === 1
				content += `<tr>
						<td>${i}</td>
						<td id="answer-${result['answers'][i].id}-name">${result['answers'][i].text}</td>
						<td><input data-id="${result['answers'][i].id}" data-correct="${correct}" type="checkbox" checked="${correct}" class="question_answer_correct"/></td>
						<td><a href="#" data-id="${result['answers'][i].id}" class="edit_answer">Edit</a></td>
					</tr>`
			}
			document.querySelectorAll('edit_answer').forEach(el=>{
				el.addEventListener('click',e=>{
					e.preventDefault();
					modal_answer(el.dataset.id);
				})
			})
		})
	}
}

function modal_answer(answer_id){

}
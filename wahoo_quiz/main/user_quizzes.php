<?php
session_start();
if(!array_key_exists('id',$_SESSION))
	header('Location: /login.php');
//for now this page will be shown to everyone for their quizzes they created.

require_once '../config.php';
require_once 'functions.php';
$title = $_SESSION['username'].'\'Quizzes Page';
$res = $QUIZ->query('select id,name from quizzes where owner_id='.$_SESSION['id']);
error_log(print_r($res,true));
include '../templates/header.php';
$_SESSION['quiz_ids'] = array();
?>
<div class="mb-2 alert alert-info alert-dismissible fade show hidden" id='alert' role="alert">
	<strong id="alert-txt">Quiz URL Copied to your Clipboard.</strong>
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
<div class="row">
	<div class="col-1"></div>
	<div class="col-10">
		<table class="table table-striped">
			<thead>
			<tr class="font-weight-bold text-center">
				<td>Quiz Title</td>
				<td><span title="If your browser supports clicking this link will copy it to your clipboard. Otherwise manually copy it.">Click to get Quiz URL/Highlight for more details.</span></td>
				<td>Quiz Analytics</td>
			</tr>
			</thead>
			<?php while($row=$res->fetch_row()):?>
			<tr>
				<td id="quiz_name_<?PHP echo $row[0]; ?>"><?php
						echo $row[1];
						$token=b64_encode(pack('NNC',$row[0],$_SESSION['id'],($row[0]^$_SESSION['id']) & 255));
						array_push($_SESSION['quiz_ids'],$row[0]);
						?></td>
				<td><a href="<?php echo $_SERVER['host'].'/?token='.$token; ?>" class="get-quiz-token" data-token="<?php echo $token; ?>" ><?php echo $token;?></a></td>
				<td><a href="#" class="get-quiz-analytics" data-id="<?php echo $row[0]; ?>">Get Analytics</a></td>
			</tr>
			<?php endwhile; ?>
		</table>
	</div>
	<div class="col-1"></div>
</div>

<div class="modal fade" id="quiz_modal" role="dialog" aria-labelledby="quiz_modal_title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h2 class="modal-title text-center w-100" id="">
					<div>
						<span id="quiz_title" data-id="-1">Quiz Title</span>
					</div>
				</h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-striped">
					<thead>
						<tr>
							<td class="text-center font-weight-bold w-5">
								Question Number
							</td>
							<td class="text-center font-weight-bold w-70">
								Question Text
							</td>
							<td class="text-center font-weight-bold w-7">
								Correct %
							</td>
							<td class="text center font-weight-bold w-7">
								Answered
							</td>
							<td class="text-center font-weight-bold w-11">
								Answer Details
							</td>
						</tr>
					</thead>
					<tbody id="questions">

					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<div class="row w-80 d-flex flex-row">
					<div class="p-2">

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	"use strict";
	function show_alert(msg,timeout=4000,error=false){
	  document.getElementById('alert-txt').innerText = msg;
	  const el = $('#alert');
	  if(error)
	  	el.addClass('alert-danger');
	  else
			el.removeClass('alert-danger');
	  el.show();
	  setTimeout(function(){
		  $('#alert').hide();
	  },timeout+250);
  }
	document.querySelectorAll('.get-quiz-token').forEach(el=>{
		el.addEventListener('click',e=>{
			e.preventDefault();
			show_alert("Quiz URL Copied to your Clipboard.");
			copyToClipBoard(el.href);
	});
	});
	document.querySelectorAll('.get-quiz-analytics').forEach(el=>{
		el.addEventListener('click',e=>{
			e.preventDefault();
			get_quiz(el.dataset.id);
		});
	});

  /**
	 * Gets the analytics for a specific quiz.
	 *
   * @param quiz_id number The quiz id we're getting analytics for.
   */
  function get_quiz(quiz_id){
  	document.getElementById('quiz_title').innerText = document.getElementById(`quiz_name_${quiz_id}`).innerText;
  	submit('/admin/api.php',{
  		'cmd':'get_question_analytics',
  		'quiz_id':parseInt(quiz_id),
		},res=>{
			if(!res['ok']) {
		  	console.log(res['error']);
		  	show_alert('Error: '+res['error'],5000,true);
	  	}
			else {

				let content = '';
				if (res['rows'] === 0) {
					content = `<tr><td>No one's answered any questions.</td></tr>`
				}
				else {
					for (let i = 0; i < res['rows']; i++) {
						const row = res['result'][i];
						content += `<tr><td>${i}</td>
												<td>${row['text']}</td>
												<td>${Math.round((row['correct'] / row['answered']) * 100)}<td>
												<td>${row['answered']}</td>
												<td class='answer_analytics' data-id='${row['id']}'>Answer Analytics</td><tr/>`;
					}
				}
				console.log(content);
			  document.getElementById('questions').innerHTML = content;
		  	$('#quiz_modal').modal('toggle')
	  	}
;
		});

	}
</script>
<?php include '../templates/footer.php'; ?>

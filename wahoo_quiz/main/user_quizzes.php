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
?>
<div class="row">
	<div class="col-12">
	</div>
</div>
<div class="row">
	<div class="col-1"></div>
	<div class="col-10">
		<table class="table table-striped">
			<thead>
			<tr class="font-weight-bold text-center">
				<td>Quiz Title</td>
				<td>Quiz Token/Get Quiz URL</td>
				<td>Quiz Analytics</td>
			</tr>
			</thead>
			<?php while($row=$res->fetch_row()):?>
			<tr>
				<td><?php echo $row[1]; ?></td>
				<td><a href="#" class="get-quiz-token" data-token="<?php $token=b64_encode(pack('NNC',$row[0],$_SESSION['id'],($row[0]^$_SESSION['id']) & 255)); echo $token; ?>"><?php echo $token; ?></a></td>
				<td><a href="#" class="get-quiz-analytics" data-id="<?php echo $row[0]; ?>">Analytics</a></td>
			</tr>
			<?php endwhile; ?>
		</table>
	</div>
	<div class="col-1"></div>
</div>
<?php include '../templates/footer.php'; ?>

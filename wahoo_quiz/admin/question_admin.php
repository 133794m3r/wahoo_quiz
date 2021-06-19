<?php
	session_start();
	require_once '../main/functions.php';
	if(!array_key_exists('role',$_SESSION) || $_SESSION['role'] > 1)
		raise_http_error(403);
	$title = "Quiz Admin";
	require_once('../templates/header.php');
	require_once('../config.php');
	$res = $QUIZ->query('select q.id,q.name,u.username from quizzes q inner join users u on q.owner_id = u.id order by u.id');
	?>
	<div class="row">
		<div class="col">
			<select id="quizzes">
				<?php if($res->num_rows != 0): ?>
				<?php while($row = $res->fetch_row()): ?>
					<?php error_log(print_r($row,true));?>
					<option id="<?php echo $row[0];?>"><?php echo "$row[1] by $row[2]";?></option>
				<?php endwhile; ?>
				<?php else: ?>
					<option id="-1">You have no quizzes currently. Please create one.</option>
				<?php endif; ?>
			</select>
		</div>
		<div class="col">
			<button type="button" name="retrieve button" class="button" aria-details="Press to retrieve selected quiz.">
				Retrieve Quiz
			</button>
		</div>
	</div>
	<div class="row">
		<div class="col">

		</div>
	</div>


	<script type="text/javascript">

	</script>
<?php include('../templates/footer.php'); ?>



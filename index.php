<?php
session_start();
require_once 'config.php';
if($_SERVER['REQUEST_METHOD'] == 'POST'){

}
else{
	if(isset($_GET['quiz_id'])){
		$stmt = $QUIZ->prepare('select name, id from quizzes where id = ?');
		$stmt->bind_param('d',$_GET['quiz_id']);
		$stmt->bind_result($q_name, $q_id);
		$res = $stmt->fetch();
		if($res){
			$stmt = $QUIZ->prepare('select ');
		}
		else{
			echo "2";
		}
	}
}
?>
<?php
$title="Wahoo Quiz";include('templates/header.php');
?>


<?php include('templates/footer.php'); ?>

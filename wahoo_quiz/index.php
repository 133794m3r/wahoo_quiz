<?php
session_start();
require_once 'config.php';
if($_SERVER['REQUEST_METHOD'] == 'POST'){

}
else{
	if(isset($_GET['token'])){

	}
}
?>
<?php
$title="Wahoo Quiz";
include('templates/header.php');
?>


<?php include('templates/footer.php'); ?>

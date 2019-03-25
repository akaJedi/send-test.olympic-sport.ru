<?php #unset($_SESSION['dev']);
#if(isset($_GET['d'])) { $_SESSION['dev'] = true; }
/*
if(!isset($_SESSION['dev'])){
  die('доступ запрещен, На данный момент проводятся технические работы');
}else{
  $DEV=true;
}*/


if(isset($_POST["login"])&&isset($_POST["password"])&&!empty($_POST["login"])&&!empty($_POST["password"]))
{
	$db = mysqli_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysqli_select_db($db, CFG_DB_DATABASE);
	$res=mysqli_query($db, "SELECT COUNT(*) FROM `mail_user` WHERE Name='".mysqli_escape_string($db, $_POST["login"])."' AND Password='".mysqli_escape_string($db, $_POST["password"])."'");
	$c=mysqli_fetch_row($res);
	if($c && $c[0]>0)
	{
		$_SESSION["login"]=$_POST["login"];
	}
	mysqli_close($db);
}
?>
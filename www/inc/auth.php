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
	mysql_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysql_select_db(CFG_DB_DATABASE);
	$res=mysql_query("SELECT COUNT(*) FROM `mail_user` WHERE Name='".mysql_escape_string($_POST["login"])."' AND Password='".mysql_escape_string($_POST["password"])."'");
	$c=mysql_result($res,0);
	if($c>0)
	{
		$_SESSION["login"]=$_POST["login"];
	}
	mysql_close();
}
?>
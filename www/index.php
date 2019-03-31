<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL & ~E_NOTICE);
$fpath=dirname(__FILE__);
require_once $fpath. '/lib/KLogger.php';

$log = new KLogger($fpath.'/log/', KLogger::DEBUG);


include_once("config.php");
include_once("inc/auth.php");
require_once 'functions.php';

define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
if(!IS_AJAX):
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Сервис отправки почты</title>
	<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
	<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script src="/js/common.js?v=1"></script>
	<script type="text/javascript" src="js/tiny_mce/tiny_mce.js"></script>
	<style>
	body
	{
		font-family:verdana;
	}
	</style>
</head>
<body>
<center>
<h2>Сервис отправки почты</h2>
<br />
<hr />
<a href="index.php">Отправка почты</a> | 
<a href="index.php?view=1">История рассылки почты</a> | 
<a href="index.php?view=3">Подписчики</a> | 
<a href="index.php?view=4">Загрузка файлов</a> | 
<a href="index.php?view=5">Справка</a>
<hr />
</center>
<?php
endif;

if(isset($_SESSION["login"])&&!empty($_SESSION["login"]))
{
	if(isset($_GET["view"]))
	{
		switch(intval($_GET["view"]))
		{
			 case 1:
				include_once("inc/maillist.php");
				break;
			 case 2:
				include_once("inc/showmail.php");
				break;
			 case 3:
				include_once("inc/customer.php");
				break;
			 case 4:
				include_once("inc/fileupload.php");
				break;
			 case 5:
				include_once("inc/help.php");
				break;
		}
	}
	else
	{
		include_once("inc/sendmail.php");
	}
}
else
{
	include_once("inc/login.php");
}

if(!IS_AJAX):
?>
</body>
</html>
<?php
endif;
?>
<?php
	mysql_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysql_select_db(CFG_DB_DATABASE);
	mysql_query("SET NAMES 'utf8'"); 

	if(isset($_GET["delete"]))
	{
		mysql_query("DELETE FROM `mails` WHERE Id='".mysql_escape_string($_GET["delete"])."'");
	}
	$res=mysql_query("SELECT * FROM `mails` ORDER BY `CreationDate` DESC LIMIT 1000");
	$list=array();
	while($data=mysql_fetch_assoc($res))
	{
		/*
		if(strtotime($data["CreationDate"])>1349963843)
		{
			$data["FromName"]=iconv("WINDOWS-1251","UTF-8",$data["FromName"]);
			$data["ToName"]=iconv("WINDOWS-1251","UTF-8",$data["ToName"]);
			$data["Title"]=iconv("WINDOWS-1251","UTF-8",$data["Title"]);
		}*/
		
		$list[]=$data;
	}
	mysql_close();
?>
<table border="1" align="center">
	<tr>
		<td>От кого</td>
		<td>Организация</td>
		<td>Кому</td>
		<td>Псевдоним получателя</td>
		<td>Тема</td>
		<td>Дата</td>
		<td></td>
	</tr>
<?php
foreach($list as $data)
{
?>
	<tr>
		<td><?=$data["FromEmail"]?></td>
		<td><?=$data["FromName"]?></td>
		<td><?=$data["To"]?></td>
		<td><?=$data["ToName"]?></td>
		<td><?=$data["Title"]?></td>
		<td><?=$data["CreationDate"]?></td>
		<td>
			<a href="index.php?view=2&id=<?=$data["Id"]?>">Просмотр</a> 
			<a href="index.php?view=1&delete=<?=$data["Id"]?>">Удалить</a>
		</td>
	</tr>
<?php
}
?>
</table>
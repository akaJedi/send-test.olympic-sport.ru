<?php
	mysql_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysql_select_db(CFG_DB_DATABASE);
	#mysql_query('SET NAMES utf-8');
	$res=mysql_query("SELECT * FROM `mails` WHERE Id='".$_GET["id"]."'");
	$data=mysql_fetch_assoc($res);
	
	mysql_close();
?>
<table border="1" align="center">
	<tr>
		<td>От кого</td>
		<td><?=$data["FromEmail"]?></td>
	</tr>
	<tr>
		<td>Организация</td>
		<td><?php if(strtotime($data["CreationDate"])>1349963843)echo iconv("WINDOWS-1251","UTF-8",$data["FromName"]);else echo $data["FromName"]; ?></td>
	</tr>
	<tr>
		<td>Кому</td>
		<td><?=$data["To"]?></td>
	</tr>
	<tr>
		<td>Псевдоним получателя</td>
		<td><?php if(strtotime($data["CreationDate"])>1349963843)echo iconv("WINDOWS-1251","UTF-8",$data["ToName"]);else echo $data["ToName"]; ?></td>
	</tr>
	<tr>
		<td>Тема</td>
		<td><?php if(strtotime($data["CreationDate"])>1349963843)echo iconv("WINDOWS-1251","UTF-8",$data["Title"]);else echo $data["Title"]; ?></td>
	</tr>
	<tr>
		<td>Дата</td>
		<td><?=$data["CreationDate"]?></td>
	</tr>
<?php if(!empty($data['files'])): ?>	
	  <tr>
		<td colspan="2">
			<b>Прикрепленные файлы:</b>
	<br />

<table align="center">
	<?php
	$files = explode("\n", $data['files']);
	foreach($files as $file)
	{
	$file= urldecode(iconv('cp1251','utf-8',$file));
		?>
	<tr>
		<td align="center">
			<a href="http://www.send-test.olympic-sport.ru/images/upload/<?=$file?>">
				<?php
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				if( in_array($ext, array('png','jpeg','jpg', 'gif', 'pjpeg')) ):
				?>
				<img width="100" src="http://www.send-test.olympic-sport.ru/images/upload/<?=$file?>" border="0" />
			<?php else: ?>
			<br /><br /><br />
				<?php endif; ?>
			</a>
		</td>
		<td align="left">
			<a href="http://www.send-test.olympic-sport.ru/images/upload/<?=$file?>"><?=$file?></a>
		</td>
	</tr>
		<?php
	}
	?>
</table>

<? endif; ?>





		</td>
	</tr>
	<tr>
		<td colspan="2">
			Письмо
			<br /><?=iconv("WINDOWS-1251","UTF-8",$data["Body"])?>
		</td>
	</tr>
</table>
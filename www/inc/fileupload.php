<table align="center">
	<tr>
		<td align="center">
<FORM ENCTYPE="multipart/form-data" METHOD="POST">
Файл: <INPUT TYPE="file" NAME="userfile">
<INPUT TYPE="submit" name="upload" VALUE="Загрузить">
</FORM>
<?php
$path = $fpath."/images/upload/";
if(isset($_REQUEST["file"]))
{
	$fileName=urldecode($_REQUEST["file"]);
	unlink($path.$fileName);
}
if(isset($_REQUEST["upload"]))
{
	$max_size = 200000000;
	if (isset($HTTP_POST_FILES['userfile']))
	{
		if (is_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name']))
		{
			if ($HTTP_POST_FILES['userfile']['size']>$max_size)
			{
				echo "Файл слишком большой<br>\n";
				exit;
			}
			#if (($HTTP_POST_FILES['userfile']['type']=="image/gif") || ($HTTP_POST_FILES['userfile']['type']=="image/pjpeg") || ($HTTP_POST_FILES['userfile']['type']=="image/png") || ($HTTP_POST_FILES['userfile']['type']=="image/jpeg") || ($HTTP_POST_FILES['userfile']['type']=="application/pdf"))
			#{
				if (file_exists($path . $HTTP_POST_FILES['userfile']['name']))
				{
					//echo "Файл по адресу <a href='http://www.send-test.olympic-sport.ru/images/upload/".$HTTP_POST_FILES['userfile']['name']."'>http://www.send-test.olympic-sport.ru/images/upload/".$HTTP_POST_FILES['userfile']['name']."</a> уже загружен<br>\n";
				}
				$res = copy($HTTP_POST_FILES['userfile']['tmp_name'], $path.$HTTP_POST_FILES['userfile']['name']);
				if (!$res)
				{
					echo "загрузка неудачна!<br>\n";
					exit;
				}
				else
				{
					echo "Загрузка успешна<br>\n";
				}
				echo "Название: ".$HTTP_POST_FILES['userfile']['name']."<br>\n";
				echo "Размер: ".$HTTP_POST_FILES['userfile']['size']." байтов<br>\n";
				echo "Тип: ".$HTTP_POST_FILES['userfile']['type']."<br>\n";
				echo "Ссылка: <a href='http://www.send-test.olympic-sport.ru/images/upload/".$HTTP_POST_FILES['userfile']['name']."'>http://www.send-test.olympic-sport.ru/images/upload/".$HTTP_POST_FILES['userfile']['name']."</a><br>\n";
			#}
			#else
			#{
			#	echo "Не правильный тип файла<br>\n";
			#}
		}
	}
}
?>
		</td>
	</tr>
</table>
<?php

$files=array();
if($handle=opendir($fpath."/images/upload/"))
{
    while(false!==($file=readdir($handle)))
	{
        if($file!="."&&$file!="..")
		{
            $files[]=$file; 
        }
    }
    closedir($handle); 
}
?>
<table align="center">
	<tr>
		<td colspan="2" align="center">Список загруженых изображений:</td>
	</tr>
	<?php
	foreach($files as $file)
	{
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
			<br /><?=$file?><br /><br />
				<?php endif; ?>
			</a>
		</td>
		<td align="left">
			<a href="http://www.send-test.olympic-sport.ru/images/upload/<?=$file?>">http://www.send-test.olympic-sport.ru/images/upload/<?=str_replace(" ","%20",$file)?></a>
		</td>
		<td align="left">
			<a href="index.php?view=4&file=<?=urlencode($file)?>">Удалить</a>
		</td>
	</tr>
		<?php
	}
	?>
</table>
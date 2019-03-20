<form name="ImportCustomers" method="POST" enctype="multipart/form-data">
<table border="0" align="center">
	<tr>
		<td>Файл(xls):</td>
		<td><input type="file" name="file" /></td>
		<td>Группа:</td>
		<td>
			<select name="Group">
				<?php
					foreach($glist as $dt)
					{
						?>
						<option value="<?=$dt["Id"]?>"><?=$dt["Name"]?></option>
						<?php
					}
				?>
			</select>
		</td>
		<td><input type="submit" name="ImportCustomers" value="Импорт подписчиков из файла" /></td>
	</tr>
</table>
</form>
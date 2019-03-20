<form name="CustomerForm" method="POST">
<input type="hidden" name="Id" value="<?php echo !empty($Id) ? (int)$Id : ''; ?>" />
<table border="0" align="center">
 <?php if(!empty($error)) echo '<tr><td colspan="5" class="error" style="color: red;">'.$error.'</td></tr>' ?>
	<tr>
		<td>Организация:</td>
		<td><input type="text" name="Organization" value="<?php echo !empty($Organization) ? htmlspecialchars($Organization) : ''; ?>" /></td>
		<td>Псевдоним:</td>
		<td><input type="text" name="Name" value="<?php echo !empty($Name) ? htmlspecialchars($Name) : ''; ?>" /></td>
		<td>Email:</td>
		<td><input type="text" name="Email" value="<?php echo !empty($Email) ? htmlspecialchars($Email) : ''; ?>" /></td>
		<td>Группа:</td>
		<td>
			<select name="Group">
				<?php 
					foreach($glist as $dt)
					{
						?>
						<option value="<?=$dt["Id"]?>" <?php if($Group == $dt["Id"]) echo 'selected="selected"'; ?>><?=$dt["Name"]?></option>
						<?php
					}
				?>
			</select>
		</td>
		<td><input type="submit" name="AddCustomer" value="Сохранить" /></td>
	</tr>
</table>
</form>
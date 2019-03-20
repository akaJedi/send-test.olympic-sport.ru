<?php
ini_set('display_errors',1);
	mysql_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysql_select_db(CFG_DB_DATABASE);
	mysql_query("SET NAMES 'utf8'");
//mysql_query("SET NAMES 'utf8'");
/*
mysql_query("SET collation_connection = 'UTF-8_unicode_ci'");
mysql_query("SET collation_server = 'UTF-8_unicode_ci'");
mysql_query("SET character_set_client = 'UTF-8'");
mysql_query("SET character_set_connection = 'UTF-8'");
mysql_query("SET character_set_results = 'UTF-8'");
mysql_query("SET character_set_server = 'UTF-8'");*/

	/**
	 * success_msg  Выводим сообщение
	 */
	if(isset($_SESSION['success_msg'])){
		echo "<div class='success' style='font-size: 22px;color: green;margin:10px;'>".$_SESSION['success_msg'].'</div>';
		unset($_SESSION['success_msg']);
	}




	$res=mysql_query("SELECT * FROM customer_groups GROUP BY Name");
	$glist=array();
	while($data=mysql_fetch_assoc($res))
	{
		$glist[]=$data;
	}

	$filterWhere="";

	if(isset($_REQUEST["FilterGroup"])&&!empty($_REQUEST["FilterGroup"]))
	{
		$filter=$_REQUEST["FilterGroup"];
		$filterWhere=" WHERE `customer_groups`.`Id`='".$filter."'";
	}



	if(isset($_POST["Delete"]))
	{
		mysql_query("DELETE FROM customer_groups WHERE Id='".mysql_escape_string($_POST["FilterGroup"])."'");
	}
	if(isset($_GET["delete_cid"]))
	{
		mysql_query("DELETE FROM customers WHERE Id='".$_GET["delete_cid"]."'") or die('Error: '.mysql_error());
        !IS_AJAX or die('success');
        $_SESSION['success_msg']='Адресат удален!';
        header('Location: /index.php?view=3');
	}

	if(isset($_POST["AddGroup"])&&isset($_POST["GroupName"])&&!empty($_POST["GroupName"]))
	{
		mysql_query("INSERT INTO customer_groups (Name) VALUES('".$_POST["GroupName"]."')");
	}


if ( isset($_POST["AddCustomer"]) || isset($_GET["Edit"]) ) {
    if ( isset($_POST["AddCustomer"]) && empty($_POST['Id']) )
        $action = 'insert';
    elseif ( isset($_GET["Edit"]) )
        $action = 'update';

    $redirect_to = '/index.php?view=3&FilterGroup=';

    if ( !$_POST && $action == 'update' ) {
        $Id = (int) $_GET["Edit"];
        $result = mysql_query("SELECT * FROM customers WHERE Id='" . $Id . "'");
        $customer = mysql_fetch_assoc($result);
        extract($customer);
        $Group = $GroupId;
        require 'templates/CustomerForm.php';
        exit();
    }

    if ( $_POST ) {
        array_map("trim", $_POST);
        extract($_POST);
        $Id = (int) $Id;
        $Group = (int) $Group;

        $error = false;
        $error = validateCustomer($Name, $Organization, $Email);

        if ( !$error ) {
            $sql = $action == 'insert' ? "INSERT INTO " : "UPDATE ";
            $sql .= "customers SET ";
            $sql .= "Name = '" . mysql_escape_string($Name) . "', ";
            $sql .= "Email = '" . mysql_escape_string($Email) . "', ";
            $sql .= "GroupId = " . $Group . ", ";
            $sql .= "Organization = '" . mysql_escape_string($_POST["Organization"]) . "'";
            $sql .= $action == 'update' ? " WHERE Id = " . $Id : "";

            #echo $sql; exit();

            $result = mysql_query($sql);

            if ( !$result ) {
                $error = "Не удалось выполнить запрос на добавление подписчика.<br />" . mysql_errno() . ": " . mysql_error();
            } else {
                $success = "Подписчик " . $Name . " «" . $Email . "» успешно " . ($action == 'insert' ? "добавлен" : "сохранен");
                $_SESSION['success_msg'] = $success;
                header('Location: '.$redirect_to.$Group);
                exit();
            }
        }


        if ( $error ) {
            require 'templates/CustomerForm.php';
            exit();
        }
    }
}



if ( isset($_POST["ImportCustomers"]) ) {
    $file = uploadFile(array('xls'));

    if ( $file[0] === true ) {
        // Начинаем парсить xls
        require_once CFG_PATH_ROOT . 'lib/excel/reader.php';

        $data = new Spreadsheet_Excel_Reader();

        $data->setOutputEncoding('UTF-8');

        $data->read($file[1]);

        echo '<br /><div style="width: 1000px; margin: 0 auto;">';

        if ( $data->sheets[0]['numRows'] <= 0 ) {
            echo 'Файл пуст';
        }

        $error = false;
        $errors = array();

        $q = "INSERT INTO customers (Name, Email, GroupId, Organization) VALUES ";
        $import_values = array();
        $import_customers_html = '<table border="1">';
        for ( $i = 2; $i <= $data->sheets[0]['numRows']; $i++ )
        {
            $Organization = smcf_filter($data->sheets[0]['cells'][$i][1]);
            $Name = smcf_filter($data->sheets[0]['cells'][$i][2]);
            $Email = smcf_filter($data->sheets[0]['cells'][$i][4]);

            if( empty($Name) && empty($Email)) break;

            if( ($error = validateCustomer($Name, $Organization, $Email)) !== FALSE){
                $errors[] = sprintf("<b>Ряд %d</b> подписчик %s %s: %s", $i, $Name, $Email, $error);
            }

            if(count($errors)>0) continue;

            $import_values[] = "('" . mysql_escape_string($Name) . "', '" . mysql_escape_string($Email) . "', '" . (int) $_POST["Group"] . "', '" . mysql_escape_string($Organization) . "')";

            $import_customers_html .= '<tr><td>' . $Organization . '</td><td>' . $Name . '</td><td>' . $Email . "</td></tr>";
        }

        if(count($errors) > 0)
        {
            echo '<strong>В таблице найдены ошибки:</strong><br />Исправьте их и повторите импорт.<br /><br /><div style="font: 13px/24px Arial;color:red;">';
            echo '-- '.implode('<br />-- ', $errors);
            echo '</div><br /><br />';
            require 'templates/ImportCustomers.php';
        }
        else
        {
            $import_customers_html .= '</table><br />';
            $q .= implode(', ', $import_values);

            //mysql_query("SET NAMES 'utf8'");
            $result = mysql_query($q);

            if ( !$result ) {
                $error = "Не удалось выполнить запрос на импорт подписчиков.<br />" . mysql_errno() . ": " . mysql_error();
            } else {
                echo '<b>Импотировано ' . count($import_values) . ' подписчиков!</b><br />' . $import_customers_html;
            }

        }
        //echo '<br /><br /><hr />'.$q;

        echo '</div>';

        unlink($file[1]);
        exit();
    } else {
        echo $file[1];
    }
}





$res=mysql_query("SELECT `customers`.*,`customer_groups`.`Name` as GroupName FROM `customers` INNER JOIN  customer_groups ON `customer_groups`.Id=`customers`.`GroupId` $filterWhere LIMIT 1000");
	$list=array();
	while($data=mysql_fetch_assoc($res))
	{
		$list[]=$data;
	}

	mysql_close();
?>


<form name="GroupForm" method="POST">
<table border="0" align="center">
	<tr>
		<td>Название группы:</td>
		<td><input type="text" name="GroupName" /></td>
		<td><input type="submit" name="AddGroup" value="Добавить группу" /></td>
	</tr>
</table>
</form>

<?php require 'templates/CustomerForm.php'; ?>
<?php require 'templates/ImportCustomers.php'; ?>



<hr />
<form name="FilterForm" method="POST">
<table align="center">
	<tr>
		<td>Фильтр:</td>
		<td>
			По группе
			<select name="FilterGroup">
				<option value="">Все</option>
				<?php
					foreach($glist as $dt)
					{
						?>
						<option value="<?=$dt["Id"]?>" <?php if(isset($filter)&&($dt["Id"]==$filter))echo "selected='selected'";?>><?=$dt["Name"]?></option>
						<?php
					}
				?>
			</select>

		</td>
		<td>
			<input type="submit" name="Filter" value="Выбрать" />
			<?php
			if(isset($filter))
			{
			?>
			<input type="submit" name="Delete" value="Удалить" />
			<?php
			}
			?>
		</td>
	</tr>
</table>
</form>
<?php
if(count($list)>0)
{
?>
<table border="1" align="center">
	<tr>
		<td>Организация</td>
		<td>Email</td>
		<td>Псевдоним</td>
		<td>Группа</td>
		<td></td>
	</tr>
<?php
foreach($list as $data)
{
?>
	<tr>
		<td><?=$data["Organization"]?></td>
		<td><?=$data["Email"]?></td>
		<td><?=$data["Name"]?></td>
		<td><?=$data["GroupName"]?></td>
		<td><a href="index.php?view=3&Edit=<?=$data["Id"]?>" class="action-edit">Редактировать</a>
			<a href="index.php?view=3&delete_cid=<?=$data["Id"]?>" class="action-delete" data-customer="<? echo trim($data["Organization"]);?>">Удалить</a></td>
	</tr>
<?php
}
?>
</table>
<?php
}else{
?>
<center>В этой группе нет подписчиков</center>
<?php
}
?>
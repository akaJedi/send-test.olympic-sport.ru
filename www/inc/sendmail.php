<?php
ini_set('display_errors',1);



function send_mime_mail($name_from, // имя отправителя
                        $email_from, // email отправителя
                        $name_to, // имя получателя
                        $email_to, // email получателя
                        $data_charset, // кодировка переданных данных
                        $send_charset, // кодировка письма
                        $subject, // тема письма
                        $body // текст письма
                        ) {

  $to = mime_header_encode($name_to, $data_charset, $send_charset)
                 . ' <' . $email_to . '>';
  $subject = mime_header_encode($subject, $data_charset, $send_charset);
  $from =  mime_header_encode($name_from, $data_charset, $send_charset)
                     .' <' . $email_from . '>';
  if($data_charset != $send_charset) {
    $body = iconv($data_charset, $send_charset, $body);
  }
  
  $boundary = "---";
  #$headers  = "MIME-Version: 1.0;\r\n"; 
  $headers .= "From: $from\r\n";
  $headers .= "Content-type: multipart/mixed; boundary=\"$boundary\"\r\n";

  return mail($to, $subject, $body, $headers, '-f'.$email_from);
}

function mime_header_encode($str, $data_charset, $send_charset) {
  if($data_charset != $send_charset) {
    $str = iconv($data_charset, $send_charset, $str);
  }
  return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
}

function xmail($from, $name, $to, $subj, $text, $toName, &$mail_errors = array(), $afiles = array())
{
    global $log;

    $adr = array_map('trim', explode(",", $to));
    $ret = null;
    for ( $i = 0; $i < count($adr); $i++ )
    {
        if( !filter_var($adr[$i], FILTER_VALIDATE_EMAIL) ){
            $mail_errors[] = 'Некорректный e-mail ('.$adr[$i].')';
        }
    }

    if(count($mail_errors)>0) return false;
    
    
    
    $boundary = "---";
    $body = "--$boundary\n";
    /* Присоединяем текстовое сообщение */
    #$body .= "Content-Transfer-Encoding: quoted-printable\n";
    $body .= "Content-type: text/html; charset=utf-8\n\n";    
    $body .= $text."\n";
    
    
    if(!empty($afiles)){
    
      foreach($afiles as $filename)
      {
	  $file = fopen($filename, "r"); //Открываем файл
	  $ftext = fread($file, filesize($filename)); //Считываем весь файл
	  fclose($file); //Закрываем файл
	  $fbasename = basename($filename);	  
	  	  
	  /* Добавляем тип содержимого, кодируем текст файла и добавляем в тело письма */
	  $body .= "--$boundary\n";
	  $body .= "Content-Type: application/octet-stream; name==?utf-8?B?".base64_encode($fbasename)."?=\n"; 
	  $body .= "Content-Transfer-Encoding: base64\n";
	  $body .= "Content-Disposition: attachment; filename==?utf-8?B?".base64_encode($fbasename)."?=\n\n";
	  $body .= chunk_split(base64_encode($ftext))."\n";
      }      
    
    }
    $body .= "--".$boundary ."--\n";
    
    for ( $i = 0; $i < count($adr); $i++ )
    {
        $_toname = is_array($toName) && !empty($toName[$i]) ? $toName[$i] : $adr[$i];
        $ret = send_mime_mail($name, $from, $_toname, $adr[$i], "UTF-8", "UTF-8", $subj, $body);

        if($ret){        			
	      $log->logInfo('сообщение отправлено', $adr[$i]);
        }
        else     
        {
            $mail_errors[] = 'Ошибка не удалось отправить сообщение на: '.$adr[$i];
            $log->logError('сообщение не отправлено!', $adr[$i]);
        }
    }
    
    return $ret;
}




function AddMailToDB($fromEmail,$fromName,$to,$toName,$title,$body, $afiles)
{
	//$body=iconv("UTF-8","WINDOWS-1251",$body);
	//$fromName=iconv("UTF-8","WINDOWS-1251",$fromName);
	//$title=iconv("UTF-8","WINDOWS-1251",$title);

	if(is_array($toName)) $toName= implode(', ',$toName);

	//$toName=iconv("UTF-8","WINDOWS-1251",$toName);
	$db=mysqli_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysqli_select_db($db, CFG_DB_DATABASE);   mysqli_set_charset($db, 'utf8');

	$files ='';
	if(!empty($afiles)){
	  $files = mysqli_escape_string($db, implode("\n", $afiles));
	}
	
	mysqli_query($db, "INSERT INTO `mails` (`FromEmail`,`FromName`,`To`,`ToName`,`Title`,`Body`,`CreationDate`,`files`) VALUES('$fromEmail','$fromName','$to','".mysqli_escape_string($db, $toName)."','$title','".mysqli_escape_string($db, $body)."','".date("Y-m-d H:i:s",time())."', '". $files . "')");  
	mysqli_close($db);
}
function DeleteMailTemplates($id)
{
	//$body=iconv("UTF-8","WINDOWS-1251",$body);
	$db=mysqli_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysqli_select_db($db, CFG_DB_DATABASE);   mysqli_set_charset($db, 'utf8');
	mysqli_query($db, "DELETE FROM `mail_templates` WHERE Id='".$id."'");
	mysqli_close($db);
}
function AddMailTemplates($title,$body,$subscribe,$color,$image)
{
	//$body=iconv("UTF-8","WINDOWS-1251",$body);
	//$subscribe=iconv("UTF-8","WINDOWS-1251",$subscribe);
	//$title=iconv("UTF-8","WINDOWS-1251",$title);
	$db=mysqli_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysqli_select_db($db, CFG_DB_DATABASE);   mysqli_set_charset('utf8');
	if(isset($_POST["templateId"])&&(!empty($_POST["templateId"])))
	{
		mysqli_query($db, "UPDATE `mail_templates` SET `Name`='$title',`Body`='".mysqli_escape_string($db, $body)."',`Subscribe`='".mysqli_escape_string($db, $subscribe)."',`Color`='$color',`Image`='$image' WHERE Id='".$_POST["templateId"]."'");
		$nid=$_POST["templateId"];
	}
	else
	{
		mysqli_query($db, "INSERT INTO `mail_templates` (`Name`,`Body`,`Subscribe`,`Color`,`Image`) VALUES('$title','".mysqli_escape_string($db, $body)."','".mysqli_escape_string($db, $subscribe)."','$color','$image')");
		$nid=mysqli_insert_id($db);
	}
	mysqli_close($db);
	return $nid;
}
function GetTargetList()
{
	//$body=iconv("UTF-8","WINDOWS-1251",$body);
	$db=mysqli_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysqli_select_db($db, CFG_DB_DATABASE);   mysqli_set_charset($db, 'utf8');
	$res=mysqli_query($db, "SELECT * FROM `customer_groups` GROUP BY Name");
	$glist=array();
	while($data=mysqli_fetch_assoc($res))
	{
		$glist[]=$data;
	}
	mysqli_close($db);
	return $glist;
}
function GetTemplates()
{
	//$body=iconv("UTF-8","WINDOWS-1251",$body);
	$db=mysqli_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysqli_select_db($db, CFG_DB_DATABASE);   mysqli_set_charset($db, 'utf8');
	$res=mysqli_query($db, "SELECT * FROM `mail_templates`");
	$tlist=array();
	while($data=mysqli_fetch_assoc($res))
	{
		//$data["Name"]=iconv("WINDOWS-1251","UTF-8",$data["Name"]);
		//$data["Body"]=iconv("WINDOWS-1251","UTF-8",$data["Body"]);
		//$data["Subscribe"]=iconv("WINDOWS-1251","UTF-8",$data["Subscribe"]);
		$tlist[]=$data;
	}
	mysqli_close($db);
	return $tlist;
}
function GetEmailsInGroup($id)
{
	$db=mysqli_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysqli_select_db($db, CFG_DB_DATABASE);   mysqli_set_charset($db, 'utf8');
	$res=mysqli_query($db, "SELECT `customers`.*,customer_groups.Name as GroupName FROM `customers` inner join customer_groups on `customers`.GroupId=customer_groups.Id where GroupId='".$id."'");
	$list=array();
	while($data=mysqli_fetch_assoc($res))
	{
		$list[]=$data;
	}
	mysqli_close($db);
	return $list;
}/*
function ValidateGroupEmail($groupEmails,$groupName,$subject)
{
	$db=mysqli_connect(CFG_DB_HOSTNAME,CFG_DB_USERNAME,CFG_DB_PASSWORD);
	mysqli_select_db($db, CFG_DB_DATABASE);   mysqli_set_charset($db, 'utf8');
	$where="";
	$baseEmails=array();
	$exp=explode(",",$groupEmails);
	foreach($exp as $email)
	{
		if($where!="")$where.=" OR ";
		$where.=" To LIKE `%".trim($email)."%`";
		$baseEmails[]=trim($email);
	}
	$res=mysqli_query($db, "SELECT To FROM `mails` WHERE ($where) AND Title='$subject' LIMIT 1");
	$data=mysqli_fetch_assoc($res);
	$emails=$data["To"];
	$exp=explode(",",$emails);
	foreach($exp as $email)
	{
		$email=trim($email);
		$find=false;
		foreach($baseEmails as $bemail)
		{
			if($bemail==trim($email))
			{
				break;
			}
		}
	}
	mysqli_close($db);
}*/
if(isset($_POST['delete']))
{
	if(isset($_POST["templateId"])&&(!empty($_POST["templateId"])))
	{
		DeleteMailTemplates($_POST["templateId"]);
		$_POST["templateId"]="";
		$_POST["mailTemplate"]=0;
		$_POST["bgimage"]="";
		$_POST["bgcolor"]="";
		$_POST['subscribe']="";
		$_POST['message']="";
		$_POST['subject']="";
	}
}
$bgimage=$_POST["bgimage"];
$bgcolor=$_POST["bgcolor"];
$to=substr(htmlspecialchars(trim($_POST['to'])), 0, 1000);
$toName=substr(htmlspecialchars(trim($_POST['toName'])), 0, 1000);
$from=substr(htmlspecialchars(trim($_POST['from'])), 0, 1000);
$subject=substr(htmlspecialchars(trim($_POST['subject'])), 0, 1000);
$name=substr(htmlspecialchars(trim($_POST['name'])), 0, 1000);
$groupId=$_POST['groupId'];
$targetView=intval($_POST['targetView']);
$subscribe=str_replace("\\'","'",str_replace("\\\"","\"",substr(trim($_POST['subscribe']), 0, 1000000)));
$message=str_replace("\\'","'",str_replace("\\\"","\"",substr(trim($_POST['message']), 0, 1000000)));
$message=str_replace("../","http://www.send-test.olympic-sport.ru/",$message);
$message=str_replace("\"images/upload/","\"http://www.send-test.olympic-sport.ru/images/upload/",$message);
$subscribe=str_replace("../","http://www.olympic-sport.ru/",$subscribe);
$subscribe=str_replace("\"images/upload/","\"http://www.send-test.olympic-sport.ru/images/upload/",$subscribe);

$tpl=file_get_contents(CFG_PATH_ROOT."templates/2.html");
if($_POST['save'])
{
	if(!empty($message))
	{
		$_POST["mailTemplate"]=AddMailTemplates($subject,$message,$subscribe,$bgcolor,$bgimage);
		$sysmessage="<center><h4><font color=green> Шаблон успешно сохранен</font></h4></center>";
	}
	else $sysmessage="<center><h4><font color=red> Произошла ошибка, шаблон не сохранен</font></h4></center>";
}

?>
<script type="text/javascript">
	var editorView=1;
	tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		language:"ru",
		plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave,visualblocks",

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft,visualblocks",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Style formats
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],

		// Replace values for the template plugin
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		}
	});
	function ShowTargetView(view)
	{
		if(view==1)
		{
			document.getElementById("targetTr1").style.display="none";
			document.getElementById("targetTr2").style.display="none";
			document.getElementById("targetTr3").style.display="table-row";
		}
		else
		{
			document.getElementById("targetTr1").style.display="table-row";
			document.getElementById("targetTr2").style.display="table-row";
			document.getElementById("targetTr3").style.display="none";
		}
	}
	function ChangeTemplate()
	{
		document.forms["sendForm"].submit();
	}/*
	function ChangeMessageBg(color)
	{
		var con=tinyMCE.get('message').getContent();
		var ind1=con.indexOf("<div id=\"mcon\" style=\"");
		var ind2=con.substr(ind1+("<div id=\"mcon\" style=\"").length).indexOf("\"");
		var curColor=con.substr(ind1+("<div id=\"mcon\" style=\"").length,ind2);
		tinyMCE.get('message').setContent(con.replace("<div id=\"mcon\" style=\""+curColor+"\"","<div id=\"mcon\" style=\"background-color: "+color+";\"").replace("<div style=\""+curColor+"\"","<div style=\"background-color: "+color+";\""));
	}
	function ChangeMessageBgImage(url)
	{
		var con=tinyMCE.get('message').getContent();
		var ind1=con.indexOf("<div id=\"mcon\" style=\"");
		var ind2=con.substr(ind1+("<div id=\"mcon\" style=\"").length).indexOf("\"");
		var curColor=con.substr(ind1+("<div id=\"mcon\" style=\"").length,ind2);
		tinyMCE.get('message').setContent("<span style='background-image: url("+url+");display:block;' >"+con+"</span>");
	}*/
	function TestShow()
	{
		if(editorView==1)
		{
			var width="auto";
			var height="auto";
			document.getElementById("testDiv").style.display="block";
			document.getElementById("conDiv").style.display="none";
			if(document.getElementById("imgSizeX").value!="")width=document.getElementById("imgSizeX").value+"px";
			if(document.getElementById("imgSizeY").value!="")height=document.getElementById("imgSizeY").value+"px";
			document.getElementById("testshow").value="Редактировать";
			document.getElementById("testDiv").innerHTML="<div style='width:"+width+";height:"+height+";background-repeat:no-repeat;background-color:"+document.getElementById("bgcolor").value+";background-image:url("+document.getElementById("bgimage").value+");'>"+tinyMCE.get('message').getContent()+"</div>";
			editorView=2;
		}
		else
		{
			document.getElementById("testDiv").style.display="none";
			document.getElementById("conDiv").style.display="block";
			document.getElementById("testshow").value="Просмотр";
			editorView=1;
		}
	}
	function ChangeMessageBgImage(url)
	{
		var Im = new Image()
		Im.onload = function(){document.getElementById("imgSizeX").value=this.width;document.getElementById("imgSizeY").value=this.height;}
		Im.src = url;
	}
</script>
<form method="post" name="sendForm" id="fr" enctype="multipart/form-data"  accept-charset="UTF-8">
<table id="tb" align="center">
	<tbody>
	<tr>
		<td class="lr" align="right">
			Тип отправки:
		</td>
		<td >
			<input type="radio" id="targetView1" name="targetView" value="1" <?php if(!isset($_POST["targetView"])||(intval($_POST["targetView"]==1)))echo " checked=\"true\"" ?> onclick="ShowTargetView(2)" /><label for="targetView1">Адрес</label>
			<input type="radio" id="targetView2" name="targetView" value="2" <?php if(intval($_POST["targetView"]==2))echo " checked=\"true\"" ?> onclick="ShowTargetView(1)" /><label for="targetView2">Группа</label>
		</td>
	</tr>
	<tr id="targetTr1" <?php if(intval($_POST["targetView"]==2))echo "style=\"display:none;\"" ?>>
		<td class="lr" align="right">Кому (e-mail) :</td>
		<td ><input name="to" type="text"  value="<?php if(isset($_POST["to"]))echo $_POST["to"]; ?>" class="tf"/></td>
	</tr>
	<tr id="targetTr2" <?php if(intval($_POST["targetView"]==2))echo "style=\"display:none;\"" ?>>
		<td class="lr" align="right">Кому (псевдоним) :</td>
		<td ><input name="toName" type="text"  value="<?php if(isset($_POST["toName"]))echo $_POST["toName"]; ?>" class="tf"/></td>
	</tr>
	<tr id="targetTr3" <?php if(!isset($_POST["targetView"])||intval($_POST["targetView"])==1)echo "style=\"display:none;\"" ?>>
		<td class="lr" align="right">Кому (группа) :</td>
		<td>
			<select name="groupId">
				<?php
				$targetList=GetTargetList();
				foreach($targetList as $target)
				{
				?>
					<option value="<?=$target["Id"]?>" <?php if(isset($_POST["groupId"])&&$_POST["groupId"]==$target["Id"])echo "selected='selected'"; ?>><?=$target["Name"]?></option>
				<?php
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="lr" align="right">От (любой e-mail):</td>
		<td><input name="from" type="text" value="<?php if(isset($_POST["from"]))echo $_POST["from"]; ?>" class="tf"/></td>
	</tr>
	<tr>
		<td class="lr" align="right">Название организации:</td>
		<td><input name="name" type="text" value="<?php if(isset($_POST["name"]))echo $_POST["name"]; ?>" class="tf"/></td>
	</tr>
	<tr>
		<td class="lr" align="right">Шаблон:</td>
		<td>
			<select name="mailTemplate" onchange="ChangeTemplate()">
				<option value="0" <?php if(!isset($_POST["mailTemplate"])||(isset($_POST["mailTemplate"])&&$_POST["mailTemplate"]==0))echo "selected='selected'"; ?>>Базовый</option>
				<?php
					$tlist=GetTemplates();
					foreach($tlist as $dt)
					{
						if(isset($_POST["mailTemplate"])&&$_POST["mailTemplate"]==$dt["Id"]&&(!isset($_POST['btn'])))
						{
							$_POST["message"]=$dt["Body"];
							$_POST["subscribe"]=$dt["Subscribe"];
							$_POST["subject"]=$dt["Name"];
							$_POST["bgimage"]=$dt["Image"];
							$_POST["bgcolor"]=$dt["Color"];
							$bgimage=$_POST["bgimage"];
							$bgcolor=$_POST["bgcolor"];
							$templateId=$dt["Id"];
						}
						?>
						<option value="<?=$dt["Id"]?>" <?php if(isset($_POST["mailTemplate"])&&$_POST["mailTemplate"]==$dt["Id"])echo "selected='selected'"; ?>><?=$dt["Name"]?></option>
						<?php
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="lr" align="right">Тема :</td>
		<td><input name="subject" type="text" class="tf" value="<?php if(isset($_POST["subject"]))echo $_POST["subject"]; ?>" /></td>
	</tr>
	<tr>
		<td colspan="2" class="lr">Сообщение :
			<br />
			<table>
				<tr>
					<td>Фон письма:</td>
					<td>Цвет
						<select name="bgcolor" id="bgcolor">
							<option value="">Отсутвует</option>
							<option value="red" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="red")echo "selected='selected'"; ?>>Красный</option>
							<option value="magenta" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="magenta")echo "selected='selected'"; ?>>Фиолетовый</option>
							<option value="black" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="black")echo "selected='selected'"; ?>>Черный</option>
							<option value="blue" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="blue")echo "selected='selected'"; ?>>Синий</option>
							<option value="green" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="green")echo "selected='selected'"; ?>>Зеленый</option>
							<option value="yellow" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="yellow")echo "selected='selected'"; ?>>Желтый</option>
							<option value="gold" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="gold")echo "selected='selected'"; ?>>Золотой</option>
							<option value="purple" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="purple")echo "selected='selected'"; ?>>Пурпурный</option>
							<option value="lightgreen" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="lightgreen")echo "selected='selected'"; ?>>Светло-зеленый</option>
							<option value="grey" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="grey")echo "selected='selected'"; ?>>Серый</option>
							<option value="silver" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="silver")echo "selected='selected'"; ?>>Серебряный</option>
							<option value="orangered" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="orangered")echo "selected='selected'"; ?>>Оранжевый</option>
							<option value="indianred" <?php if(isset($_POST["bgcolor"])&&$_POST["bgcolor"]=="indianred")echo "selected='selected'"; ?>>Розовый</option>
						</select>
					</td>
					<td>Картинка (url)
						<input type="text" id="bgimage" name="bgimage" value="<?php if(isset($_POST["bgimage"]))echo $_POST["bgimage"]; ?>" onchange="ChangeMessageBgImage(this.value)"/>
						<input type="button" id="testshow" value="Просмотр" name="testshow" onclick="TestShow()" />
					</td>
				</tr>
			</table>
			<div id="conDiv">
			<textarea cols="30" rows="20" name="message" id="message" class="tf"><div id="mcon" style="background-color: transparent;"><?php if(isset($_POST["message"]))echo str_replace("\\\'","\'",str_replace("\\\"","\"",$_POST["message"])); ?></div></textarea>
			</div>
			<div id="testDiv" style="display:none;">
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center" class="lr">Подпись :
			<br />
			<textarea cols="15" rows="10" name="subscribe" id="ttf2" class="tf"><?php if(isset($_POST["subscribe"]))echo str_replace("\\\'","\'",str_replace("\\\"","\"",$_POST["subscribe"])); ?></textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<?php
			if(isset($templateId))
			{
			?>
			<input type="submit" value="Сохранить шаблон"  name="save" id="save"/>
			<input type="submit" value="Удалить шаблон"  name="delete" id="delete"/>
			<input type="submit" value="Добавить как новый шаблон" onclick="document.getElementById('templateId').value='';" name="save" id="save"/>
			<?php
			}else{
			?>
			<input type="submit" value="Добавить как новый шаблон"  name="save" id="save"/>
			<?php
			}
			?>
			
			
			
			
<style type="text/css">
#File-Attachment { 
text-align: left; margin: 50px 0;
float: left;
width: 468px;
}
#File-Attachment div {
overflow-y: scroll;
height: 500px;
background: whitesmoke;
padding: 11px;
font-size: 12px;
line-height: 27px;
}
</style>			
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


<div id="File-Attachment">
<b>Прикрепить файлы:</b><br /><br />
<div style="overflow-y: scroll; height:500px;">
	<?php
	foreach($files as $file)
	{
	      $f = htmlspecialchars($file);
	      $fz = FileSizeConvert(filesize($fpath."/images/upload/".$file));
	      echo sprintf("<label><input type='checkbox' name='afiles[]' value='%s' />%s (%s)</label><br />", $f, $f, $fz);
	}
	?>
</div>
<small>Максимальный суммарный размер всех прикрепленных файлов<br /><b>не должен превышать 32мб.</b></small>
</div>			
			
			<br /><br /><br /><br />
			
			<input type="submit" value="Отправить письмо"  name="btn" id="btn"/>
		</td>
	</tr>
	</tbody>
</table>
<?php if(isset($templateId))echo "<input type=\"hidden\" value=\"".$templateId."\"  name=\"templateId\" id=\"templateId\"/>"; ?>
<input type="hidden" value=""  name="imgSizeX" id="imgSizeX"/>
<input type="hidden" value=""  name="imgSizeY" id="imgSizeY"/>
</form>
<center><p></p></center>
<?php
if($_POST['btn'])
{
	if(!empty($tpl)&&((!empty($to)&&!empty($toName)&&$targetView==1)||(($targetView==2)&&!empty($groupId)))&&!empty($from)&&!empty($subject)&&!empty($name))
	{
		if($targetView==2)
		{
			$nto="";
			$glist=GetEmailsInGroup($groupId);
			$toName=array();
			foreach($glist as $customer)
			{
				$GroupName = $customer["GroupName"];
				//$toName=$customer["GroupName"];
				$toName[] = $customer["Name"].(!empty($customer['Organization']) ? ' | '.$customer["Organization"] : '');

				if($nto!="")$nto.=", ";
				$nto.=$customer["Email"];
			}
			//ValidateGroupEmail();
			$to=$nto;
		}
		if($bgcolor=="")$bgcolor="transparent";
		$bgimage=str_replace(" ","%20",$bgimage);
		if(isset($_POST["imgSizeX"]))$imgSizeX=$_POST["imgSizeX"];
		if(isset($_POST["imgSizeY"]))$imgSizeY=$_POST["imgSizeY"];
		$message=str_replace("{content}",$message,$tpl);
		$message=str_replace("{subscribe}",$subscribe,$message);
		$message=str_replace("{image}",$bgimage,$message);
		$message=str_replace("{bgcolor}",$bgcolor,$message);
		$message=str_replace("{imgSizeX}",$imgSizeX,$message);
		$message=str_replace("{imgSizeY}",$imgSizeY,$message);
		$message.="<img src='".CFG_HTTP_ROOT."images/1x1.png' />";
		
		
		$log->logInfo('Отсылка почты...');
		$log->logInfo('Тема', $subject);			
		$log->logDebug($to);
		
		
		$afiles = array(); $db_files = array();
		$afpath = $fpath."/images/upload/";
		if(!empty($_POST['afiles'])) {
		  foreach($_POST['afiles'] as $afile) {	      
		      
		      if(file_exists($afpath.$afile)){
			$afiles[] = $afpath.$afile;
			$db_files[] = $afile;
			$log->logInfo('прикреплен файл', $afpath.$afile);
		      }else{
			$log->logError('прикрепленый файл не существует!', $afpath.$afile);
			$log->logInfo('выход...');
			throw new Exception($afpath.$afile.' файл не существует!');
		      }
		      
		  }
		}
		
		
		$mail_errors = array();
		
		
		$t_start = microtime(true);

		$ret=xmail($from,$name,$to,$subject,$message,$toName, $mail_errors, $afiles);
		
		$wt = '('.round( microtime( true ) - $t_start, 4 ).' сек.)';
		$log->logInfo($wt);
		
		//AddMailToDB($from,$name,$to,$toName,$subject,$message);
		if($targetView==2){
			AddMailToDB($from,$name,$GroupName,$GroupName,$subject,$message, $db_files);
		}else{
			AddMailToDB($from,$name,$to,$toName,$subject,$message, $db_files);
		}

		if($ret==true)
		{
			echo "<center><h4><font color=green> Сообщение успешно отправлен адресату</font></h4></center>";
		}
		else
		{
			echo "<center><h4><font color=red> Произошла ошибка, сообщение не отправлено :(</font></h4><br />";
   foreach($mail_errors as $merror){
       echo $merror.'<br />';
   }
   echo "</center><br /><br /><br />";

		}
	}
	else
	{
		echo "<center><h4><font color=red> Произошла ошибка, сообщение не отправлено :(</font></h4></center>";
	}
}
if(isset($sysmessage))
{
	echo "<center><h4>".$sysmessage."</h4></center>";
}
?>
<?php
if(isset($_POST["bgimage"]))
{
?>
<script type="text/javascript">
ChangeMessageBgImage(document.getElementById("bgimage").value);
</script>
<?php
}
?>
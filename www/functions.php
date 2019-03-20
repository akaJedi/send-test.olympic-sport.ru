<?php

function uploadFile($allowedExts = array("gif", "jpeg", "jpg", "png"))
{
    $extension = end(explode(".", $_FILES["file"]["name"]));
    if ( in_array($extension, $allowedExts) ) {
        if ( $_FILES["file"]["error"] > 0 ) {
            return array(false, 'Ошибка при загрузке файла ' . $_FILES["file"]["error"]);
        } else {
            move_uploaded_file($_FILES["file"]["tmp_name"], CFG_PATH_ROOT . "upload/" . $_FILES["file"]["name"]);
            return array(true, CFG_PATH_ROOT . "upload/" . $_FILES["file"]["name"]);
        }
    } else {
        return array(false, 'Неверный формат файла (' . $extension . '), разрешена загрузка только: ' . implode(', ', $allowedExts));
    }
}

// Remove any un-safe values to prevent email injection
function smcf_filter($value)
{
    $pattern = array("/\n/", "/\r/", "/content-type:/i", "/to:/i", "/from:/i", "/cc:/i", "/\"/", "/\'/");
    $value = preg_replace($pattern, "", $value);
    return $value;
}

function validateCustomer($Name, $Organization, $Email)
{
    $error = false;
    if ( strlen($Name) <= 3 || strlen($Name) > 200 )
        $error = 'Псевдоним должен быть от 3 до 200 символов!';
    elseif ( empty($Organization) )
        $error = 'Ввведите Организацию!';
    else{
        $emails = array_map('trim', explode(',', $Email));
        $email_errors = array();
        foreach($emails as $_email)
            if ( !filter_var($_email, FILTER_VALIDATE_EMAIL) )
                $email_errors[] = $_email;
        if(count($email_errors)>0)
            $error = "E-mail адрес введён неверно! (".implode(') (', $email_errors).")";
    }

    return $error;
}

function FileSizeConvert($bytes)
{
    $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

    foreach($arBytes as $arItem)
    {
        if($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
            break;
        }
    }
    return $result;
}
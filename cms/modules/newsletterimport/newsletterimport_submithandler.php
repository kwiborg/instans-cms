<?php

if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_NEWSLETTERSEND", true);

if ($_GET[df]){
    if (file_exists($_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/".$_GET[df].".csv")){
        unlink($_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/".$_GET[df].".csv");
    }
}

/// CLEAN UP
if ($handle = opendir($_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/")) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            $filetime = filemtime($_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/".$file);
            if (time()-(int)$filetime > (8*60*60)){
                if (file_exists($_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/".$file)){
                    unlink($_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/".$file);
                }
            }
        }
    }
    closedir($handle);
}
///

if ($_FILES){
    $temp_filename = $_FILES["importfile"]["tmp_name"];
    $original_filename = $_FILES["importfile"]["name"];
    $f = time().str_makerand(4,4);
    $new_filename = $f.".csv";
    $dest_folder = $_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/";
    $csvfile = $dest_folder.$new_filename;
    if (!move_uploaded_file($temp_filename, $dest_folder.$new_filename)){
        $usermessage_error = "Fejl i overførsel af fil!";
        header("Location: index.php?content_identifier=newsletterimport&usermessage_error=$usermessage_error");
        exit;
    } else {
        $temp = file_get_contents($csvfile);
        $arr_file = explode("\r\n", $temp);
        $arr_file = file($csvfile);
        foreach($arr_file as $k => $line){
            $arr_file[$k] = str_replace("\r\n", "", $line);
        }
        $arr_column_names = explode(";", $arr_file[0]);
        $arr_okay = array("cfield___FIRSTNAME", "cfield___LASTNAME", "cfield___EMAIL", "cfield___COMPANY", "cfield___ADDRESS", "cfield___ZIPCODE", "cfield___CITY", "cfield___PHONE", "cfield___CELLPHONE", "cfield___JOB_TITLE");
        if ($arr_column_names != $arr_okay){
            $diff = array_diff($arr_okay, $arr_column_names);
            $usermessage_error = "Forkert filformat. Følgende kolonner mangler eller er i forkert rækkefølge: ".implode(", ", $diff);
            header("Location: index.php?content_identifier=newsletterimport&df=$f&usermessage_error=$usermessage_error");
            exit;
        } else {
            header("Location: index.php?content_identifier=newsletterimport&step=2&f=$f");
            exit;
        }
    }
}

switch ($dothis) {
case "run_import":
    $importmode = $_POST["importmode"];
    $import_newslettertemplate_id = $_POST["imptemplate"];
    if ($importmode > 1){
        if (trim($_POST["import_newgroupname"]) == ""){
            if ($_POST["impgroup"] > 0){
                $import_landinggroup_id = $_POST["impgroup"];
            } else {
                echo "Error, no group ID";
                exit;
            }
        } else {
            $name = trim($_POST["import_newgroupname"]);
            $name = str_replace("'", "", $name);
            $name = str_replace("\"", "", $name);
            $name = stripslashes($name);
            $sql = "insert into GROUPS (GROUP_NAME, DESCRIPTION, CREATED_DATE, CHANGED_DATE, AUTHOR_ID, SITE_ID) values ('$name', 'Importeret d. ".date("d-m-Y")."', '".time()."', '".time()."', '".$_SESSION[CMS_USER][USER_ID]."', '$_SESSION[SELECTED_SITE]')";
            mysql_query($sql);
            $import_landinggroup_id = mysql_insert_id();
        }
    }
    $csvfile = $_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/$_GET[f].csv";
    $temp = file_get_contents($csvfile);
    $arr_file = explode("\r\n", $temp);
    $arr_file = file($csvfile);
    $file_seems_utf8 = seems_utf8(implode("", $arr_file));
    foreach($arr_file as $k => $line){
        $arr_file[$k] = str_replace("\r\n", "", $line);
    }
    $arr_column_names = explode(";", $arr_file[0]);
    $arr_okay = array("cfield___FIRSTNAME", "cfield___LASTNAME", "cfield___EMAIL", "cfield___COMPANY", "cfield___ADDRESS", "cfield___ZIPCODE", "cfield___CITY", "cfield___PHONE", "cfield___CELLPHONE", "cfield___JOB_TITLE");
    unset($arr_file[0]);
    array_merge($arr_file, array());
    foreach($arr_file as $k => $line){
        $arr_values = explode(";", $line);
        foreach ($arr_values as $tempkey => $value){
            if ($arr_okay[$tempkey] == "cfield___FIRSTNAME"){
               $nametemp = explode(" ", $value);
               if (count($nametemp) > 1 && trim($arr_values[1]) == ""){
                   $ttlastname = $nametemp[count($nametemp)-1];
                   unset($nametemp[count($nametemp)-1]);
                   $ttfirstname = implode(" ", $nametemp);
                   // $arr_temp[cfield___FIRSTNAME] = $ttfirstname;
                   // $arr_temp[cfield___LASTNAME] = $ttlastname;
                    $arr_temp[cfield___FIRSTNAME] = utf8_encode($ttfirstname);
                    $arr_temp[cfield___LASTNAME] = utf8_encode($ttlastname);
               }
            }
            if ($utf8_site && !$file_seems_utf8){
                if (!$arr_temp[$arr_okay[$tempkey]]) $arr_temp[$arr_okay[$tempkey]] = utf8_encode($value);
            } else {
                if (!$arr_temp[$arr_okay[$tempkey]]) $arr_temp[$arr_okay[$tempkey]] = $value;
            }
        }
        $arr_POSTVARS[] = $arr_temp;
        unset($arr_temp);
    }
    /*echo "<pre>";
    print_r($arr_POSTVARS);
    echo "</pre>";
    */

    $newsletter_templateid = $import_newslettertemplate_id;
    $newsletter_defaultgroupid = returnFieldValue("GENERAL_SETTINGS", "NEWSLETTER_GROUPID", "ID", $_SESSION[SELECTED_SITE]);
    $landing_group_id = $import_landinggroup_id;

    foreach ($arr_POSTVARS as $count => $_POST){
        $_POST["t_id"] = $newsletter_templateid;
        $_POST["subscriber_email"] = $_POST["cfield___EMAIL"];
        $subscriber_email = $_POST["subscriber_email"];
        $template_id = $_POST["t_id"];
        if (trim($subscriber_email) != "" && valid_email($subscriber_email)){
            $user_id = newsletter_is_user(trim($subscriber_email));
            if (!$user_id){
                unset($_POST["cfield___EMAIL"]);
                $user_id = newsletter_insert_user($subscriber_email, $_POST);
                $new_user = true;
            }            
            $status = newsletter_subscription_engine($user_id, $template_id, "QUIET");
            if ($status == "OKAY_subscribed" || $status == "OKAY_resubscribed"){
                $validate_key = md5($subscriber_email.$user_id.$template_id."1nstansNewsletter098");
                newsletter_verify_subscription($user_id, $template_id, $validate_key, "QUIET");
            }
            $okay_messages .= "- Importeret: $_POST[cfield___FIRSTNAME] $_POST[cfield___LASTNAME] (e-mail: '$subscriber_email')<br/>";

            /// GRUPPER 
            if ($importmode == 1){
                /// $importmode == 1 --> DO NOTHING SPECIAL
                $sql = "update USERS set DELETED='0' where ID='$user_id' and DELETED='1'";
                mysql_query($sql);
            } else if ($importmode == 2){
                $sql = "delete from NEWSLETTER_SUBSCRIPTIONS where USER_ID='$user_id' and TEMPLATE_ID='$newsletter_templateid'";
                mysql_query($sql);
                $sql = "delete from USERS_GROUPS where USER_ID='$user_id' and GROUP_ID='$newsletter_defaultgroupid'";
                mysql_query($sql);
                $sql = "insert into USERS_GROUPS (USER_ID, GROUP_ID) values ('$user_id', '$landing_group_id')";
                mysql_query($sql);
                $sql = "update USERS set DELETED='0' where ID='$user_id' and DELETED='1'";
                mysql_query($sql);
            } else if ($importmode == 3){
                $sql = "insert into USERS_GROUPS (USER_ID, GROUP_ID) values ('$user_id', '$landing_group_id')";
                mysql_query($sql);
                $sql = "update USERS set DELETED='0' where ID='$user_id' and DELETED='1'";
                mysql_query($sql);
            }
        } else {
            $error_messages .= "- Fejl: Ugyldig eller manglende e-mail for $_POST[cfield___FIRSTNAME] $_POST[cfield___LASTNAME] (e-mail: '$subscriber_email')<br/>";
        }
    }
    $done = true;
    $fhandle = fopen($_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/res_".$_GET[f].".csv", "w");
    fwrite($fhandle, $okay_messages."|||SPLIT|||".$error_messages);
    fclose($fhandle);
    if (file_exists($_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/".$_GET[f].".csv")){
        unlink($_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/".$_GET[f].".csv");
    }
    header("Location: index.php?content_identifier=newsletterimport&step=3&f=$_GET[f]&m=$importmode&gid=$landing_group_id&tid=$newsletter_templateid");
    exit;
}
?>
<?php
    include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
    include_once($cmsAbsoluteServerPath."/common.inc.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/cms/modules/newsletter/frontend/newsletter_common.inc.php");
    
    connect_to_db();
    
    $csvfile = $_SERVER["DOCUMENT_ROOT"]."/cms/modules/newsletter/import/import.csv";    
    $temp = file_get_contents($csvfile);
    $arr_file = explode("\r\n", $temp);
    $arr_file = file($csvfile);
    echo "<pre>";
    print_r($arr_file);
    echo "</pre>";    
    
    foreach($arr_file as $k => $line){
        $arr_file[$k] = str_replace("\r\n", "", $line);
    }

    $arr_column_names = explode(";", $arr_file[0]);
    $arr_okay = array("cfield___FIRSTNAME", "cfield___LASTNAME", "cfield___EMAIL", "cfield___COMPANY", "cfield___ADDRESS", "cfield___ZIPCODE", "cfield___CITY", "cfield___PHONE", "cfield___CELLPHONE", "cfield___JOB_TITLE");
    
    if ($arr_column_names != $arr_okay){
        echo "Wrong file format. Diff:<br><br>";
        print_r(array_diff($arr_okay, $arr_column_names));
        exit;
    } else {
        echo "File format okay.<br><br>";
        unset($arr_file[0]);
        array_merge($arr_file, array());
        foreach($arr_file as $k => $line){
            $arr_values = explode(";", $line);
            foreach ($arr_values as $tempkey => $value){
                /*if ($arr_okay[$tempkey] == "cfield___EMAIL"){
                    $value = "instanstest_".$value.".lol";
                }*/
                if ($arr_okay[$tempkey] == "cfield___FIRSTNAME"){
                   $nametemp = explode(" ", $value);
                   if (count($nametemp) > 1){
                       $ttlastname = $nametemp[count($nametemp)-1];
                       unset($nametemp[count($nametemp)-1]);
                       $ttfirstname = implode(" ", $nametemp);
                       // $arr_temp[cfield___FIRSTNAME] = utf8_encode($ttfirstname);
                       // $arr_temp[cfield___LASTNAME] = utf8_encode($ttlastname);
                       $arr_temp[cfield___FIRSTNAME] = $ttfirstname;
                       $arr_temp[cfield___LASTNAME] = $ttlastname;
                   }
                }
                if (!$arr_temp[$arr_okay[$tempkey]]) $arr_temp[$arr_okay[$tempkey]] = $value;
            }
            $arr_POSTVARS[] = $arr_temp;
            unset($arr_temp);
        }
    }
    
    echo "<pre>";
    print_r($arr_POSTVARS);
    echo "</pre>";
    
    $newsletter_templateid = 29;
    $newsletter_defaultgroupid = 13;
    $landing_group_id = 28;

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
            echo "- STATUS=$status, EMAIL=$subscriber_email, USER_ID=$user_id, TEMPLATE_ID=$template_id<br>";

            /// GRUPPER 
            $sql = "delete from NEWSLETTER_SUBSCRIPTIONS where USER_ID='$user_id' and TEMPLATE_ID='$newsletter_templateid'";
            mysql_query($sql);
            $sql = "delete from USERS_GROUPS where USER_ID='$user_id' and GROUP_ID='$newsletter_defaultgroupid'";
            mysql_query($sql);
            $sql = "insert into USERS_GROUPS (USER_ID, GROUP_ID) values ('$user_id', '$landing_group_id')";
            mysql_query($sql);
        } else {
            echo "- Not e-mail: $subscriber_email";
        }
    }
    
?>
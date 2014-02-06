<?php

include_once("common.inc.php");

unset($_SESSION["CMS_USER"]);
unset($_SESSION["SELECTED_SITE"]);

if ($_POST[dothis] == "logind" AND strlen($_POST[pass]) > 0 AND strlen($_POST[username]) > 0) {

    unset($e);

    $sql = "
        select
            ID, USERNAME, FIRSTNAME, LASTNAME, PASSWORD, PASSWORD_ENCRYPTED
        from
            USERS
        where
            USERNAME = '".mysql_real_escape_string($_POST[username])."'
            and (PASSWORD != '' AND PASSWORD IS NOT NULL)
            and DELETED = '0'
        limit 1";
    $result = mysql_query($sql);
    $row = mysql_fetch_array($result);

    if ($result === false || mysql_num_rows($result) == 0) {
        $e = 1;
    } else if (mysql_num_rows($result) == 0) {
        $e = 1;
    } else {

        if ($_POST[pass] == $row[PASSWORD] OR md5($_POST[pass]) == $row[PASSWORD_ENCRYPTED]) {

            $permissions = returnDistinctUserPermissions($row[ID]);
            if (is_array($permissions) === true) {

                if (in_array("CMS_LOGIN", $permissions) === true) {
                    setLoginValues($row[ID], $row[USERNAME], $row[FIRSTNAME], $row[LASTNAME], array());
                    header("location: site_selector.php"); /// site selector
                    exit;
                }

            }

        }

        $e = 1;

    }

}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Log ind</title>
    <link rel="stylesheet" href="cms.css"/>
    <script src="/cms/scripts/prototype.js" type="text/javascript"></script>
    <script src="/cms/scripts/scriptaculous/scriptaculous.js" type="text/javascript"></script>
    <script type="text/javascript" src="commonscripts.js"></script>
    <script type="text/javascript">
        function KeyDownHandler(e,btn)	{
            // process only the Enter key
            if(e && e.which){
                characterCode = e.which
            } else {
                characterCode = e.keyCode;
            }
            if (characterCode == 13)	{
                // cancel the default submit
                e.returnValue=false;
                e.cancel = true;
                // submit the form by programmatically clicking the specified button
                btn.click();
            }
        }

    </script>
</head>
<body onload="document.forms[0].username.focus()">
<form method="post" onkeypress="KeyDownHandler(event,this.submitbutton)" action="">
    <input type="hidden" name="dothis" value=""/>
    <div id="wrapitall">
        <div style="width:300px; margin:0px auto">
            <div class="feltblok_header">Log ind</div>
            <div class="feltblok_wrapper">
                <h2>Brugernavn:</h2>
                <input type="text" name="username" class="inputfelt"/>
                <h2>Kodeord:</h2>
                <input type="password" name="pass" class="inputfelt"/>
                <br/><br/>
                <input id="submitbutton" type="button" value="Log ind" onclick="verifyLogin()" class="lilletekst_knap"/>
            </div>
        </div>
    </div>
</form>
<?php
    if ($e) {
        echo "<script type='text/javascript'>alert('Det brugernavn og/eller kodeord, som du har indtastet, er ikke gyldigt. Prøv evt. igen.')</script>";
    }
?>
</body>
</html>
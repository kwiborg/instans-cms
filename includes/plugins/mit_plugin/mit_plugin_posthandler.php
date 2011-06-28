<?php
    if ($_POST){
        // print_r($_POST);
        $sql = "insert into MIN_TABEL (fornavn, efternavn) values ('$_POST[fornavn]', '$_POST[efternavn]')";
        mysql_query($sql);
        header("location: http://www.dr.dk/");
        exit;
    }
?>
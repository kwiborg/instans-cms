<table border="1">
<tr>
<?php 
    $sql = "select HEADING from PAGES where DELETED='0'";
    $res = mysql_query($sql);
    while ($row = mysql_fetch_assoc($res)){
        echo "<td>".$row[HEADING]."</td>";
    }
?>
</tr></table>

<form method="post" action="">
    Dit fornavn: <input type="text" name="fornavn" /><br/>
    Dit efternavn: <input type="text" name="efternavn" /><br/>
    <input type="submit" value="Gem!" />
</form>
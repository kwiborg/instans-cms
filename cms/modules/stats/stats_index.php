<?php
	if (!$_SESSION["CMS_USER"]){
 		header("location: ../../login.php");	
	}
?>
<h1>Statistik</h1>
<div class="feltblok_header">Se statistik for dette website</div>
<div class="feltblok_wrapper">
	<p><a href='<?=$stat_url;?>' target='_blank'>Klik her for at åbne statistiksystemet i et nyt vindue</a></p>
</div>
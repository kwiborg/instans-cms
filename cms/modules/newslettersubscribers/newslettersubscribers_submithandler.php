<?php
	switch($_GET[dothis]){
		case "unsubscribe":
			$sql = "
				update 
					NEWSLETTER_SUBSCRIPTIONS 
				set 
					SUBSCRIBED='0', CONFIRMED='0', CHANGED_DATE='".time()."'
				where 
					USER_ID='$_GET[user_id]' and TEMPLATE_ID='$_GET[template_id]'
			";
			mysql_query($sql);
			header("location: index.php?content_identifier=newslettersubscribers&dothis=show_subscriptions&user_id=".$_GET[user_id]);
			exit;
		break;
	}
?>
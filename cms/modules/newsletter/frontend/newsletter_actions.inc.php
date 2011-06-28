<?php
	require_once($_SERVER["DOCUMENT_ROOT"]."/cms/modules/newsletter/frontend/newsletter_common.inc.php");
	switch ($_POST["action"]){
		/// SUBSCRIBE  ///////////////////////////////////////////////		
		case "subscribe":
			if (!$_POST["subscriber_email"]){
				exit;
			} else {
				$subscriber_email	= $_POST["subscriber_email"];
				// $subscriber_fn		= $_POST["subscriber_firstname"];
				// $subscriber_ln		= $_POST["subscriber_lastname"];
				$template_id		= $_POST["t_id"];
				/// Opret USER, hvis den ikke eksisterer
				$user_id = newsletter_is_user($subscriber_email);
				if (!$user_id){
					$user_id = newsletter_insert_user($subscriber_email, $_POST);
					$new_user = true;
				}
				$status = newsletter_subscription_engine($user_id, $template_id);
				if ($status == "OKAY_subscribed" || $status == "OKAY_resubscribed"){
					// if (!newsletter_is_registered_user($user_id) && !$new_user){
						// newsletter_update_name($user_id, $subscriber_fn, $subscriber_ln);
					// }
					if (require_validation($template_id)){
						$status .= "confirm";
						newsletter_send_subscribe_verification_mail($user_id, $template_id);
					} else {
						$validate_key = md5($subscriber_email.$user_id.$template_id."1nstansNewsletter098");
						newsletter_verify_subscription($user_id, $template_id, $validate_key);
					}
					newsletter_usermessage("usermessage", $status, $user_id, $template_id);
				} else {
					newsletter_usermessage("usermessage", $status, $user_id, $template_id);
				}
			}
		break;
		/// UNSUBSCRIBE ///////////////////////////////////////////////		
		case "unsubscribe":
			if (!$_POST["subscriber_email"]){
				exit;
			} else {
				$subscriber_email	= $_POST["subscriber_email"];
				$template_id		= $_POST["t_id"];
				$user_id = newsletter_is_user($subscriber_email);
				$status = newsletter_unsubscription_engine($user_id, $template_id);
				if ($status == "OKAY_unsubscribed" || $status == "OKAY_optout"){
					newsletter_send_unsubscribe_verification_mail($user_id, $template_id);
					newsletter_usermessage("usermessage", $status, $user_id, $template_id);
				} else {
					newsletter_usermessage("usermessage", $status, $user_id, $template_id);
				}
			}
		break;
	}
	switch ($_GET["action"]){
		case "verify_subscription":
			if ($_GET["uid"] && $_GET["tid"] && $_GET["verify"]){
				newsletter_verify_subscription($_GET["uid"], $_GET["tid"], $_GET["verify"]);
			} else {
				newsletter_usermessage("subscribe", "ERROR_subscriptionnotconfirmed");
			}
		break;
		case "cancel_unsubscription":
			if ($_GET["uid"] && $_GET["tid"] && $_GET["verify"]){
				newsletter_cancel_unsubscription($_GET["uid"], $_GET["tid"], $_GET["verify"]);
			} else {
				newsletter_usermessage("subscribe", "ERROR_unsubscribenotcancelled");
			}
		break;
		/// UPDATE CATEGORIES FROM FORM WITHIN E-MAIL ///////////////////////////////////////////////		
		case "update_categories_from_email":
			if (count($_POST) > 0) {
				if ($_GET["uid"] && $_GET["c"] == md5($_GET["uid"]."1nstansFlyvemaskine")){
					update_categories_from_email($_GET["uid"], $_GET["newsletterid"], $_POST);
					newsletter_usermessage("subscribe", "OKAY_categoriesupdated", "", $_GET["newsletterid"]);
				}
			} else {
				$warning = cmsTranslate("NewsletterErrorEmailclient");
				$_GET["action"] = "updateinterestgroups";
			}
			break;
		case "approveproof":
				if ($_GET["uid"] && $_GET["c"] == md5($_GET["uid"]."1nstansFlyvemaskine")){
					newsletter_approve($_GET[newsletterid], $_GET["uid"]);
					// Get template-id for passing to fn:usermessage
					$sql = "select TEMPLATE_ID from NEWSLETTERS N where N.ID = '$_GET[newsletterid]'";
					$res = mysql_query($sql);
					$template_id = mysql_result($res,0);
					newsletter_usermessage("subscribe", "OKAY_proofapproved", $_GET["uid"], $template_id);
				}
			exit;
			break;
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////
	/// FUNKTIONER //////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////

?>
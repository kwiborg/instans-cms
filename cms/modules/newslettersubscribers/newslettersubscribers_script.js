 	function newsletter_unsubscribe(user_id, template_id, email, template_name){
		if (confirm("Vil du framelde " + email + " fra nyhedsbrevet \"" + template_name + "\"?")){
			location = "index.php?content_identifier=newslettersubscribers&dothis=unsubscribe&user_id="+user_id+"&template_id="+template_id;
		}
	}
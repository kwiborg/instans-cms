function verify_comment(){
	name 	= $("blogpost_commentfield_name").value;
	email 	= $("blogpost_commentfield_email").value;
	url 	= $("blogpost_commentfield_url").value;
	comment = $("blogpost_commentfield_comment").value;
	err = "";
	if (name == ""){
		err += "(*) Udfyld venligst feltet Navn\n";
	}
	if (email == ""){
		err += "(*) Udfyld venligst feltet E-mail\n";
	}
	if (email != "" && !isMail(email)){
		err += "(*) Indtast venligst en gyldig e-mail-adresse\n";
	}
	if (comment == ""){
		err += "(*) Udfyld venligst feltet Kommentar\n";
	}
	if ($("blogpost_commentfield_captcha") && $("blogpost_commentfield_captcha").value == ""){
		err += "(*) Udfyld venligst feltet Spam-forebyggelse med koden fra billedet.\n";
	}
	if (err != ""){
		alert(err);
		return false;
	} 
}
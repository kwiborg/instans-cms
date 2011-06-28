/*
	Comment management functions
*/

function comment_approve(comment_id) {
	Element.show('ajaxloader_comments');
	var url = '/cms/modules/blogs/blogs_ajaxresponders.php';
	var pars = 'do=ajax_comment_approve&comment_id='+comment_id
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: comment_approved,
			onFailure: function(){alert("Der opstod en AJAX-relateret fejl. Prøv venligst igen senere.")}
		});
	return false;
}

function comment_approved(originalRequest){
	var arr_status = originalRequest.responseText.split("|||");
	if (arr_status[0]=="SUCCESS") {
		$('comment_'+arr_status[2]).removeClassName('notapprovedcomment');
		$('commenttools_'+arr_status[2]).removeClassName('notapprovedcomment');
	} else {
		alert(arr_status[1]);
	}
	Element.hide('ajaxloader_comments');
}

function comment_reject(comment_id) {
	Element.show('ajaxloader_comments');
	var url = '/cms/modules/blogs/blogs_ajaxresponders.php';
	var pars = 'do=ajax_comment_reject&comment_id='+comment_id
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: comment_rejected,
			onFailure: function(){alert("Der opstod en AJAX-relateret fejl. Prøv venligst igen senere.")}
		});
	return false;
}

function comment_rejected(originalRequest){
	var arr_status = originalRequest.responseText.split("|||");
	if (arr_status[0]=="SUCCESS") {
		$('comment_'+arr_status[2]).addClassName('notapprovedcomment');
		$('commenttools_'+arr_status[2]).addClassName('notapprovedcomment');
	} else {
		alert(arr_status[1]);
	}
	Element.hide('ajaxloader_comments');
}

function comment_approve_whitelist(blog_id, comment_id) {
	Element.show('ajaxloader_comments');
	var url = '/cms/modules/blogs/blogs_ajaxresponders.php';
	var pars = 'do=ajax_comment_whitelist&blog_id='+blog_id+'&comment_id='+comment_id;
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: comment_whitelisted,
			onFailure: function(){alert("Der opstod en AJAX-relateret fejl. Prøv venligst igen senere.")}
		});
	return false;
}

function comment_whitelisted(originalRequest){
	var arr_status = originalRequest.responseText.split("|||");
	if (arr_status[0]=="SUCCESS") {
		$('comment_'+arr_status[2]).removeClassName('notapprovedcomment');
		$('commenttools_'+arr_status[2]).removeClassName('notapprovedcomment');
		alert(arr_status[1]);
	} else {
		alert(arr_status[1]);
	}
	Element.hide('ajaxloader_comments');
}

function comment_reject_whitelist(blog_id, comment_id, commenter_email) {
	Element.show('ajaxloader_comments');
	var url = '/cms/modules/blogs/blogs_ajaxresponders.php';
	var pars = 'do=ajax_comment_whitelist_revoke&blog_id='+blog_id+'&comment_id='+comment_id+'&commenter_email='+commenter_email;
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: comment_whitelist_revoked,
			onFailure: function(){alert("Der opstod en AJAX-relateret fejl. Prøv venligst igen senere.")}
		});
	return false;
}

function comment_whitelist_revoked(originalRequest){
	var arr_status = originalRequest.responseText.split("|||");
	if (arr_status[0]=="SUCCESS") {
		$('comment_'+arr_status[2]).addClassName('notapprovedcomment');
		$('commenttools_'+arr_status[2]).addClassName('notapprovedcomment');
		alert(arr_status[1]);
	} else {
		alert(arr_status[1]);
	}
	Element.hide('ajaxloader_comments');
}

function comment_markspam(comment_id) {
	Element.show('ajaxloader_comments');
	var url = '/cms/modules/blogs/blogs_ajaxresponders.php';
	var pars = 'do=ajax_comment_isspam&comment_id='+comment_id
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: comment_markedspam,
			onFailure: function(){alert("Der opstod en AJAX-relateret fejl. Prøv venligst igen senere.")}
		});
	return false;
}

function comment_markedspam(originalRequest){
	var arr_status = originalRequest.responseText.split("|||");
	if (arr_status[0]=="SUCCESS") {
		$('comment_'+arr_status[2]).addClassName('notapprovedcomment');
		$('commenttools_'+arr_status[2]).addClassName('notapprovedcomment');
		$('comment_'+arr_status[2]).addClassName('spamcomment');
		$('commenttools_'+arr_status[2]).addClassName('spamcomment');
	} else {
		alert(arr_status[1]);
	}
	Element.hide('ajaxloader_comments');
}

function comment_markham(comment_id) {
	Element.show('ajaxloader_comments');
	var url = '/cms/modules/blogs/blogs_ajaxresponders.php';
	var pars = 'do=ajax_comment_isham&comment_id='+comment_id
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: comment_markedham,
			onFailure: function(){alert("Der opstod en AJAX-relateret fejl. Prøv venligst igen senere.")}
		});
	return false;
}

function comment_markedham(originalRequest){
	var arr_status = originalRequest.responseText.split("|||");
	if (arr_status[0]=="SUCCESS") {
		$('comment_'+arr_status[2]).removeClassName('spamcomment');
		$('commenttools_'+arr_status[2]).removeClassName('spamcomment');
		$('comment_'+arr_status[2]).removeClassName('notapprovedcomment');
		$('commenttools_'+arr_status[2]).removeClassName('notapprovedcomment');
	} else {
		alert(arr_status[1]);
	}
	Element.hide('ajaxloader_comments');
}

function comment_makeeditable(comment_id) {
	var edit_element = $('commenttext_'+comment_id);
	var url = '/cms/modules/blogs/blogs_ajaxresponders.php';

	var editor = new Ajax.InPlaceEditor(edit_element, url, {
		rows:5,
		cols:25,
		okText:'Gem',
		cancelText:'Afbryd redigering',
		savingText:'Gemmer',
		clickToEditText:'Rediger kommentar',
		callback:function(form, value) { Element.show('ajaxloader_comments'); return 'do=ajax_comment_editsave&comment_id='+comment_id+'&commenttext=' + encodeURIComponent(value) },
		onComplete:comment_editcomplete
	}); 
	editor.enterEditMode();
}

function comment_editcomplete(originalRequest) {
	Element.hide('ajaxloader_comments');
}

function comment_delete(comment_id) {
	Element.show('ajaxloader_comments');
	var url = '/cms/modules/blogs/blogs_ajaxresponders.php';
	var pars = 'do=ajax_comment_delete&comment_id='+comment_id;
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: comment_deleted,
			onFailure: function(){alert("Der opstod en AJAX-relateret fejl. Prøv venligst igen senere.")}
		});
	return false;
}

function comment_deleted(originalRequest){
	var arr_status = originalRequest.responseText.split("|||");
	if (arr_status[0]=="SUCCESS") {
		Element.hide('comment_'+arr_status[2]);
		alert(arr_status[1]);
	} else {
		alert(arr_status[1]);
	}
	Element.hide('ajaxloader_comments');
}



function highlight(obj) {
	obj.style.backgroundColor = "#ffffbe";
}

function highlight_off(obj) {
	obj.style.backgroundColor = "#f1f1e3";
}

function verify() {
	if (trim($('blogentry_heading').value) == "") {
		alert("Du skal skrive en overskrift");
		return false;
	} else {
		document.forms['form_blogentry'].submit();
	}
}

/*
Blog-specific tab functions
*/

//Set tab to intially be selected when page loads:
//[which tab (1=first tab), ID of tab content to display]:
var initialtab=[1, "sc1"]

function do_onload(){
	// TAB FUNCTIONS TO DO ONLOAD
	if (document.getElementById("tablist")) {
		var cookiename = (typeof persisttype!="undefined" && persisttype=="sitewide") ? "tabcontent" : window.location.pathname;
		var cookiecheck = window.get_cookie && get_cookie(cookiename).indexOf("|") != -1;
		collecttablinks();
		initTabcolor=cascadedstyle(tabobjlinks[1], "backgroundColor", "background-color");
		initTabpostcolor=cascadedstyle(tabobjlinks[0], "backgroundColor", "background-color");
		if (typeof enablepersistence!="undefined" && enablepersistence && cookiecheck) {
			var cookieparse=get_cookie(cookiename).split("|");
			var whichtab=cookieparse[0];
			var tabcontentid=cookieparse[1];
			expandcontent(tabcontentid, tabobjlinks[whichtab]);
		} else {
			expandcontent(initialtab[1], tabobjlinks[initialtab[0]-1]);
		}
	}
	if ($('ajaxloader_comments')) {
		Element.hide('ajaxloader_comments');
	}
}

if (window.addEventListener)
window.addEventListener("load", do_onload, false)
else if (window.attachEvent)
window.attachEvent("onload", do_onload)
else if (document.getElementById)
window.onload=do_onload
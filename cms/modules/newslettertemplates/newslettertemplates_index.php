<?php
switch ($dothis) {
case "history":
	echo templateHistory($_GET[ntid]);
	break;
case "recipients_group":
	echo recipientUsergroupsForm($_GET[ntid]);
	break;
case "rediger":
   echo newsletterTemplateForm();
   break;
case "opret":
   echo newsletterTemplateForm();
   break;
default:
	echo listNewsletterTemplates();
	break;
}
?>
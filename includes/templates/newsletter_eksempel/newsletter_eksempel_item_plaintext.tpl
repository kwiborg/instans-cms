{* 
	template: 	newsletter_instans_item.tpl
	author: 	Instans / JNN
	date: 		28.08.2006	 
*}
{section name=item loop=$newsletter_items} 

==================================================
{$newsletter_items[item].HEADING} {if $newsletter_items[item].LINKURL}(link: {$newsletter_items[item].LINKURL}){/if} 
-------------------------------------------------- 
{$newsletter_items[item].CONTENT}
================================================== 

{/section}
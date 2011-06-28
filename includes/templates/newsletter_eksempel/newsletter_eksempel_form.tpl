{if $categories != ""}
<br/>
<form>
	<input type="hidden" name="action" value="update_categories_from_email" />
	<a href="{$update_categories_action}" target="_blank" style="color:#da2128;">Klik her for at tilpasse dine interesser</a>. Du modtager nyheder i følgende kategorier:
	<br />
	{section name=category loop=$categories}
		<input {if $categories[category].OPTED_OUT == 0}checked{/if} type="checkbox" name="subscribeto_category_{$categories[category].ID}" />&nbsp;{$categories[category].NAME}&nbsp;
	{/section}
	<br />
	<br />
</form>
{/if}
Hvis du ikke længere ønsker at modtage vores nyhedsbrev, kan du benytte dette link: <a href="{$unsubscribe_url}" target="_blank" style="color:#da2128;">Afmeld dette nyhedsbrev</a>. Du er altid velkommen til at tilmelde dig igen på vores hjemmeside.

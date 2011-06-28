<?php
// blogmanager_common.inc.php
function blogForm() {
	global $fckEditorPath;
	$blogdata = hentRow($_GET[id], "BLOGS");
//	print_r($blogdata);
	if ($_GET[dothis]=="rediger") {
		$html .= "<h1>Rediger \"$blogdata[TITLE]\"</h1><br/>";
	} else {
		$html .= "<h1>Opret blog</h1><br/>";
	}
	$html .= "<form action='' method='post' name='form_blog' id='form_blog'>";	
	$html .= "<ul id='tablist'>
					<li><a href='#' class='current' onClick='return expandcontent(\"sc1\", this)'>Generelt</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc6\", this)'>Visning</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc2\", this)'>Kommentarer</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc3\", this)'>Feeds</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc4\", this)'>Rettigheder</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc5\", this)'>Søgeoptimering</a></li>";
	$html .= "</ul>";
	$html .= "<div id='tabcontentcontainer'>";
	$html .= "<div id='sc1' class='tabcontent'>
					<h2>Publiceret</h2>";
	$html .= createSelectYesNo("blog_published", $blogdata[PUBLISHED]);
	$html .= "<h2>Sprog</h2>";
	$html .= buildLanguageDropdown($blogdata[LANGUAGE], false, "blog_language");
	$html .= "<h2>Titel</h2>
				<input id='blog_title' name='blog_title' value='$blogdata[TITLE]' class='inputfelt' />
				<h2>Kort beskrivelse (tag line)</h2>
				<input id='blog_subtitle' name='blog_subtitle' value='$blogdata[SUBTITLE]' class='inputfelt' />
				<h2>Beskrivelse</h2>";
	$oFCKeditor = new FCKeditor('blog_description') ;
	$oFCKeditor->BasePath = $fckEditorPath . "/";
	$oFCKeditor->ToolbarSet	= "CMS_BlogDescription";
	$oFCKeditor->Height	= "200";
	$oFCKeditor->Value	= $blogdata["DESCRIPTION"];
	$oFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
	$html .= $oFCKeditor->CreateHtml() ;
	$html .= "</div>";
	$html .= "<div id='sc6' class='tabcontent'>";
	$html .= "<h2>Template</h2>";
	$html .= buildTemplateDropdown($blogdata[TEMPLATE_ID], $id="blog_template");
	$html .= "<h2>Visning af indlæg på oversigt</h2>";
	$html .= "<p><input type='text' maxlength='2' size='2' id='blog_items_displaycount' name='blog_items_displaycount' value='$blogdata[ITEMS_DISPLAYCOUNT]' /> indlæg ";
	$html .= "<select id='blog_show_completepost' name='blog_show_completepost' class='inputselect' size='1'>";
	$html .= "<option value='1' ";
	if ($blogdata[SHOW_COMPLETEPOST] == 1) {
		$html .= "selected='selected'";
	}
	$html .= ">i fuld længde</option>";
	$html .= "<option value='0' ";
	if ($blogdata[SHOW_COMPLETEPOST] == 0) {
		$html .= "selected='selected'";
	}
	$html .= ">som resumé</option>";
	$html .= "</select></p>";
	
	$html .= "<h2>Længde på resumé</h2>
				<p><input type='text' maxlength='2' size='2' id='blog_syndication_snippetlength' name='blog_syndication_snippetlength' value='$blogdata[SYNDICATION_SNIPPETLENGTH]' /> sætninger.</p>";
	
	$html .= "<h2>Vis profilbillede ved hvert indlæg</h2>";
	$html .= createSelectYesNo("blog_show_profileimage", $blogdata[SHOW_PROFILEIMAGE]);
	$html .= "</div>";
	$html .= "<div id='sc2' class='tabcontent'>
				<h2>Tillad kommentarer</h2>";
	$html .= createSelectYesNo("blog_comments_allowed", $blogdata[COMMENTS_ALLOWED]);
	$html .= "<h2>Tillad HTML i kommentarer</h2>
				<input id='blog_comments_striptags' name='blog_comments_striptags' value='$blogdata[COMMENTS_STRIPTAGS]' class='inputfelt' />
				<p>Hvis feltet er tomt er det <em>ikke</em> tilladt at bruge HTML i kommentarerne. Hvis du skriver HTML-tags er kun disse tags tilladt. Skriv f.eks. \"&lt;a&gt;\" for at tillade links og fjerne alle andre HTML koder.</p>
			<h2>Modtag kommentarer på e-mail</h2>";
	$html .= createSelectYesNo("blog_comments_email", $blogdata[COMMENTS_EMAIL]);
	$html .= "<h2>Godkend kommentarer</h2>";
	$html .= createSelectYesNo("blog_approvecomments", $blogdata[APPROVECOMMENTS]);
	$html .= "<p>Hvis du vælger denne indstilling, skal du godkende kommentarer før de bliver vist på hjemmesiden. Kommentarer fra e-mail adresser, som er tilføjet bloggens \"whitelist\" bliver automatisk godkendt.</p>
				<h2>Avanceret spambekæmpelse: Benyt Akismet nøgle</h2>
				<p>Det er muligt at abonnere på spam filtrering hos <a href='http://www.akismet.com' target='_blank'>Akismet</a>. Akismet er gratis på personlige blogs, men koster penge til kommerciel brug (virksomheds-blogs, blog netværk, professionelle blogs).</p><h3>Indtast Akismet nøgle</h3>
				<input id='blog_spamprevent_akismetkey' name='blog_spamprevent_akismetkey' value='$blogdata[SPAMPREVENT_AKISMETKEY]' class='inputfelt' />";
	$html .= "<h2>Avanceret spambekæmpelse: Brugere skal indtaste kode for at kommentere</h2>";
	$html .= createSelectYesNo("blog_spamprevent_captcha", $blogdata[SPAMPREVENT_CAPTCHA]);
	$html .= "</div>";
	$html .= "<div id='sc3' class='tabcontent'>
				<h2>Feeds og sikkerhed</h2>
				<p>Denne blog er tilgængelig som XML / RSS feed på adressen:<br/>";
	$feed_url = return_feed_url("BLOGS", $blogdata[ID]);
	$html .= "<input id='blog_feedadress' value='$feed_url' readonly class='inputfelt' />";
	$html .= "</p>
				<p>Ved at publicere RSS / XML feeds for bloggen giver du adgang til alle, som kender adressen på feed'et. Kun brugere, der har adgang til at læse bloggen på hjemmesiden kan se denne adresse. Men har man først fået oplyst adressen, er der fri adgang til feed'et - det er ikke muligt at kræve login.</p>
				<p><strong>Derfor bør du ikke publicere et feed hvis bloggen indeholder følsomme oplysninger.</strong></p>
				<p>Det er muligt at opnå en vis grad af sikkerhed ved jævnligt at ændre feed adressen, men dette betyder at alle legitime brugere af feed'et skal logge ind på hjemmesiden for at opdatere deres abonnements-adresser.</p>
				<h2>Publicér XML/RSS feed for denne blog</h2>";
	$html .= createSelectYesNo("blog_syndication_allowed", $blogdata[SYNDICATION_ALLOWED]);
	$html .= "<h2>Visning af indlæg i feed</h2>";
	$html .= "<select id='blog_syndication_showcompletepost' name='blog_syndication_showcompletepost' class='inputselect' size='1'>";
	$html .= "<option value='1' ";
	if ($blogdata[SYNDICATION_SHOWCOMPLETEPOST] == 1) {
		$html .= "selected='selected'";
	}
	$html .= ">Vis fuld længde</option>";
	$html .= "<option value='0' ";
	if ($blogdata[SYNDICATION_SHOWCOMPLETEPOST] == 0) {
		$html .= "selected='selected'";
	}
	$html .= ">Vis kun resumé</option>";
	$html .= "</select>";
	$html .= "<h2>Ny feed adresse</h2>";
	$html .= createSelectYesNo("blog_syndication_newkey", 0);
	$html .= "<p><strong>Bemærk:</strong> Når bloggen gemmes, opdateres feed adressen alle brugere af feed'et skal opdatere deres abonnements-adresser.</p>";
	$html .= "</div>";
	$html .= "<div id='sc4' class='tabcontent'>";
	$html .= "<h2>Om rettigheder</h2>";
	$html .= "<p>Hvis der ikke er sat specifikke rettigheder, har alle ret til at udføre den beskrevne funktion. Dog kræver det altid adgang til CMS + adgang til blog-modulet at kunne skrive indlæg på en blog.</p>";
	$html .= datapermission_set("DATA_CMS_BLOG_PUBLISH", "BLOGS", $_GET[id]);
	$html .= datapermission_set("DATA_FE_BLOG_READ", "BLOGS", $_GET[id]);
	$html .= datapermission_set("DATA_FE_BLOG_COMMENT", "BLOGS", $_GET[id]);
	$html .= "</div>";
	$html .= "<div id='sc5' class='tabcontent'>";
	$html .= build_rewritekey_input("Meningsfuld side-adresse", "BLOGS", $_GET[id], "this.form.blog_title.value");
	$html .= "</div>";
	$html .= "</div>"; // tabcontentcontainer
	$html .= "<input type='hidden' id='dothis' name='dothis' value='update' />";
	$html .= "<input type='hidden' id='blog_id' name='blog_id' value='$_GET[id]' />";
	$html .= "<div class='knapbar'>
				<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=blogmanager\"' />
				<input type='button' value='Gem' onclick='verify()' />
			</div>";
	$html .= "</form>";
	return $html;
}

function listBlogs() {
	$html = "<h1>Blogs</h1>
				<div class='feltblok_header'></div>
				<div class='feltblok_wrapper'>";
	
	$sql = "select * from BLOGS where DELETED = 0 and UNFINISHED = 0 and SITE_ID in (0,'$_SESSION[SELECTED_SITE]')";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$html .= 	"<table class='oversigt'>
						<tr class='trtop'>
							<td class='kolonnetitel'>Titel</td>
							<td class='kolonnetitel'>Seneste indlæg</td>
							<td class='kolonnetitel'>Antal indlæg / kommentarer</td>
							<td class='kolonnetitel'>Funktioner</td>
						</tr>";
		while ($row = mysql_fetch_array($result)) {
			$sql = "select UNIX_TIMESTAMP(CREATED_DATE) as LATESTPOST from BLOGPOSTS where BLOG_ID = '$row[ID]' and PUBLISHED = '1' and UNFINISHED = 0 and DELETED = 0 order by Date(CREATED_DATE) desc, Time(CREATED_DATE) desc LIMIT 1";
			$result2 = mysql_query($sql);
			if (mysql_num_rows($result2) > 0) {
				$th = mysql_fetch_array($result2);
				$lastpost = returnNiceDateTime($th[LATESTPOST], 1);
			} else {
				$lastpost = "Endnu ingen indlæg";
			}
			$html .= "<tr>
					<td>$row[TITLE]</td>
					<td>$lastpost</td>
					<td>";
			$sql = "select count(*) from BLOGPOSTS where BLOG_ID = '$row[ID]' and PUBLISHED = '1' and UNFINISHED = 0 and DELETED = 0";
			$result3 = mysql_query($sql);
			$entrycount = mysql_result($result3,0);
			$html .=	"$entrycount / ".comment_count(false, "BLOGPOSTS", $row[ID])."</td>
					<td>
						<input type='button' class='lilleknap' value='Slet'  onclick='if (confirm(\"Vil du slette bloggen?\")) location=\"index.php?content_identifier=blogmanager&amp;dothis=delete&amp;id=$row[ID]\"' />
						<input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=blogmanager&amp;dothis=rediger&amp;id=$row[ID]\"' />
					</td>
				</tr>";
		}
		$html .= "</table>";
	} else {
		$html .= "Der er ikke oprettet nogen blogs.";
	}
	$html .= "</div>";
	$html .= "<div class='knapbar'>
					<input type='button' value='Opret ny blog' onclick='location=\"index.php?content_identifier=blogmanager&amp;dothis=opret\"' />
				</div>";
	return $html;
}

?>
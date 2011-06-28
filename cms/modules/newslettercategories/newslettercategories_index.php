<script type="text/javascript">
	function newslettercat_checkSub(obj, mode){
		a = document.getElementsByTagName("input");
		for(i=0; i<a.length; i++){
			if (a[i].name.indexOf("_" + obj.name + "_") > -1){
				if (mode){
					a[i].checked = true;
					a[i].disabled = true;
				}
				if (!mode){
					a[i].checked = false;
					a[i].disabled = false;
				}
			}
		}	
	}
	
	function newCategory(groupId){
		document.catform.groupid.value = groupId;
		document.catform.dothis.value = "newcat";
		document.catform.submit();
	}

	function newCategoryGroup(){
		document.catform.dothis.value = "newcatgroup";
		document.catform.submit();
	}

	function deleteCheckedCats(){
		if (confirm("Vil du slette de valgte kategorier?")){	
			document.catform.dothis.value = "deletecats";
			document.catform.submit();
		}
	}

</script>
<h1>Nyhedsbrev: Interessekategorier</h1>
<form method="post" action="" name="catform">
<input type="hidden" name="dothis" value="" />
<input type="hidden" name="groupid" value="" />
<div class="feltblok_header">Oprettede interessekategorier inddelt i grupper</div>
<div class="feltblok_wrapper">
	<?php
		echo newsletter_returnGroupedCategories();	
	?>
</div>

<div class="feltblok_header">Opret ny interessekategori-gruppe</div>
<div class="feltblok_wrapper">
	<h2>Gruppens titel (fx. "Alderstrin", "Producenter" eller "Interesseområder")</h2>
	<input type="text" class="inputfelt" name="newgroup_title" />
	<div class="knapbar"><input type='button' value="Opret gruppe" onclick="newCategoryGroup()" /></div>
</div>
</form>
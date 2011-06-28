<script type="text/javascript">
	if (window.addEventListener) {
		window.addEventListener("load", do_onload, false);
	} else if (window.attachEvent) {
		window.attachEvent("onload", do_onload);
	} else if (document.getElementById) {
		window.onload=do_onload;
	}

	function do_onload() {
		var nodes = document.getElementsByClassName("cart_showrelated_link");
		for(i = 0; i < nodes.length; i++) {
			nodes[i].onclick = function(){
				var linkid = this.id;
				var arr_linkid = linkid.split("_");
				var itemid = arr_linkid[2];

				// Hide cart_showrelated_[id]
				var showrelated = "cart_showrelated_"+itemid;
				Element.hide(showrelated);
				
				// Show + hightlight cart_related_[id]
				var related = "cart_related_"+itemid;
				$(related).removeClassName('hide_for_ajax');
				new Effect.Highlight($(related));
				return false;
			};
		}
	}
</script>
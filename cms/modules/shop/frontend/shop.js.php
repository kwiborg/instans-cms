<?php global $cmsAbsoluteServerPath, $cmsURL;

if (is_file($cmsAbsoluteServerPath . "/modules/$arr_content[mode]/frontend/$arr_content[mode].css")) {
	$css1 = "$cmsURL/modules/$arr_content[mode]/frontend/$arr_content[mode].css";
}
if (is_file($_SERVER['DOCUMENT_ROOT']."/includes/css/$arr_content[mode].css")) {
	$css2 = "$cmsDomain/includes/css/$arr_content[mode].css";
}

?>
<script type='text/javascript'>
Event.observe(window, 'load', initPage, false);

function initPage() {
	// Makes productlinks pop-up
	var plinks = document.getElementsByClassName('shopproductlink');
	var nodes = $A(plinks);
	nodes.each(
		function(node) {
			node.onclick = function() { 
				var imageurl = node.href;
				var poptitle = node.title;
				
				// Calculate window size
				var size = node.className;
				sizearr = size.split(" ");
				size = sizearr[1];
				sizearr = size.split("x");
				width = Number(sizearr[0]) + 50;
				height = Number(sizearr[1]) + 100;
				
				var pars = 'height='+height+',width='+width;
				newwindow=window.open('','popupimage', pars);

				var pophtml = "<html><head><title>" + poptitle + "<\/title>";
				<?php if ($css1 != "") { ?>
				pophtml += '<link rel="stylesheet" type="text/css" href="<?php echo $css1; ?>" \/>';
				<?php } ?>
				<?php if ($css2 != "") { ?>
				pophtml += '<link rel="stylesheet" type="text/css" href="<?php echo $css2; ?>" \/>';
				<?php } ?>
				pophtml += '<\/head><body>';
				pophtml += '<div class="popimagecontainer"><h1>'+poptitle+'<\/h1>';
				pophtml += '<img src="'+imageurl+'" alt="'+poptitle+'" \/><\/div>';
				pophtml += '<div class="popimagecontainer"><a href="javascript:self.close()">';
				pophtml += '<?php echo cmsTranslate("Close"); ?>';
				pophtml += '<\/a><\/div>';
				pophtml += '<\/body><\/html>';
							
				var tmp = newwindow.document;
				tmp.write(pophtml);
				tmp.close();


				if (window.focus) {newwindow.focus()}
				return false; 
			}
		});
}
</script>
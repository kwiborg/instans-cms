<?php
	global $cmsAbsoluteServerPath;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo returnPageTitleTag($arr_content, "ExampleSite: ") ?></title>
  <?php echo metaTagGenerator($arr_content); ?>
  <?php echo modeHtmlHeaderIncludes($arr_content, "css"); ?>
  <?php include ($cmsAbsoluteServerPath . "/frontend/frontend_headerJS.php"); ?> 
  <script src="<?=$arr_content[baseurl];?>/includes/javascript/prototype.js" type="text/javascript"></script>
  <script src="<?$arr_content[baseurl];?>/includes/javascript/scriptaculous.js" type="text/javascript"></script>    
  <script src="<?$arr_content[baseurl];?>/includes/javascript/example.js" type="text/javascript"></script>
  <script src="<?$arr_content[baseurl];?>/includes/javascript/common.js" type="text/javascript"></script>
  <?php echo modeHtmlHeaderIncludes($arr_content, "javascript"); ?>
		<style type="text/css">
			body{
				margin:0;
				background-color:#fff;
			}
			form{
				margin:0;
				padding:0;
			}
			#outer_wrapper{
				width:950px;
				margin:0 auto;
			}
			#top_wrapper{
				height:170px;
				background-color:#aaa;
				border-bottom:2px solid #fff;
			}
			#path_wrapper{
				position:relative;
				height:30px;
				background-color:#ccc;
				border-bottom:2px solid #fff;	
				font:normal 11px/30px arial;
				padding:0 15px 0 15px;
			}
			#left_column{
				float:left;
				display:inline;
				width:155px;
				background-color:#ddd;
				border-right:2px solid #fff;	
				padding-top:20px;			
				padding-right:20px;
				padding-left:5px;	
			}
			#center_column{
				float:left;
				display:inline;
				width:558px;
				background-color:#ddd;				
				border-right:2px solid #fff;
				padding:20px;
				font:normal 11px/16px arial;				
			}
			#center_column h1{
				font:normal 18px arial;
				margin:0 0 20px 0;
			}
			#center_column h2{
				font:bold 14px arial;
				margin:0 0 0 0;
				margin-collapse:collapse;
			}
			h2, h3, h4, h5, h6 {margin-bottom: 0;}
			#center_column p{
				margin: 0 0 1em;
			}
			#right_column{
				float:left;
				display:inline;
				width:158px;
				background-color:#ddd;				
				padding:5px;
			}
			#left_column ul{
				font:normal 11px/18px arial;
				padding:0;
				margin:0 0 0 25px;
				list-style-type:square;
			}
			#left_column ul li{
				padding:0;
				margin:0;
			}
			.marginBox{
				background-color:#ccc;
				width:90%;
				min-height:100px;
				margin-bottom:20px;
				font:normal 11px arial;
				padding:5px;
			}
			.marginBox h2{
				font:bold 11px arial;
				background-color:#bbb;
				padding:5px;
				margin:0 0 5px 0;
			}
			.marginBox .boxTitle{
				font:bold 11px arial;
				background-color:#bbb;
				padding:5px;
				margin:0 0 5px 0;
			}
			#footer{
				height:100px;
				background-color:#ccc;
				border-top:2px solid #fff;
				text-align:center;
				font:normal 11px/50px arial;
			}
			#print{
				margin-top:20px;
				color:#666;
			}
			
			div.blog_overviewitem{
				background-color:#efe;
				padding:10px;
				margin-bottom:20px;
			}
			div.blog_overviewitem h1{
				margin:0 0 10px 0 !important;
			}
			div.blog_profilebox{
				border:1px solid #aaa;
				padding:5px;
				margin-bottom:10px;
			}
			div.blog_profilebox div.blog_author_image{
				float:right;
			}
			div.blog_profilebox div.blog_author_time{
				float:left;
				width:75%;
			}
			div.blog_profilebox .clearer{
				clear:both;
			}
			div.blog_overview_content{
			}
			div.blog_overviewitem_bottombar{
				margin-top:10px;				
			}
			div.blog_overviewitem_bottombar span.divider{
				margin:0 10px 0 10px;
				color:#aaa;
			}
			div#blogpost_commentform_box{
				border:1px solid #fff;
				padding:10px;
				margin-top:20px;
			}
			div#blogpost_comments_closed{
				border:1px solid #fff;
				padding:10px;
				margin-top:20px;
				font-weight:bold;
			}
			.blogpost_commentform_label{
				font-weight:bold;
			}
			.blogpost_commentfield{
				margin-bottom:10px;
			}
			.blogpost_commentform_clear{
				clear:both;
			}
			.blogpost_commentform_buttons{
				margin-top:20px;
				text-align:right;
			}
			div#blogpost_comments_box{
				border:1px solid #fff;
				padding:10px;
				margin-top:20px;				
			}
			div#blogpost_comments_box ol{
				list-style-type:none;
				margin:0;
				padding:0;
			}
			div#blogpost_comments_box ol li{
				background-color:#aaa;
				margin:0;
				margin-bottom:10px;
				padding:10px;
			}
			div.blogpost_comment_byline{
				font-weight:bold;
			}
			li.authorcomment{
				border:3px solid #060;
			}
			div.blog_useralert_red, div.blog_useralert_green{
				padding:10px;
				background-color:#600;
				font-weight:bold;
				color:#fff;
				margin-bottom:20px;
			}
			div.blog_useralert_green{
				background-color:#060;
			}
			div.blog_useralert_red{
				background-color:#600;
			}
			div.blogpost_snippet{
				font-weight:bold;
				margin-bottom:10px;
			}
			.marginBox ul{
				list-style-type:none;
				font:normal 11px arial;
				margin:0;
			}
			.blog_authorbox_image{
				text-align:center;
			}
		</style>
	</head>
	<body>
		<div id="outer_wrapper">
			<div id="top_wrapper">
				</div>
			<div id="path_wrapper"><?=returnHTMLMenuPath($arr_content[pageid], $arr_content[mode], "Du er her: ", "", " &raquo; ", $arr_content)?><div style="position:absolute; right:15px; top:0">Log ind</div></div>
			<div id="main_wrapper">
				<div id="left_column">
					<ul>
						<li>Menupunkt niveau 1</li>
						<li>Menupunkt niveau 1</li>
						<li>Menupunkt niveau 1
							<ul>
								<li>Menupunkt niveau 2
									<ul>
										<li>Menupunkt niveau 3</li>
									</ul>
								</li>
							</ul>
						</li>
						<li>Menupunkt niveau 1</li>
						<li>Menupunkt niveau 1</li>
						<li>Menupunkt niveau 1</li>
					</ul>
				</div>
				<div id="center_column">
					<?=buildPageMainContent($arr_content)?>
				</div>
				<div id="right_column">
					<?=blogtemplate_aboutauthor_box($arr_content[blogid], $arr_content)?>
					<?=blogtemplate_aboutblog_box($arr_content[blogid], $arr_content)?>
					<div class="marginBox">
						<h2>Arkiv pr. måned</h2>
						<?=blogtemplate_archive_months($arr_content[blogid], $arr_content)?>
					</div>
					<div class="marginBox">
						<h2>Tags</h2>
						<?=blogtemplate_archive_tags($arr_content[blogid], $arr_content)?>
					</div>
					<div class="marginBox">
						<h2>Senest kommenterede indlæg</h2>
						<?=blogtemplate_latest_commented($arr_content[blogid], $arr_content)?>
					</div>
				</div>
				<div style="clear:both"></div>
			</div>
			<div id="footer">Firmanavn | Adresse | Postnummer | By | Tlf | Fax | E-mail</div>
		</div>
		<script type="text/javascript">
			document.getElementById("left_column").style.height = document.getElementById("center_column").offsetHeight-20+"px";
			document.getElementById("right_column").style.height = document.getElementById("center_column").offsetHeight-40+"px";
		</script>
	</body>
</html>

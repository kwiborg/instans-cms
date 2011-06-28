<?php
	include_once("$_SERVER[DOCUMENT_ROOT]/cms_config.inc.php");
	session_start();
	$ses_id = session_id();
?>

<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="/cms/scripts/fancy_upload/css/common.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="/cms/modules/filearchive2/filearchive2_upload.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="/cms/modules/filearchive2/filearchive2_style.css" type="text/css" media="screen" />
	<script type="text/javascript" src="/cms/scripts/fancy_upload/js/moo_tools.js"></script>
	<script type="text/javascript" src="/cms/scripts/fancy_upload/js/Fx.ProgressBar.js"></script>
	<script type="text/javascript" src="/cms/scripts/fancy_upload/swiff/Swiff.Uploader.js"></script>
	<script type="text/javascript" src="/cms/scripts/fancy_upload/js/FancyUpload2.js"></script>
	<script type="text/javascript">

	//<![CDATA[

window.addEvent('domready', function() { // wait for the content
	// our uploader instance 
	var up = new FancyUpload2($('demo-status'), $('demo-list'), { // options object
		// we console.log infos, remove that in production!!
		verbose: true,

		// url is read from the form, so you just have to change one place
		url: $('form-demo').action,
		
		// path to the SWF file
		path: '/cms/scripts/fancy_upload/swiff/Swiff.Uploader.swf',
		
		// remove that line to select all files, or edit it, add more items
		/*
		typeFilter: {
			'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png'
		},
		*/
		fileListMax: 5,	
		
		// this is our browse button, *target* is overlayed with the Flash movie
		target: 'demo-browse',
		
		// graceful degradation, onLoad is only called if all went well with Flash
		onLoad: function() {
			$('demo-status').removeClass('hide'); // we show the actual UI
			$('demo-fallback').destroy(); // ... and hide the plain form
			
			// We relay the interactions with the overlayed flash to the link
			this.target.addEvents({
				click: function() {
					return false;
				},
				mouseenter: function() {
					this.addClass('hover');
				},
				mouseleave: function() {
					this.removeClass('hover');
					this.blur();
				},
				mousedown: function() {
					this.focus();
				}
			});

			// Interactions for the 2 other buttons
			
			$('demo-clear').addEvent('click', function() {
				up.remove(); // remove all files
				return false;
			});

			$('demo-upload').addEvent('click', function() {
				up.start(); // start upload
				return false;
			});
		},
		
		// Edit the following lines, it is your custom event handling
		
		/**
		 * Is called when files were not added, "files" is an array of invalid File classes.
		 * 
		 * This example creates a list of error elements directly in the file list, which
		 * hide on click.
		 */ 
		onSelectFail: function(files) {
			files.each(function(file) {
				new Element('li', {
					'class': 'validation-error',
					html: file.validationErrorMessage || file.validationError,
					title: MooTools.lang.get('FancyUpload', 'removeTitle'),
					events: {
						click: function() {
							this.destroy();
						}
					}
				}).inject(this.list, 'top');
			}, this);
		},
		
		/**
		 * This one was directly in FancyUpload2 before, the event makes it
		 * easier for you, to add your own response handling (you probably want
		 * to send something else than JSON or different items).
		 */
		onFileSuccess: function(file, response) {
			var json = new Hash(JSON.decode(response, true) || {});
			
			if (json.get('status') == '1') {
				file.element.addClass('file-success');
				file.info.set('html', '<strong>Filen er nu uploaded!</strong>');
			} else {
				file.element.addClass('file-failed');
				file.info.set('html', '<strong>Der opstod en fejl:</strong> ' + (json.get('error') ? (json.get('error') + ' #' + json.get('code')) : response));
			}
		},
		
		/**
		 * onFail is called when the Flash movie got bashed by some browser plugin
		 * like Adblock or Flashblock.
		 */
		onFail: function(error) {
			switch (error) {
				case 'hidden': // works after enabling the movie and clicking refresh
					alert('To enable the embedded uploader, unblock it in your browser and refresh (see Adblock).');
					break;
				case 'blocked': // This no *full* fail, it works after the user clicks the button
					alert('To enable the embedded uploader, enable the blocked Flash movie (see Flashblock).');
					break;
				case 'empty': // Oh oh, wrong path
					alert('A required file was not found, please be patient and we fix this.');
					break;
				case 'flash': // no flash 9+ :(
					alert('To enable the embedded uploader, install the latest Adobe Flash plugin.')
			}
		},
		
		onComplete: function(){top.location.href='/cms/index.php?content_identifier=filearchive2&dothis=editfil&folder_id=<?=$_GET[folderid];?>&context=upload'}

	
	});
	
});
		//]]>

	</script>
	
	<style type="text/css">
	#demo-status
	{
		padding:				10px 15px;
		width:					420px;
	}
	 
	#demo-status .progress
	{
		background:				white url(/cms/images/progress.gif) no-repeat;
		background-position:	+50% 0;
		margin-right:			0.5em;
		margin-top: 			2px;
		margin-bottom: 			5px;
	}
	 
	#demo-status .progress-text
	{
		font-weight:			bold;
		font-family:verdana,arial,sans-serif;
		font-size:11px;
	}
	 
	#demo-list
	{
		list-style:				none;
		width:					450px;
		margin:					0;
	}
	 
	#demo-list li.file
	{
		border-bottom:			1px solid #cdcdcd;
		background:				url(/cms/images/file.png) no-repeat 4px 4px;
	}
	#demo-list li.file.file-uploading
	{
		background-image:		url(/cms/images/uploading.png);
		background-color:		#D9DDE9;
	}
	#demo-list li.file.file-success
	{
		background-image:		url(/cms/images/success.png);
	}
	#demo-list li.file.file-failed
	{
		background-image:		url(/cms/images/failed.png);
	}
	 
	#demo-list li.file .file-name
	{
		margin-left:			44px;
		display:				block;
		clear:					left;
		line-height:			40px;
		height:					40px;
		font-family:verdana,arial,sans-serif;
		font-size:11px;
	}
	#demo-list li.file .file-size
	{
		font-size:				0.9em;
		line-height:			18px;
		float:					right;
		margin-top:				2px;
		margin-right:			6px;
		font-family:verdana,arial,sans-serif;
		font-size:11px;
	}
	#demo-list li.file .file-info
	{
		display:				block;
		margin-left:			44px;
		line-height:			20px;
		font-family:verdana,arial,sans-serif;
		font-size:11px;
		clear
	}
	#demo-list li.file .file-remove
	{
		clear:					right;
		float:					right;
		line-height:			18px;
		margin-right:			6px;
		font-family:verdana,arial,sans-serif;
		font-size:11px;
	}
	#demo-list li.file a.file-remove  {
		color: black;
	}
	
	.overall-title, .current-title {
		font-family:verdana,arial,sans-serif;
		font-size:11px;
	}

	.current-text {
		font-family:verdana,arial,sans-serif;
		font-size:11px;
	}
	
	</style>
</head>
<body>


<form action="/cms/modules/filearchive2/filearchive2_upload.php?folderid=<?=$_GET['folderid']; ?>&PHPSESSID=<?=$ses_id; ?>" method="post" enctype="multipart/form-data" id="form-demo">
	<div id="demo-fallback">
		<input type="file" name="Filedata" />
	</div>

	<div id="demo-status" class="hide">
		<div>
			<span class="overall-title">Samlet status for upload</span><br/>
			<img src="/cms/images/bar.gif" class="progress overall-progress" />
		</div>
		<div>
			<span class="current-title">Status for aktuel fil</span><br />
			<img src="/cms/images/bar.gif" class="progress current-progress" />
		</div>
		<div class="current-text"></div>
	</div>
 
	<ul id="demo-list"></ul>
	
	<div style="background-color: #dddddd; padding: 5px 5px 5px 20px;">
		<div style="float: left;">
			<input type="button" id="demo-browse" value="Vælg filer" />
			<input type="button" id="demo-clear" value="Ryd liste" />
		</div>
		<div style="text-align: right;">
			<input type="button" value="Afbryd" onclick="top.location='/cms/index.php?content_identifier=<?php echo $_GET[content_identifier] ?>&folder_id=<?=$_GET['folderid']; ?>'" />
			<input type="button" id="demo-upload" value="Start Upload"/>
		</div>
	</div>
</form>
</body>
</html>
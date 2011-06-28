// Register the related command.
FCKCommands.RegisterCommand( 
	'customImage', 
	new FCKDialogCommand(
		'customImage', 
			'Inds&aelig;t billede', FCKPlugins.Items['customImage'].Path + 'fck_customImage.php', 
			600, 600)
);

// Create the toolbar button.
var ocustomImageItem = new FCKToolbarButton( 'customImage', 'Inds&aelig;t billede' ) ;
ocustomImageItem.IconPath = FCKPlugins.Items['customImage'].Path + 'customImage.gif' ;
FCKToolbarItems.RegisterItem( 'customImage', ocustomImageItem ) ;

// The object used for all HtmlTiles operations.
var FCK_customImage = new Object() ;

// Insert a new html block at the actual selection.
FCK_customImage.Insert = function( html ) {
	FCK.InsertHtml(html);
}

// alert(FCKCommands.GetCommand('customImage'));

/** 
* Add an additional Context Menu for the plugin 
* Overwrite the internal function that returns the Context Menu items 
*/ 
FCKContextMenu.__GetGroup = FCKContextMenu._GetGroup; 

FCKContextMenu._GetGroup = function( groupName ) {
	var oGroup ;
	
	switch ( groupName ) { 
		case 'Image' : 
			oGroup = new FCKContextMenuGroup( true, this, 'Image', FCKLang.ImageProperties, true ) ;

			//
			// Overwrite the internal function that tells the visibility of items
			// in a Context Menu 
			//

			oGroup.__RefreshState = oGroup.RefreshState; 
			oGroup.RefreshState = function() { 
				// Get the actual selected tag (if any). 
				var oTag = FCKSelection.GetSelectedElement() ; 
				var sTagName ; 
				 
				if ( oTag ) 
				sTagName = oTag.tagName ; 
				// Set items visibility. 
				this.SetVisible( sTagName == 'INPUT' && ( oTag.type == 'text' ) ) ; 
				// 
				// Calls the original function 
				// 
				this.__RefreshState(); 
			} 
			break; 
		default: 
			// 
			// Calls the original function 
			// 
			oGroup = this.__GetGroup(groupName); 
	} 
	return oGroup; 
}

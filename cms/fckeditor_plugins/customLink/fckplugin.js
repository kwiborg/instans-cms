// Register the related command.
FCKCommands.RegisterCommand( 
	'customLink', 
	new FCKDialogCommand(
		'customLink', 
			'Inds&aelig;t link', FCKPlugins.Items['customLink'].Path + 'fck_customLink.php', 
			400, 430)
);

// Create the "Insert_Matrix" toolbar button.
var ocustomLinkItem = new FCKToolbarButton( 'customLink', 'Lav link' ) ;
ocustomLinkItem.IconPath = FCKPlugins.Items['customLink'].Path + 'customLink.gif' ;
FCKToolbarItems.RegisterItem( 'customLink', ocustomLinkItem ) ;

// The object used for all HtmlTiles operations.
var FCK_customLink = new Object() ;

// Insert a new html block at the actual selection.
FCK_customLink.Insert = function( html ) {
	FCK.InsertHtml(html);
}
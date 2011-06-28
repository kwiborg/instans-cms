// Register the related command.
FCKCommands.RegisterCommand( 
	'Insert_Matrix', 
	new FCKDialogCommand(
		'Insert_Matrix', 
			'Indsæt PLP Matrix', FCKPlugins.Items['Insert_Matrix'].Path + 'fck_Insert_Matrix.php', 
			400, 300)
);

// Create the "Insert_Matrix" toolbar button.
var oInsert_MatrixItem = new FCKToolbarButton( 'Insert_Matrix', 'Indsæt PLP matrix' ) ;
oInsert_MatrixItem.IconPath = FCKPlugins.Items['Insert_Matrix'].Path + 'Insert_Matrix.gif' ;
FCKToolbarItems.RegisterItem( 'Insert_Matrix', oInsert_MatrixItem ) ;

// The object used for all HtmlTiles operations.
var FCK_insertMatrix = new Object() ;

// Insert a new html block at the actual selection.
FCK_insertMatrix.Insert = function( html ) {
	FCK.InsertHtml(html);
}
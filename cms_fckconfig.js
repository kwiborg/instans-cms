// CONFIG OVERRIDES ---------------------------------------------
FCKConfig.GeckoUseSPAN	= false ;
FCKConfig.LinkBrowser = false ;
FCKConfig.LinkUpload = false;
FCKConfig.LinkDlgHideAdvanced = true;
FCKConfig.LinkDlgHideTarget = false;
FCKConfig.ImageDlgHideAdvanced = true;
FCKConfig.ImageBrowser = false;
FCKConfig.ImageUpload = false;
FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/silver/' ;
FCKConfig.ProcessHTMLEntities	= true ;
FCKConfig.IncludeLatinEntities	= false ;
FCKConfig.IncludeGreekEntities	= false ;


// REGISTER PLUG-INS --------------------------------------------
FCKConfig.PluginsPath = '/cms/fckeditor_plugins/' ;
FCKConfig.Plugins.Add( 'Insert_Matrix' ) ;
FCKConfig.Plugins.Add( 'customLink' ) ;
FCKConfig.Plugins.Add( 'customImage' ) ;

// TOOLBARS -----------------------------------------------------
FCKConfig.ToolbarSets["CMS_Default"] = [
	['Source'],
	['Cut','Copy','Paste','PasteWord'],
	['Undo','Redo','RemoveFormat'],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['customLink','Unlink','Anchor'],
	['customImage','Flash','Table','Rule','SpecialChar', 'TextColor'],
	['FontFormat', 'About']
] ;

FCKConfig.ToolbarSets["CMS_BasicEditing"] = [
	['Source'],
	['Cut','Copy','Paste','PasteWord'],
	['Undo','Redo','RemoveFormat'],
	['Bold','Italic'],
	['OrderedList','UnorderedList']
] ;

FCKConfig.ToolbarSets["CMS_NewsletterContent"] = [
	['Source'],
	['Cut','Copy','Paste','PasteText','PasteWord'],
	['Undo','Redo','RemoveFormat'],
	['Bold','Italic','Underline'],
	['OrderedList','UnorderedList','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['customLink','Unlink'],
	['customImage']
];

FCKConfig.ToolbarSets["CMS_NewsletterItemContent"] = [
	['Source'],
	['Cut','Copy','Paste'],
	['Undo','Redo','RemoveFormat'],
	['Bold','Italic','Underline'],
	['OrderedList','UnorderedList'],
	['customLink','Unlink']
];

FCKConfig.ToolbarSets["CMS_BlogDescription"] = [
	['Source'],
	['Cut','Copy','Paste'],
	['Undo','Redo','RemoveFormat'],
	['Bold','Italic','Underline'],
	['OrderedList','UnorderedList'],
	['customLink','Unlink'],
	['customImage']
];

FCKConfig.ToolbarSets["Bookmaker_HovedKolonne"] = [
	['Cut','Copy','Paste'],
	['Undo','Redo','RemoveFormat'],
	['Bold','Italic','Underline'],
	['OrderedList','UnorderedList'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['customLink','Unlink'],
	['customImage', 'Table', 'Insert_Matrix', 'SpecialChar']
] ; 

 FCKConfig.ToolbarSets["Bookmaker_SideKolonne"] = [
	['Cut','Copy','Paste'],
	['Undo','Redo','RemoveFormat'],
	['Bold','Italic','Underline'],
	['OrderedList','UnorderedList'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['customLink','Unlink'],
	['customImage', 'SpecialChar']
] ; 

FCKConfig.ToolbarSets["CMS_MaillistContent"] = [
	['Source'],
	['Cut','Copy','Paste','PasteText','PasteWord'],
	['Undo','Redo','RemoveFormat'],
	['Bold','Italic','Underline'],
	['OrderedList','UnorderedList'],
	['Link','Unlink']
];

FCKConfig.ToolbarSets["CMS_MaillistItemContent"] = [
	['Source'],
	['Cut','Copy','Paste'],
	['Undo','Redo','RemoveFormat'],
	['Bold','Italic','Underline'],
	['OrderedList','UnorderedList'],
	['Link','Unlink']
];
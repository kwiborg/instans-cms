<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title><?=returnPageTitleTag($arr_content, "Dit Nye Website - ")?></title>
        <?php echo metaTagGenerator($arr_content); ?>
        <link rel="stylesheet" href="/includes/css/eksempel.css" type="text/css" />
        <?php echo modeHtmlHeaderIncludes($arr_content, "css"); ?>
        <script src="<?=$arr_content[baseurl];?>/includes/javascript/prototype.js" type="text/javascript"></script>
        <script src="<?=$arr_content[baseurl];?>/includes/javascript/scriptaculous.js" type="text/javascript"></script>        <script src="<?=$arr_content[baseurl];?>/includes/javascript/common.js" type="text/javascript"></script>      
        <?php echo modeHtmlHeaderIncludes($arr_content, "javascript"); ?>
        <?php include ($_SERVER[DOCUMENT_ROOT]."/cms/frontend/frontend_headerJS.php"); ?>    
    </head>
    <body>
        <div id="sitewrapper">
            <div id="topwrapper">
                <h1 id="logo"><?=returnFieldValue("SITES", "SITE_NAME", "ID", 1)?></h1>
                <div id="mainmenu">
                    <? newBuildFrontendMenu(1, 1, 0, 0, $arr_content, "", "", $arr_content[arrPath], 0) ?>
                </div>
                <div id="submenu">
                    <?  
                        /* 
                            HER KUNNE BYGGES EN SUB-MENU MED:
                            newBuildFrontendMenu(1, 1, $arr_content["toplevel_id"], 0, $arr_content, "", "", $arr_content[arrPath], 0)
                        */
                    ?>
                </div>
            </div>
            <div id="mainwrapper">
                <div id="leftmenu">
                    <?php
                    ?>
                    &nbsp;
                </div>
                <div id="maincontent">
                    <?=buildPageMainContent($arr_content)?>
                    <?=printerFriendly($arr_content, true, true, false)?>
                </div>
            </div>
            <div style="clear:both"></div>
            <div id="buildwith">Powered by Instans CMS. It's Just For Websites.</div>
        </div>
    </body>
</html>
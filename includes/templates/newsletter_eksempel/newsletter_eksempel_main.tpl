<html>
    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
    </head>
    <body bgcolor="#ffffff">
        {literal}
            <style type="text/css"> 
                td#redbar a{
                    color:#333333;
                }
                div.boxtext{padding:10px 15px 10px 15px; font:normal 11px verdana; color:#fff; padding-top:10px; padding-right:15px; padding-bottom:10px;padding-left:15px;font-weight:normal;font-size:11px;font-family:verdana;
                }
                div.boxtext_adr{padding:10px 15px 10px 15px; font:normal 11px verdana; color:#000;padding-top:10px;padding-right:15px;padding-bottom:10px;padding-left:15px;font-weight:normal;font-size:11px;font-family:verdana;
                }
                div.boxtext h1{font:bold 12px verdana; margin:0; margin-bottom:3px;font-weight:bold;font-size:12px;font-family:verdana;
                }
                div.boxtext a{color:#fff; font:normal 11px verdana;font-weight:normal;font-size:11px;font-family:verdana;
                }
                div.boxtext_adr a{color:#000; font:normal 11px verdana;font-weight:normal;font-size:11px;font-family:verdana;
                }
                div#starttext{padding:15px; font:normal 11px/14px verdana; color:#000000;font-weight:normal;font-size:11px;font-family:verdana;
                }
                img.itemimage{border:1px solid #ddd;
                }
                div.itemtext{font:normal 11px verdana; color:#000;font-weight:normal;font-size:11px;font-family:verdana;
                }
                a.linkbar{font:normal 11px verdana; color:#fff;font-weight:normal;font-size:11px;font-family:verdana;
                }
                div.itemtext h1{font:bold 12px verdana; margin:0; margin-bottom:3px;font-weight:bold;font-size:12px;font-family:verdana;
                }
                div.itemtext h1 a{text-decoration:none; color:#000;
                }
                div.itemtext a{color:#000;
                }
                div.itemtext h1 a:hover{text-decoration:underline; color:#ff1919 !important;
                }
                hr.streg{color:#ddd; height:1px; margin:5px 0 5px 0;margin-top:5px;margin-bottom:5px;
                }
                div#footer{font-weight:normal;font-size:11px;font-family:verdana;color:#000;
                }
                div#footer a{color:#000;
                }
                p{
                    margin-top:0;
                }
                a.itemlink{color:#000; font-weight:bold;}            
            </style>
        {/literal}
        <table width="620" cellpadding="10" cellspacing="0" border="0" align="center">
            <tr>
                <td>
                    <p style="font-family:verdana; font-size:11px; text-align:center">Har du problemer med at læse dette nyhedsbrev?<br/><a href="{$newsarchive_url}" style="color:#000">Klik her for at læse nyhedsbrevet på vores hjemmeside</a>
                </td>
            </tr>
        </table>
        <table width="620" bgcolor="#ffffff" cellpadding="10" cellspacing="0" border="0" align="center">
            <tr>
                <td>
                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" bgcolor="#ffffff">
                        <tr>
                            <td colspan="3">
                                {php}
                                    connect_to_db();
                                    $sql = "select SITE_NAME from SITES where SITE_ID='1' limit 1";
                                    $res = mysql_query($sql);
                                    $row = mysql_fetch_assoc($res);
                                    echo $row[SITE_NAME];
                                {/php}
                            </td>            
                        </tr>
                        <tr>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td id="redbar" colspan="3" bgcolor="#eeeeee" align="right">
                                            <table cellpadding="5"><tr><td style="font-family:verdana; font-size:9px; font-weight:normal; color:#333333;"></td></tr></table>
                                        </td>
                                    </tr>
                                        <tr>
                                            <td colspan="3" height="5" style="border-bottom:1px solid #aaa"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" height="5"></td>
                                        </tr>

                                        <tr>
                                            <td valign="top" colspan="3">
                                                <div class="itemtext" style="width:90%; font:normal 11px verdana; color:#000;font-weight:normal;font-size:11px;font-family:verdana;">
                                                    {$newsletter_textabove}
                                                </div>
                                            </td>
                                        </tr>
                                    {if $newsletter_content_index != ""}
                                        <tr>
                                            <td colspan="3" height="5" style="border-bottom:1px solid #aaa"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" height="5"></td>
                                        </tr>
                                        <tr>
                                            <td valign="top" colspan="3">
                                                <h1 style="font:bold 12px verdana; font-weight:bold;font-size:12px;font-family:verdana;">Indholdsfortegnelse</h1>
                                                <div class="itemtext" style="width:90%; font:normal 11px verdana; color:#000;font-weight:normal;font-size:11px;font-family:verdana;">
                                                    {$newsletter_content_index}
                                                    <br/><br/>
                                                </div>
                                            </td>
                                        </tr>
                                    {/if}
                                    <tr>
                                        <td colspan="3" height="5" style="border-bottom:1px solid #aaa"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" height="5"></td>
                                    </tr>
                                    {$content_rows}
                                    <tr>
                                        <td valign="top" colspan="3">
                                            <div class="itemtext" style="width:90%; font:normal 11px verdana; color:#000;font-weight:normal;font-size:11px;font-family:verdana;">
                                                {$newsletter_textbelow}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td colspan="3" height="5" style="border-bottom:1px solid #aaa"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" height="5"></td>
                                    </tr>
                                    <tr>
                                        <td style="font-family:verdana; font-size:11px;">
                                            {$user_form}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>    
    </body>
</html>
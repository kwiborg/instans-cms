{section name=item loop=$newsletter_items}
    {if $newsletter_items[item].IMAGEPOS == "NONE"}
                        <tr>
                            <td valign="top" colspan="3">
                                <div class="itemtext" style="font:normal 11px verdana; color:#000;font-weight:normal;font-size:11px;font-family:verdana;">
                                    <h1 style="font:bold 12px verdana; font-weight:bold;font-size:12px;font-family:verdana;">{$newsletter_items[item].HEADING}</h1>
                                    {$newsletter_items[item].CONTENT}
                                    {if $newsletter_items[item].LINKMODE != "nolink"}
                                        <p><a class="itemlink" style="color:#000; font-weight:bold;" href="{$newsletter_items[item].LINKURL}">Læs mere - klik her</a></p>
                                    {/if}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" height="5" style="border-bottom:1px solid #aaa"></td>
                        </tr>
                        <tr>
                            <td colspan="3" height="5"></td>
                        </tr>
    {elseif $newsletter_items[item].IMAGEPOS == "LEFT"}
                        <tr>
                            <td valign="top" width="62"><img width="60" style="border:1px solid #ddd;width:60px;" src="{$newsletter_items[item].IMAGEURL}" border="0" alt="Item image" class="itemimage" /></td>
                            <td valign="top" width="10"></td>
                            <td valign="top">
                                <div class="itemtext" style="font:normal 11px verdana; color:#000;font-weight:normal;font-size:11px;font-family:verdana; padding-right:10px;">
                                    <h1 style="font:bold 12px verdana; margin:0; margin-bottom:3px;font-weight:bold;font-size:12px;font-family:verdana;">
                                        {if $newsletter_items[item].LINKMODE != "nolink"}
                                            <a class="itemlink" style="color:#000; font-weight:bold; text-decoration:none" href="{$newsletter_items[item].LINKURL}">
                                        {/if}
                                        {$newsletter_items[item].HEADING}
                                        {if $newsletter_items[item].LINKMODE != "nolink"}
                                            </a>
                                        {/if}
                                    </h1>
                                    {$newsletter_items[item].CONTENT}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3"><hr style="color:#ddd; height:1px; margin:5px 0 5px 0;margin-top:5px;margin-bottom:5px;" class="streg" /></td>
                        </tr>                        
    {elseif $newsletter_items[item].IMAGEPOS == "RIGHT"}
                        <tr>
                            <td valign="top">
                                <div class="itemtext" style="font:normal 11px verdana; color:#000;font-weight:normal;font-size:11px;font-family:verdana;">
                                    <h1 style="font:bold 12px verdana; margin:0; margin-bottom:3px;font-weight:bold;font-size:12px;font-family:verdana;">{$newsletter_items[item].HEADING}</h1>
                                    {$newsletter_items[item].CONTENT}
                                    {if $newsletter_items[item].LINKMODE != "nolink"}
                                        <p><a class="itemlink" style="color:#000; font-weight:bold;" href="{$newsletter_items[item].LINKURL}">Læs mere - klik her</a></p>
                                    {/if}
                                </div>
                            </td>
                            <td valign="top" width="10"></td>
                            <td valign="top" width="62"><img width="60" style="border:1px solid #ddd;width:60px;" src="{$newsletter_items[item].IMAGEURL}" border="0" alt="Item image" class="itemimage" /></td>
                        </tr>
                        <tr>
                            <td colspan="3"><hr style="color:#ddd; height:1px; margin:5px 0 5px 0;margin-top:5px;margin-bottom:5px;" class="streg" /></td>
                        </tr>                        
    {/if}
{/section}
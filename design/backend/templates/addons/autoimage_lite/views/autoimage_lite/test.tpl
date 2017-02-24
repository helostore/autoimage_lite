<style type="text/css" xmlns="http://www.w3.org/1999/html">
    html {
        overflow: hidden;
    }

    #header,
    #actions_panel {
        display: none;
    }
    .admin-content-wrap .content .content-wrap {
        padding-top: 0;
    }
    #content_autoimage_lite_test {
        background: #fff none repeat scroll 0 0;
        height: 100%;
        left: 0;
        overflow: auto;
        position: fixed;
        width: 100%;
    }
    .hs-ai-result {
    }
    .hs-ai-result-file {
    }
    .hs-ai-result-file img {
        max-width: none;
    }

</style>
{capture name="mainbox"}
    {if !empty($methods)}
        <p style="padding: 15px; 15px;">The sizes used in this preview are {$width}x{$height|default:'-'} pixels as defined in the <a target="_blank" href="{"settings.manage?section_id=Thumbnails"|fn_url}">products list thumbnail settings</a>.
            Verify which method renders the best results for your photos and enabled it in the <a href="{"addons.update?addon=autoimage_lite"}">AutoImage Lite settings</a>.
        Note: don't forget to scroll if there are many images.
        </p>
        <p style="padding: 0 15px 10px;">
            <a class="btn" href="{"autoimage_lite.test?target=stock"|fn_url}">Preview stock photos</a>
            <a class="btn" href="{"autoimage_lite.test?target=products"|fn_url}">Preview your products photos</a>
            {if $referrer}<a class="btn" href="{$referrer}">Exit preview</a>{/if}
        </p>
        <table width="100%" class="table table-middle">
            <thead class="">
                <tr>
                    {foreach from=$methods item="method"}
                        <th>{$method.label}</th>
                    {/foreach}
                </tr>
            </thead>
            {if !empty($results)}
                <tbody>
                    {foreach from=$results item="files"}
                    <tr class="hs-ai-result">
                        {foreach from=$files key="key" item="result"}
                            <td class="hs-ai-result-file">
                                {$result.label}<br>
                                {$atts = ''}
                                {if $result.success}
                                    {if $key == 'original'}
                                        {$atts = 'style="max-width: 600px;"'}
                                    {/if}
                                    <img {$atts nofilter} src="{$result.url}" />
                                {else}
                                    fail
                                {/if}
                            </td>
                        {/foreach}
                    </tr>
                    {/foreach}
                </tbody>
            {else}
                <p class="no-items">{__("no_data")}</p>
            {/if}

        </table>
    {else}
        No methods supported.
    {/if}

{/capture}


{include file="common/mainbox.tpl" title="AutoImage Lite Test" content=$smarty.capture.mainbox content_id="autoimage_lite_test"}
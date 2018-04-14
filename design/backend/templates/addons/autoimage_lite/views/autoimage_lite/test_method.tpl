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
    .hs-ai-grid {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-items: flex-start;
        align-content: flex-start;
    }
    .hs-ai-item {
        display: inline-block;
        vertical-align: top;
        {*flex: 1 1 {$width}px;*}
        border: 1px solid black;
        margin: 5px;
        flex-grow: 0;
        flex-shrink: 0;
    }
    .hs-ai-item.original img {
        max-width: 321px;
    }
    .hs-ai-item.original {
        overflow: hidden;
    }

</style>
{capture name="mainbox"}
    {if !empty($results)}
        <p style="padding: 15px; 15px;">The sizes used in this preview are {$width}x{$height|default:'-'} pixels as defined in the <a target="_blank" href="{"settings.manage?section_id=Thumbnails"|fn_url}">products list thumbnail settings</a>.
            Verify which method renders the best results for your photos and enabled it in the <a href="{"addons.update?addon=autoimage_lite"|fn_url}">AutoImage Lite settings</a>.
        Note: don't forget to scroll if there are many images.
        </p>
        <p style="padding: 0 15px 10px;">
            <a class="btn" href="{"autoimage_lite.test_method?target=stock&method=`$methodSlug`"|fn_url}">Preview stock photos</a>
            <a class="btn" href="{"autoimage_lite.test_method?target=products&method=`$methodSlug`"|fn_url}">Preview your products photos</a>
            {if $referrer}<a class="btn" href="{$referrer}">Exit preview</a>{/if}
        </p>
        <p>Current method: {$methodSlug}</p>

        {foreach from=$methods key="slug" item="method"}
            <a href="{"autoimage_lite.test_method?target=$target&method=`$slug`"|fn_url}">{$method.label}</a> |
        {/foreach}



        <div class="hs-ai-grid">
            {foreach from=$results key="key" item="result"}
                <div class="hs-ai-item {$methodSlug}">
                    {$atts = ''}
                    {if $result.success}
                        {if $key == 'original'}
                            {$atts = 'style="max-width: 600px;"'}
                        {/if}
                        <img title="{$result.label}" {$atts nofilter} src="{$result.url}" />
                    {else}
                        fail
                    {/if}
                </div>
            {/foreach}
        </div>

    {else}
        No images found ...or no results generated (all methods failed).
    {/if}

{/capture}


{include file="common/mainbox.tpl" title="AutoImage Lite Test" content=$smarty.capture.mainbox content_id="autoimage_lite_test"}

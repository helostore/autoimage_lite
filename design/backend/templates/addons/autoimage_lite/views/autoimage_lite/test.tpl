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
            Verify which method renders the best results for your photos and enabled it in the <a href="{"addons.update?addon=autoimage_lite"|fn_url}">AutoImage Lite settings</a>.
        Note: don't forget to scroll if there are many images.
        </p>
        <p style="padding: 0 15px 10px;">
            <a class="btn" href="{"autoimage_lite.test?target=stock"|fn_url}">Preview stock photos</a>
            <a class="btn" href="{"autoimage_lite.test?target=products"|fn_url}">Preview your products photos</a>
            {if $referrer}<a class="btn" href="{$referrer}">Exit preview</a>{/if}
        </p>
        {assign var="c_url" value=$config.current_url|fn_query_remove:"page":"limit"}
        <form action="{$c_url|fn_url}" method="post" class="form-horizontal form-edit cm-ajax" name="preview_form">
            {include file="common/pagination.tpl" save_current_page=true save_current_url=true}
            <input type="hidden" id="hsai_page" name="page" value="2" />

            <table width="100%" class="table table-middle">
                <thead class="cm-sticky-scroll">
                    <tr>
                        {foreach from=$methods item="method"}
                            <th>{$method.label}</th>
                        {/foreach}
                    </tr>
                </thead>
                <tbody>
                    {include file="addons/autoimage_lite/views/autoimage_lite/components/test_list.tpl" results=$results}
                </tbody>
            </table>

            {include file="common/pagination.tpl"}

            {*{if !empty($smarty.request.target) && $smarty.request.target == "products"}*}
                <p style="text-align: center;padding: 100px;border: 4px dotted #ddd;margin: 50px;">
                    <button type="submit"
                            style="width: 20vw;height: 10vh;display: inline-block;font-size: 2vw;"
                            class="btn btn-primary cm-ajax"
                            href="{$c_url|fn_url}"
                    >Load more</button>
                </p>
            {*{/if}*}
        </form>
    {else}
        No methods supported.
    {/if}

{/capture}

{include file="common/mainbox.tpl" title="AutoImage Lite Test" content=$smarty.capture.mainbox content_id="autoimage_lite_test"}
<script type="text/javascript">
    (function (_, $) {
        $.ceEvent('on', 'ce.formajaxpost_preview_form', function (response, params) {
            if (typeof(response) !== "undefined") {
                if (typeof(response.text) !== "undefined") {
                    if (typeof(params) !== "undefined" && typeof(params.form) !== "undefined") {
                        params.form.find('table').append(response.text);
                        var $page = $('#hsai_page');
                        if ($page) {
                            var pageNumber = parseInt($page.val());
                            $page.val(pageNumber + 1);
                        }

                    }
                }
            }
        });
    }(Tygh, Tygh.$));
</script>

{if !empty($results)}
    {foreach from=$results item="files"}
        <tr class="hs-ai-result">
            {foreach from=$files key="key" item="result"}
                <td class="hs-ai-result-file">
                    {$result.label}<br>
                    {$atts = ''}
                    {if $result.success}
                        {if $key == 'original'}
                            {$atts = 'style="max-width: 300px;"'}
                        {/if}
                        <img {$atts nofilter} src="{$result.url}" />
                    {else}
                        fail
                    {/if}
                </td>
            {/foreach}
        </tr>
    {/foreach}
{/if}

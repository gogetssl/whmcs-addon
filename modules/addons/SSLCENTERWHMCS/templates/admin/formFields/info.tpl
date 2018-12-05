<div class="info col-sm-{$colWidth}"  {if $id} id="{$id}" {elseif $addIDs}id="{$addIDs}_{$name}"{/if} style='margin-left:15px;'>
    {foreach from=$values item=value}
        {if $value != ''}<p>{if $h}<{$h}>{/if}{$value}{if $h}</{$h}>{/if}</p>{/if}
    {/foreach}
</div>

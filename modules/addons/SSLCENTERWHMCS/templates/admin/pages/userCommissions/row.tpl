<tr>
    <td><a target="_blank" href="clientssummary.php?userid={$client.id}">
            #{$client.id} {$client.name}
        </a>
    </td>
    <td><a target="_blank" href="configproducts.php?action=edit&id={$product.id}">
            #{$product.id} {$product.name}
        </a>
    </td>
    <td>{$commission}</td>
    <td>
        {assign var="atLeastOnePrice" value="0"}
        {foreach from=$pricings item=pricing}
            {if $pricing->monthly !== '-1.00'}
                {assign var="atLeastOnePrice" value="1"}
                <span><strong>{$pricing->code}</strong></span>
                <p style="margin-bottom: 0 !important;">{$MGLANG->T('table', 'basePrice')}{$pricing->monthly}</p>
                <p>{$MGLANG->T('table', 'priceWithCommission')}{$pricing->commission_monthly}</p>  
            {/if}           
        {/foreach}
        {if $atLeastOnePrice == "0"}
            <span>-</span>
        {/if}
    </td>
    <td>
        {assign var="atLeastOnePrice" value="0"}
        {foreach from=$pricings item=pricing}
            {if $pricing->quarterly !== '-1.00'}
                {assign var="atLeastOnePrice" value="1"}
                <span><strong>{$pricing->code}</strong></span>
                <p style="margin-bottom: 0 !important;">{$MGLANG->T('table', 'basePrice')}{$pricing->quarterly}</p>
                <p>{$MGLANG->T('table', 'priceWithCommission')}{$pricing->commission_quarterly}</p>
            {/if} 

        {/foreach}
        {if $atLeastOnePrice == "0"}
            <span>-</span>
        {/if}
    </td>
    <td>
        {assign var="atLeastOnePrice" value="0"}
        {foreach from=$pricings item=pricing}
            {if $pricing->semiannually !== '-1.00'}
                {assign var="atLeastOnePrice" value="1"}
                <span><strong>{$pricing->code}</strong></span>
                <p style="margin-bottom: 0 !important;">{$MGLANG->T('table', 'basePrice')}{$pricing->semiannually}</p>
                <p>{$MGLANG->T('table', 'priceWithCommission')}{$pricing->commission_semiannually}</p>
            {/if} 
        {/foreach}
        {if $atLeastOnePrice == "0"}
            <span>-</span>
        {/if}
    </td>
    <td>
        {assign var="atLeastOnePrice" value="0"}
        {foreach from=$pricings item=pricing}
            {if $pricing->annually !== '-1.00'}
                {assign var="atLeastOnePrice" value="1"}
                <span><strong>{$pricing->code}</strong></span>
                <p style="margin-bottom: 0 !important;">{$MGLANG->T('table', 'basePrice')}{$pricing->annually}</p>
                <p>{$MGLANG->T('table', 'priceWithCommission')}{$pricing->commission_annually}</p>       
            {/if} 
        {/foreach}
        {if $atLeastOnePrice == "0"}
            <span>-</span>
        {/if}
    </td>
    <td>
        {assign var="atLeastOnePrice" value="0"}
        {foreach from=$pricings item=pricing}
            {if $pricing->biennially !== '-1.00'}
                {assign var="atLeastOnePrice" value="1"}
                <span><strong>{$pricing->code}</strong></span>
                <p style="margin-bottom: 0 !important;">{$MGLANG->T('table', 'basePrice')}{$pricing->biennially}</p>
                <p>{$MGLANG->T('table', 'priceWithCommission')}{$pricing->commission_biennially}</p> 
            {/if}  
        {/foreach}
        {if $atLeastOnePrice == "0"}
            <span>-</span>
        {/if}
    </td>
    <td>
        {assign var="atLeastOnePrice" value="0"}
        {foreach from=$pricings item=pricing}
            {if $pricing->triennially !== '-1.00'}
                {assign var="atLeastOnePrice" value="1"}
                <span><strong>{$pricing->code}</strong></span>
                <p style="margin-bottom: 0 !important;">{$MGLANG->T('table', 'basePrice')}{$pricing->triennially}</p>
                <p>{$MGLANG->T('table', 'priceWithCommission')}{$pricing->commission_triennially}</p> 
            {/if} 
        {/foreach}
        {if $atLeastOnePrice == "0"}
            <span>-</span>
        {/if}
    </td> 
    <td>   
    <td>
        <div align="center">
            <button data-toggle="tooltip" title="{$MGLANG->T('editItem')}" type="button"  data-id="{$rule_id}" class="btn btn-primary btn-inverse editItem"><i class="fa fa-pencil" aria-hidden="true"></i></button>
            <button data-toggle="tooltip" title="{$MGLANG->T('deleteItem')}" type="button" data-id="{$rule_id}" class="btn btn-danger  btn-inverse deleteItem"><i class="fa fa-trash" aria-hidden="true"></i></button>
        </div>
    </td>
</tr>

<tr>
    <td>{$id}</td>
    <td><a href="clientssummary.php?userid={$client_id}">{$client_name}</a></td>
    <td><a href="clientsservices.php?id={$service_id}">{$service_name}</a></td>
    <td><a href="orders.php?action=view&id={$order_id}">{$remote_id}</a></td>
    <td>{$verification_method}</td>
    <td>
        {if $status == 'Pending Verification'}
            <span class="label label-warning">{$status}</span>
            <button data-id="{$id}" class="setVerified btn btn-success btn-sm">{$MGLANG->T('table', 'set as verified')}</button>
        {elseif $status == 'Pending Installation'}
            <span class="label label-primary">{$status}</span>
            <button data-id="{$id}" class="setInstalled btn btn-success btn-sm">{$MGLANG->T('table', 'set as installed')}</button>
        {elseif $status == 'Success'}
            <span class="label label-success">{$status}</span>
        {else}
            <span class="label label-danger">{$status}</span>
        {/if}
    </td>
    <td>{$date}</td>
</tr>

<tr>
    <td>{$id}</td>
    <td><a href="clientssummary.php?userid={$client_id}">{$client_name}</a></td>
    <td><a href="clientsservices.php?id={$service_id}">{$service_name}</a></td>
    <td>
        {if $type == 'INFO'}
            <span class="label label-primary">{$type}</span>
        {elseif $type == 'ERROR'}
            <span class="label label-danger">{$type}</span>
        {elseif $type == 'SUCCESS'}
            <span class="label label-success">{$type}</span>
        {/if}
    </td>
    <td>{$msg}</td>
    <td>{$date}</td>
    <td>
        <div>
            <button data-toggle="tooltip" title="{$MGLANG->T('deleteItem')}" type="button" data-id="{$id}" class="btn btn-danger  btn-inverse deleteItem"><i class="fa fa-trash" aria-hidden="true"></i></button>
        </div>
    </td>
</tr>

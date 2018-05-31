<h3>{$MGLANG->T('reissueTwoTitle')}</h3>
{$MGLANG->T('reissueTwoSubTitle')}

{assign var=val value=0}

<div class="row">
    <div class="col-sm-12 col-sm-offset-1">
        <form method="POST" action="{$smarty.server.REQUEST_URI}" class="form-horizontal">
            <input type="hidden" name="stepTwoForm" value="tak">
            <input type="hidden" name="webservertype" value="{$smarty.post.webservertype}">
            <input type="hidden" name="csr" value="{$smarty.post.csr}">
            <input type="hidden" name="sans_domains" value="{$smarty.post.sans_domains}">
            {foreach from=$approvalEmails key=domain item=domainApprovalEmail}
                <h3>{$domain}</h3>
                <div class="form-group">
                    {assign var=firstRadio value=0}
                    {foreach from=$domainApprovalEmail item=approvalEmail}
                        <div class="radio">
                            {if $val === 0}
                                <label><input {if $firstRadio === 0}checked{/if} type="radio" name="approveremail" value="{$approvalEmail}">{$approvalEmail}</label>
                                {else}
                                <label><input {if $firstRadio === 0}checked{/if} type="radio" name="approveremails[{$domain}]" value="{$approvalEmail}">{$approvalEmail}</label>
                                {/if}
                        </div>
                        {assign var=firstRadio value=$firstRadio+1}
                    {/foreach}
                </div>
                {assign var=val value=$val+1}
            {/foreach}
            <p class="text-center">
                <input type="submit" value="{$MGLANG->T('reissueTwoContinue')}" class="btn btn-primary">
            </p>
        </form>
    </div>
</div>
{if $error}
    <div class="alert alert-danger text-center">
        {$error}
    </div>
{/if}

<h3>{$MGLANG->T('reissueOneTitle')}</h3>

<div class="row">
    <div class="col-sm-12">
        <form method="POST" action="{$smarty.server.REQUEST_URI}" class="form-horizontal">
            <input type="hidden" name="stepOneForm" value="tak">
            <input type="hidden" name="reissueServiceID" value="{$serviceID}" />
            <input class="form-control" name="privateKey" value="{if $smarty.post.privateKey}{$smarty.post.privateKey}{else}{$privKey}{/if}" type="hidden">
            <input type="hidden" name="webservertype" value="18">
            <div class="form-group">
                <label class="col-sm-2 control-label">{$MGLANG->T('reissueOneCsr')}</label>
                <div class="col-sm-10">
                    {if $smarty.post.csr}
                        <textarea name="csr" class="form-control" rows="8">{$smarty.post.csr}</textarea>
                    {else if $csrreissue}
                        <textarea name="csr" class="form-control" rows="8">{$csrreissue}</textarea>
                             {else}
                        <textarea name="csr" class="form-control" rows="8">-----BEGIN CERTIFICATE REQUEST-----

-----END CERTIFICATE REQUEST-----</textarea>
                        {/if}
                </div>
            </div>

            {if $sansLimit || $sansLimitWildCard}
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2">
                        <div class="alert alert-info" style="margin-bottom:0;">
                            {$MGLANG->T('reissueSansHint')|default:'One domain per line. The certificate is reissued with every name listed here: keep the current names and add the new ones.'}
                            {if $sansLimit > $sansCurrentCount}
                                <br><strong>{$MGLANG->T('reissueSansFree')|default:'Unused SAN slots:'} {$sansLimit-$sansCurrentCount}</strong>
                            {/if}
                        </div>
                    </div>
                </div>
            {/if}

            {if $sansLimit}
                <div class="form-group">
                    <label class="col-sm-2 control-label">{$MGLANG->T('reissueOneSanDomains')} ({$sansLimit})</label>
                    <div class="col-sm-10">
                        <textarea name="sans_domains" rows="4" class="form-control">{if !$smarty.post.sans_domains}{$sandetails.sans_domains}{else}{$smarty.post.sans_domains}{/if}</textarea>
                    </div>
                </div>
            {/if}
            
            {if $sansLimitWildCard}
                <div class="form-group">
                    <label class="col-sm-2 control-label">{$MGLANG->T('reissueOneSanDomainsWildcard')} ({$sansLimitWildCard})</label>
                    <div class="col-sm-10">
                        <textarea name="sans_domains_wildcard" rows="4" class="form-control">{if !$smarty.post.sans_domains_wildcard}{$sandetails.wildcard_san}{else}{$smarty.post.sans_domains_wildcard}{/if}</textarea>
                    </div>
                </div>
            {/if}
            {if $removedSans}
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2">
                        <div class="checkbox alert alert-warning" style="margin:0;padding-left:30px;">
                            <label>
                                <input type="checkbox" name="confirm_remove_sans" value="1">
                                {$MGLANG->T('reissueSansRemoveConfirm')|default:'Remove these names from the certificate:'} <strong>{', '|implode:$removedSans|escape}</strong>
                            </label>
                        </div>
                    </div>
                </div>
            {/if}
            {$generateCsrIntegrationCode}
            <br />
            <p class="text-center">
                <input type="submit" value="Click to Continue" class="btn btn-primary">
            </p>
        </form>
    </div>
</div>

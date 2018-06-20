<style>
    .bootstrap-select:not([class*="col-"]):not([class*="form-control"]):not(.input-group-btn)
    {
        width: 100%;
    }
</style>
{if $enableLabel}
    <label for="{$formName}_{$name}" class="col-sm-{$labelcolWidth} control-label">{$MGLANG->T('label')}</label>
{/if}
<div class="col-sm-{$colWidth}">
    <select class="selectpicker" data-size="10" data-live-search="true" {if $id} id="{$id}" {elseif $addIDs}id="{$addIDs}_{$name}"{/if} name="{$nameAttr}{if $multiple}[]{/if}" {if $readonly||$disabled}disabled="disabled"{/if} {foreach from=$dataAttr key=dataKey item=dataValue}data-{$dataKey}="{$dataValue}"{/foreach}>
        {foreach from=$options item=option key=opValue}
            <option value="{$opValue}" {if $multiple}{if in_array($opValue,$value)}selected{/if}{else}{if $opValue==$value}selected{/if}{/if}>
                    {$option}
            </option>
        {/foreach}
    </select>
    {if $readonly}
        <input type="hidden" name="{$nameAttr}" value="{$value}" />
    {/if}
    {if $enableDescription }
      <span class="help-block">{$MGLANG->T('description')}</span>
    {/if}
    <span class="help-block error-block"{if !$error}style="display:none;"{/if}>{$error}</span>
</div>

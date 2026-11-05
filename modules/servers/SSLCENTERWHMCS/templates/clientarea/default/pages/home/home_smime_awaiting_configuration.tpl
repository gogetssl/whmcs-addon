<script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>

{if $allOk === true}
    <table id="mainTable" class="table table-bordered">
        <colgroup>
            <col style="width: 25%"/>
            <col style="width: 75%"/>
        </colgroup>
        <tbody>
            <tr>
                <td class="text-left">{$MGLANG->T('smimeProductName')}</td>
                <td class="text-left">{$productName|escape}</td>
            </tr>
            <tr>
                <td class="text-left">{$MGLANG->T('configurationStatus')}</td>
                <td class="text-left">{$MGLANG->T('Awaiting Configuration')}</td>
            </tr>
            <tr>
                <td class="text-left">{$MGLANG->T('Actions')}</td>
                <td class="text-left">
                    <a class="btn btn-primary" href="{$configurationURL|escape:'htmlall'}">{$MGLANG->T('configureNow')}</a>
                </td>
            </tr>
        </tbody>
    </table>
{/if}

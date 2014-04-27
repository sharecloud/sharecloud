{extends 'handler/handler.tpl'}
{block name=handler}
<div class="office">
    <div class="info">
        {'officeInfo'|@lang}
    </div>
    
    {if !empty($error)}
        <div class="error">
            {$error}
        </div>
    {else}
        <a href="{$link}" target="_blank">{'showOfficePreview'|@lang}</a>
    {/if}
</div>
{/block}
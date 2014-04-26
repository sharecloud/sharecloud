{extends 'main.tpl'}
{block name=content}

<p style="font-weight:bold;">Oups, something went wrong here.</p>
<p><b>Error:</b> {$error}</p>

<div class="buttons">
	<label class="button">
        <a href="{$url}">Try again</a>
    </label>
</div>
{/block}
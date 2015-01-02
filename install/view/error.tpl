{extends 'main.tpl'}
{block name=content}

<p style="font-weight:bold;">Oups, something went wrong here.</p>
<p><b>Error:</b> {$error}</p>

<div class="buttons">
	<a href="{$url}" class="btn btn-default" role="button"><span class="fa fa-repeat"> </span> Try again</a>
</div>
{/block}
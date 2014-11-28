{extends 'main.tpl'}
{block name=content}
<p><b>Caution:</b> All MySQL tables will be deleted when you proceed.</p>

<div class="buttons">
	<a href="index.php?action=tables&#38;confirm=yes" class="btn btn-default" role="button">Proceed <span class="fa fa-chevron-right"> </span></a>
</div>
{/block}
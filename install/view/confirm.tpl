{extends 'main.tpl'}
{block name=content}
<p><b>Caution:</b> All MySQL tables will be deleted when you proceed.</p>

<div class="buttons">
	<label class="button">
        <a href="index.php?action=tables&#38;confirm=yes">Proceed</a>
    </label>
</div>
{/block}
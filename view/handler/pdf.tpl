{extends 'handler/handler.tpl'}

{block name=handler}
<iframe class="pdf" src="{Router->build p1='DownloadController' p2='embed' p3=$file}"></iframe>
{/block}
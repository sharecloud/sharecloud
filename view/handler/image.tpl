{extends 'handler/handler.tpl'}

{block name=specific}
<div class="form-group clearfix">
    <label class="col-sm-4 control-label">{'ImageSize'|@lang}:</label>
    <div class="col-sm-8 no-padding">
        <p class="form-control-static">{$specific.imagesize}</p>
    </div>
</div>

{if !empty($specific.format)}
<div class="form-group clearfix">
    <label class="col-sm-4 control-label">{'Format'|@lang}:</label>
    <div class="col-sm-8 no-padding">
        <p class="form-control-static">{$specific.format}</p>
    </div>
</div>
{/if}
{/block}

{block name=handler}
<div class="image">
	{if $file->ext eq 'svg'}
		<img alt="" src="{Router->build p1='DownloadController' p2='embed' p3=$file}" />
	{else}
		<img alt="" src="{Router->build p1='DownloadController' p2='resize' p3=$file}" />
	{/if}
</div>
{/block}
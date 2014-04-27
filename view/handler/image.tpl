{extends 'handler/handler.tpl'}

{block name=specific}
<div class="form-group clearfix">
    <label class="col-sm-4 control-label">{'ImageSize'|@lang}:</label>
    <div class="col-sm-8">
        <p class="form-control-static">{$specific.imagesize}</p>
    </div>
</div>

{if !empty($specific.format)}
<div class="form-group clearfix">
    <label class="col-sm-4 control-label">{'Format'|@lang}:</label>
    <div class="col-sm-8">
        <p class="form-control-static">{$specific.format}</p>
    </div>
</div>
{/if}
{/block}

{block name=handler}
<div class="image">
	<img alt="" src="{Router->build p1='DownloadController' p2='resize' p3=$file}" />
</div>
{/block}
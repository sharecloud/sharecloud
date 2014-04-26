{extends 'handler/handler.tpl'}
{block name=specific}
{foreach $specific as $info}
<div class="form-group clearfix">
    <label class="col-sm-4 control-label">{$info@key|@lang}:</label>
    <div class="col-sm-8">
        <p class="form-control-static">{$info}</p>
    </div>
</div>
{/foreach}
{/block}
{block name=handler}
<div class="media">
    <div style="margin: 0 auto;width:{$width}px;">
        {if $mediatype eq 'video'}
    	<{$mediatype} {$options} >
    	   <source src="{Router->build p1='DownloadController' p2='embed' p3=$file}" type="{$mime}">
    	</{$mediatype}>
    	{else}
    	<{$mediatype} {$options} src="{Router->build p1='DownloadController' p2='embed' p3=$file}"></{$mediatype}>
    	{/if}
    	
	</div>
</div>
{/block}
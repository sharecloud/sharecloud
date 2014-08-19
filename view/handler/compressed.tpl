{extends 'handler/handler.tpl'}
{block name=specific}
{foreach $specific as $info}
<div class="form-group clearfix">
    <label class="col-sm-4 control-label">{$info@key|@lang}:</label>
    <div class="col-sm-8">
        <p class="form-control-static">{$info|@lang}</p>
    </div>
</div>
{/foreach}
{/block}

{block name=handler}
<h3>{'FilesContained'|@lang}</h3>
<table class="table table-striped table-hover">
	<thead>
    	<tr>
        	<th>{'Name'}</th>
            <th>{'UncompressedSize'|@lang}</th>
            <th>{'CompressedSize'|@lang}</th>
        </tr>
     </thead>
     <tbody>
{foreach $files as $f}
	<tr>
    	<td>{if $f.Encrypted eq true}<span class="glyphicon glyphicon-lock"> </span> {/if}{$f@key}</td>
    	<td>{$f.UncompressedSize}</td>
    	<td>{$f.CompressedSize}</td></tr>
{/foreach}
	</tbody>
</table>
{/block}
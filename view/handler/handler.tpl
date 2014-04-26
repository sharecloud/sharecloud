{extends 'main.tpl'}
{block name=content}

<nav class="navbar navbar-default filenav">
	<div class="container-fluid">
    	<a href="{Router->build p1='DownloadController' p2='force' p3=$file}" class="btn btn-default btn-sm navbar-btn" role="button" data-noajax="true">
            <span class="glyphicon glyphicon-save"> </span> {'LinkDownload'|@lang}
        </a>
        <a href="{Router->build p1='DownloadController' p2='raw' p3=$file}" class="btn btn-default btn-sm navbar-btn" role="button" data-noajax="true">
            <span class="glyphicon glyphicon-save"> </span> {'LinkRAW'|@lang}
        </a>   
        {if $LoggedIn}
        <a href="{Router->build p1='BrowserController' p2='permissions' p3=$file}" class="btn btn-default btn-sm navbar-btn button-filepermissions" role="button"  data-noajax="true">
            <span class="glyphicon glyphicon-share"> </span> {'PermissionSetting'|@lang}
        </a>	
    	{/if}
    </div>
</nav>

<div class="information">
	{if $LoggedIn}
    <div class="share-link" style="{if $file->permission->level lte 2}display:block;{else}display:none;{/if}">
        <h3>{'ShareLink'|@lang}</h3>
        <input class="form-control" style="width: 99%;" type="text" value="{Router->build p1='DownloadController' p2='show' p3=$file}" readonly="readonly" />
    </div>
    {/if}

	<div class="row">
        <div class="col-md-6">
            <h3>{'GeneralInfo'|@lang}</h3>
            
            <div class="form-group clearfix">
                <label class="col-sm-4 control-label">{'Size'|@lang}:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">{$file->size|@filesize}</p>
                </div>
            </div>
                
            <div class="form-group clearfix">
                <label class="col-sm-4 control-label">{'Mime'|@lang}:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">{$file->mime}</p>
                </div>
            </div>
            
            <div class="form-group clearfix">
                <label class="col-sm-4 control-label">{'NumDownloads'|@lang}:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">{$file->downloads}</p>
                </div>
            </div>
            
            <div class="form-group clearfix">
                <label class="col-sm-4 control-label">{'Date'|@lang}:</label>
                <div class="col-sm-8">
                    <p class="form-control-static">{$file->time|@dateformat}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 hashes">            
            <h3>{'Hashes'|@lang}</h3>
            
            {foreach $file->hashes as $algo => $hash}
            <div class="form-group clearfix">
            	<label class="col-sm-4 control-label">{$algo|@lang}:</label>
                <div class="col-sm-8">
                    <div class="input-group">
                        <input type="text" class="form-control" value="{$hash}" readonly="readonly">
                        
                        <span class="input-group-btn">
                        	<button class="btn btn-default copy" type="button" data-clipboard-text="{$hash}" id="{$algo}">
                            	<span class="glyphicon glyphicon-paperclip"> </span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            {/foreach}
        </div>
        
        {assign var='specific' value=$specific|default:''}
        {if $specific neq ''}
        <div class="col-md-6">
            <h3>{'SpecificInfo'|@lang}</h3>
            {block name=specific}
            {/block}
        </div>
        {/if}
    </div>
    
</div>

<div class="handler">
    {block name=handler}
    {/block}
</div>

{if $LoggedIn}
<div class="modal fade" id="modal-permissions">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{'PermissionSetting'|@lang}</h4>
            </div>
            <div class="modal-body">
            	<form role="form">
                    {$permission}
                    
                    <input id="filealias" type="hidden" value="{$file->alias}" />
                    
                    <div id="password" class="form-group" style="{if $file->permission->level eq 2}display:block{else}display:none;{/if}">
                        <label for="input-password" class="control-label">{'Password'|@lang}:</label>
                        <input type="password" class="form-control" id="input-password" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{'Cancel'|@lang}</button>
                <button type="button" class="btn btn-primary button-confirm" data-loading-text="{'PleaseWait'|@lang}">{'Save'|@lang}</button>
            </div>
        </div>
    </div>
</div>
{/if}
{/block}
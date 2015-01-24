{extends 'main.tpl'}
{block name=content}

<div class="browser" data-id="{$currentFolder->id}">
	<div class="row header clearfix">
    	<div class="column">
    		<ol class="breadcrumb">
				<li data-folder-id=''><a href="{Router->build p1='BrowserController' p2='index'}">{'Start'|@lang}</a></li>
			{foreach $breadcrumb as $f}
				<li data-folder-id='{$f->id}'><a href="{Router->build p1='BrowserController' p2='show' p3=$f}">{$f->name}</a></li>
			{/foreach}
			</ol>
    	</div>
        <div class="column size">{'Size'|@lang}</div>
        <div class="column num-downloads">{'NumDownloads'|@lang}</div>
    </div>
    
    <div class="row create-folder clearfix">
    	<div class="column input">
        	<input type="text" name="foldername" placeholder="{'FolderName'|@lang}" class="form-control input-sm" value="" id="create-folder-input" data-parent="{$currentFolder->id}" />
        </div>
    </div>
    
{foreach $folders as $folder}
	<div class="row folder clearfix" data-id="{$folder->id}">
    	<div class="column filename">
        	<i class="fa fa-folder-o"></i>
            <a class="folder" href="{Router->build p1='BrowserController' p2='show' p3=$folder}">{$folder->name}</a>
        </div>
        <div class="column size">{$folder->getContentSize()|@filesize}</div>
        <div class="column num-downloads">
            <a href="{Router->build p1='DownloadController' p2='folder' p3=$folder}" title="{'DownloadAsZip'|@lang}" data-noajax="true" data-placement="left" class="btn btn-default btn-sm">
                <i class="fa fa-cloud-download"></i>
            </a>
        </div>

    </div>
{/foreach}  

{foreach $files as $file}
	<div class="row file clearfix" data-alias="{$file->alias}" data-id="{$file->id}">
    	<div class="column filename">
        	<i class="fa {if isset($fafileicons[$file->ext])}{$fafileicons[$file->ext]}{else}fa-file-o{/if}"></i>
            {assign "splittedFilename" $file->getSplittedFilename()}
            {if is_array($splittedFilename)}
                <a class="file" href="{Router->build p1='DownloadController' p2='show' p3=$file}"><span class="filename">{$splittedFilename.0}</span><span class="ext">.{$splittedFilename.1}</span></a>
            {else}
                <a class="file" href="{Router->build p1='DownloadController' p2='show' p3=$file}"><span class="filename">{$file->filename}<span class="filename"></a>
            {/if}
                

        </div>
        
        <div class="column size">{$file->size|@filesize}</div>
        <div class="column num-downloads">

            <span>
                {$file->downloads}
            </span>
            <span>
                {if $file->permission eq 1}
                    <i data-toggle="tooltip" title="{'Public'|@lang}" class="fa fa-globe"></i>
                {elseif $file->permission eq 2}
                    <i data-toggle="tooltip" title="{'Protected'|@lang}" class="fa fa-lock"></i>
                {/if}
            </span>
            <span>
                <a href="{Router->build p1='DownloadController' p2='force' p3=$file}" data-noajax="true" class="btn btn-default btn-sm">
                    <i class="fa fa-cloud-download"></i>
                </a>
            </span>

        </div>
    </div>
{foreachelse}
	<p class="no-files">{'NoFiles'|@lang}</p>
{/foreach} 
</div>

<nav class="navbar navbar-default filenav">
	<div class="container-fluid">
    	<a href="#" class="btn btn-default btn-sm navbar-btn button-rename disabled">
            <i class="fa fa-lg fa-pencil"></i> {'Rename'|@lang}
        </a>
        
        <a href="#" class="btn btn-default btn-sm navbar-btn button-move disabled">
            <i class="fa fa-lg fa-arrows"></i> {'Move'|@lang}
        </a>
        
        <a href="#" class="btn btn-default btn-sm navbar-btn button-delete disabled">
            <i class="fa fa-lg fa-trash"></i> {'Delete'|@lang}
        </a>
        
        <a href="#" class="btn btn-default btn-sm navbar-btn button-invert-selection">{'InvertSelection'|@lang}</a>
        
        <a href="{Router->build p1='BrowserController' p2='addFolder'}?parent={$currentFolder->id}" class="btn btn-default btn-sm button-create-folder">
            <i class="fa fa-lg fa-plus"></i> {'AddFolder'|@lang}
        </a>
{if $remoteDownloadSetting == true}
        <div class="btn-group">
            <button type="button" class="btn btn-default btn-sm navbar-btn dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-lg fa-cloud-upload"></i> {'FileUpload'|@lang}
            </button>
            
            <ul class="dropdown-menu" role="menu">
                <li><a href="{Router->build p1='UploadController' p2='upload'}?parent={$currentFolder->id}" class="button-upload-file">{'UploadFromComputer'|@lang}</a></li>
                <li><a href="{Router->build p1='UploadController' p2='upload'}?parent={$currentFolder->id}" class="button-remote-download">{'UploadFromURL'|@lang}</a></li>
            </ul>
        </div>
{else}
        <a href="{Router->build p1='UploadController' p2='upload'}?parent={$currentFolder->id}" class="btn btn-default btn-sm navbar-btn button-upload">
            <i class="fa fa-lg fa-cloud-upload"></i> {'FileUpload'|@lang}
        </a>
{/if}
    </div>
</nav>

<div class="cloneable">
	<div class="row folder clearfix">
    	<div class="column filename"></div>
    </div>
    
    <div class="row file clearfix">
    	<div class="column filename"></div>
        <div class="column size"></div>
        <div class="column num-downloads"></div>
    </div>
</div>


<form class="form-rename-template clearfix" style="display:none;" role="form">
	<input type="text" name="input-filename" class="input-filename form-control input-sm" value="" />
    <div class="indicator">{'Save'|@lang}</div>
</form>

<div class="modal fade" id="modal-delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{'Delete'|@lang}</h4>
            </div>
            <div class="modal-body">
            	<p>{'DeleteSelectedFiles'|@lang}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{'Cancel'|@lang}</button>
                <button type="button" class="btn btn-primary button-confirm" data-loading-text="{'PleaseWait'|@lang}">{'Delete'|@lang}</button>
            </div>
        </div>
	</div>
</div>

<div class="modal fade" id="modal-move">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{'Move'|@lang}</h4>
            </div>
            <div class="modal-body">
            	<form role="form">
                	<div class="form-group">
                    	<label for="select-move" class="control-label">{'ChooseFolder'|@lang}:</label>
                        <select class="select-move form-control" id="select-move">
{foreach $AvailableFolders as $id => $folder}
							<option value="{$id}">{$folder}</option>
{/foreach}                        
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{'Cancel'|@lang}</button>
                <button type="button" class="btn btn-primary button-confirm" data-loading-text="{'PleaseWait'|@lang}">{'Move'|@lang}</button>
            </div>
        </div>
	</div>
</div>

{if $remoteDownloadSetting == true}
<div class="modal fade" id="modal-download">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{'UploadFromURL'|@lang}</h4>
            </div>
            
            <div class="modal-body">
                <form role="form">
                	<div class="form-group">
                    	<label for="input-url" class="control-label">{'EnterURL'|@lang}:</label>
                        <input type="text" id="input-url" name="input-url" class="form-control" />
                    </div>
                    
                    <div class="form-group">
                    	<label for="input-filename" class="control-label">{'Name'|@lang}:</label>
                        <input type="text" id="input-filename" name="input-filename" class="form-control" />
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{'Cancel'|@lang}</button>
                <button type="button" class="btn btn-primary button-confirm">{'UploadFromURL'|@lang}</button>
            </div>
        </div>
    </div>
</div>
{/if}

<div style="height:0px; width:0px; overflow:hidden;">
	<input type="file" class="input-file-upload" multiple="multiple" />
</div>
{/block}
{extends "main.tpl"}

{block name=content}
<div class="row">
	<div class="col-sm-6 col-md-3">
    	<div class="thumbnail">
            <div class="caption">
	            <h4>{'Users'|@lang}</h3>
                <span class="counter">{$num_users}</span>
                <p>
                	<a href="{Router->build p1='UsersController' p2='index'}" class="btn btn-primary" role="button">{'AllUsers'|@lang} <i class="fa fa-chevron-right"></i></a>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-md-3">
    	<div class="thumbnail">
            <div class="caption">
	            <h4>{'Files'|@lang}</h3>
                <span class="counter">{$num_files}</span>
                <p class="lead">{'FilesPerUser'|@lang:$filesPerUser}</p>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-md-3">
    	<div class="thumbnail">
            <div class="caption">
	            <h4>{'Quota'|@lang}</h3>
                <span class="counter" title="{'QuotaUsed'|@lang}">{$usedSpace|@filesize}</span>
                <p class="lead">{'QuotaAvailable'|@lang:{$availableSpace|@filesize}}</p>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-md-3">
    	<div class="thumbnail">
            <div class="caption">
	            <h4>{'SharecloudVersion'|@lang}</h3>
                <span class="counter">{$version}</span>
                <p>
                	<a href="#" class="btn btn-primary check-for-updates" role="button" data-loading-text="{'CheckingForUpdates'|@lang}">{'CheckForUpdates'|@lang} <i class="fa fa-chevron-right"></i></a>
                    <a href="https://github.com/sharecloud/sharecloud/blob/master/docs/upgrade/upgrade.md" data-noajax="true" class="btn btn-danger update-system hidden" role="button">{'UpdateAvailable'|@lang} <i class="fa fa-chevron-right"></i></a>
                    
                    <span class="check-for-update-error hidden">{'ErrorWhileCheckingForUpdates'|@lang}</span>
                </p>
                <p class="no-update-available hidden"><i class="fa fa-check"></i> {'UpToDate'|@lang}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
	<div class="col-sm-6 col-md-3">
    	<div class="panel panel-default">
        	<div class="panel-heading">{'LatestUsers'|@lang}</div>
            
            <div class="list-group">
{foreach $newUsers as $user}

				<a href="{Router->build p1='UsersController' p2='edit' p3=$user}" class="list-group-item">
                	<span class="str-truncated">
                        {if $user->getFullName() == ''}
                    	    {$user->username}
                        {else}
                            {$user->username} ({$user->getFullName()})
                        {/if}
                    </span>
                    <i class="fa fa-chevron-right pull-right"></i>
                </a>
{/foreach}
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-md-3">
    	<div class="panel panel-default">
        	<div class="panel-heading">{'MimeTypes'|@lang}</div>
            
            <ul class="list-group">
{foreach $mimes as $mime}

				<li class="list-group-item">
                	<span class="str-truncated">
                    	{$mime->mime}
                    </span>
                    <span class="pull-right">{$mime->num}</span>
                </li>
			
{/foreach}
            </ul>
        </div>
    </div>

    <div class="col-sm-6 col-md-3">
    	<div class="panel panel-default">
        	<div class="panel-heading">{'QuotaMostUsedSpace'|@lang}</div>
            
            <ul class="list-group">
{foreach $userByQutoa as $obj}

				<li class="list-group-item">
                	<span class="str-truncated">
                		{if $obj->lastname eq ''}
                			{if $obj->firstname eq ''}
                				{$obj->username}
                			{else}
                				{$obj->username} ({$obj->firstname})
                			{/if}
                		{else}
	                    	{$obj->username} ({$obj->firstname} {$obj->lastname})
                		{/if}
                    </span>
                    <span class="pull-right">
                    	{$obj->used|@filesize}
                    </span>
                </li>
			
{/foreach}
            </ul>
        </div>
    </div>
    
    <div class="col-sm-6 col-md-3">
    	<div class="panel panel-default">
        	<div class="panel-heading">{'ServerConfiguration'|@lang}</div>
            
            <ul class="list-group">
            	<li class="list-group-item">
                	<span class="str-truncated">{'PHPVersion'|@lang}</span>
                    <span class="pull-right">{$phpversion}</span>
                </li>
                <li class="list-group-item">
                	<span class="str-truncated">{'MySQLVersion'|@lang}</span>
                    <span class="pull-right">{$mysqlversion}</span>
                </li>                
                <li class="list-group-item">
                	<span class="str-truncated">{'MaxPostSize'|@lang}</span>
                    <span class="pull-right">{$maxpost|@filesize}</span>
                </li>
                <li class="list-group-item">
                	<span class="str-truncated">{'MaxUploadSize'|@lang}</span>
                    <span class="pull-right">{$maxupload|@filesize}</span>
                </li>
                <li class="list-group-item">
                	<span class="str-truncated">{'Extension'|@lang} "imagick"</span>
                    <span class="pull-right">{if $imagick}<i class="fa fa-check" title="{'Installed'|@lang}"></i>{else}<i class="fa fa-remove" title="{'NotInstalled'|@lang}"></i>{/if}</span>
                </li>
                <li class="list-group-item">
                	<span class="str-truncated">{'Extension'|@lang} "rar"</span>
                    <span class="pull-right">{if $rar}<i class="fa fa-check" title="{'Installed'|@lang}"></i>{else}<i class="fa fa-remove" title="{'NotInstalled'|@lang}"></i>{/if}</span>
                </li>
            </ul>
        </div>
    </div>
</div>
{/block}

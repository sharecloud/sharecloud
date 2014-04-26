{extends 'main.tpl'}
{block name=content}

<table class="table table-striped table-hover">
	<colgroup>
        <col />
        <col class="col-sm" />
        <col class="col-sm" />
        <col class="col-md" />
    </colgroup>
    
    <thead>
    	<tr>
        	<th>{'Username'|@lang}</th>
            <th>{'Quota'|@lang}</th>
            <th>{'UsedSpace'|@lang}</th>
            <th>{'EMail'|@lang}</th>
        </tr>
    </thead>
    
    <tbody>
{foreach $users as $user}
    	<tr>
        	<td>
            	<span class="glyphicon glyphicon-user"> </span>
            	<a href="{Router->build p1='UsersController' p2='edit' p3=$user}">{$user->username} ({$user})</a>
            </td>
            <td>{if $user->quota eq 0}{'Unlimited'|@lang}{else}{$user->quota|@filesize}{/if}</td>
            <td>{$user->getUsedSpace()|@filesize}</td>
            <td>{$user->email}</td>
        </tr>
{/foreach}
    </tbody>
</table>

<div class="buttons">
	<a href="{Router->build p1='UsersController' p2='add'}" class="btn btn-default btn-sm" role="button">
    	<span class="glyphicon glyphicon-plus"> </span> {'AddUser'|@lang}
    </a>
</div>

{/block}
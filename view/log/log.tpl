{extends 'main.tpl'}
{block name=content}

{if $showPHPEntries}
<table class="table table-striped table-hover">
	<colgroup>
    	<col class="col-sm" />
        <col class="col-md" />
        <col />
        <col class="col-md" />
        <col class="col-sm" />
    </colgroup>
    
    <thead>
    	<tr>
        	<th>{'ID'|@lang}</th>
            <th>{'ErrorLevel'|@lang}</th>
            <th>{'ErrorMsg'|@lang}</th>
            <th>{'Counter'|@lang}</th>
            <th>{'Date'|@lang}</th>
        </tr>
    </thead>
    
    <tbody>
{foreach $entries as $entry}
    	<tr>
        	<td>{$entry->id}</td>
            <td>{$entry->level}</td>
            <td>{$entry->message}</td>
            <td>{$entry->counter}</td>
            <td>{$entry->date}</td>
        </tr>
{/foreach}
    </tbody>
</table>
{else}
<table class="table table-striped table-hover">
	<colgroup>
    	<col class="col-sm" />
        <col class="col-md" />
        <col />
        <col class="col-md" />
        <col class="col-sm" />
    </colgroup>
    
    <thead>
    	<tr>
        	<th>{'ID'|@lang}</th>
            <th>{'ErrorSender'|@lang}</th>
            <th>{'ErrorMsg'|@lang}</th>
            <th>{'Date'|@lang}</th>
        </tr>
    </thead>
    
    <tbody>
{foreach $entries as $entry}
    	<tr>
        	<td>{$entry->id}</td>
            <td>{$entry->sender}</td>
            <td>{$entry->message}</td>
            <td>{$entry->date}</td>
        </tr>
{/foreach}
    </tbody>
</table>
{/if}

<div class="buttons">
	<a href="{Router->build p1='LogController' p2='clear'}" class="btn btn-default btn-sm" role="button">
    	<i class="fa fa-trash"></i> {'ClearLog'|@lang}
    </a>
{if $showPHPEntries}
	<a href="{Router->build p1='LogController' p2='index'}" class="btn btn-default btn-sm" role="button">{'SystemEntries'|@lang}</a>
{else}
	<a href="{Router->build p1='LogController' p2='php'}" class="btn btn-default btn-sm" role="button">{'PHPEntries'|@lang}</a>
{/if}
</div>


{/block}
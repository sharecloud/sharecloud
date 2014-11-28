{extends 'main.tpl'}
{block name=content}
{foreach $checks as $check}
	{if $check->result eq 1}
	<div class="panel panel-warning">
    {elseif $check->result eq 2}
    <div class="panel panel-danger">
    {else}
    <div class="panel panel-success">
    {/if}
    	<div class="panel-heading">{$check->caption}</div>
        <div class="panel-body">
        	<p>
            	<b>Status:</b>
                {if $check->result eq 1}
                poor
                {elseif $check->result eq 2}
                bad
                {else}
                Okay
                {/if}
            </p>
            
            {if $check->message neq ''}
            <p>{$check->message}</p>
            {/if}
        </div>
    </div>
{/foreach}

<h3>Extensions</h3>

{foreach $extensions as $check}
	{if $check->result eq 1}
	<div class="panel panel-warning">
    {elseif $check->result eq 2}
    <div class="panel panel-danger">
    {else}
    <div class="panel panel-success">
    {/if}
    	<div class="panel-heading">{$check->caption}</div>
        <div class="panel-body">
        	<p>
            	<b>Status:</b>
                {if $check->result eq 1}
                poor
                {elseif $check->result eq 2}
                bad
                {else}
                Okay
                {/if}
            </p>
            
            {if $check->message neq ''}
            <p>{$check->message}</p>
            {/if}
        </div>
    </div>
{/foreach}


<div class="buttons">
	<a href="{if $canProceed eq true}index.php?action=tables{else}#{/if}" class="btn btn-default" role="button" {if $canProceed neq true}disabled="disabled"{/if}>Proceed <span class="fa fa-chevron-right"> </span></a>
</div>
{/block}
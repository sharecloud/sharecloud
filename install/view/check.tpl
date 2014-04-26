{extends 'main.tpl'}
{block name=content}
<style>
.box {
	border:1px solid #ccc;
	margin-bottom:5px;
}

.box.good {
	background:green;
	color:white;	
}

.box.bad {
	background:#A40000;	
	color:white;
}

.box.notGood {
	background:rgb(230, 199, 0);
}

.box .content {
	border-top:1px solid #ccc;
	background:white;
	padding:5px;
	color:black;
}
.box .content p {
	margin:2px 0px;	
}

.box .status p {
	display:block;
	float:left;
	width:80%;
	margin:0px;
	padding:5px;
}

.box .status span {
	display:block;
	float:right;
	width:15%;
	margin:0px;
	padding:5px;
	font-weight:bold;
	text-align:right;
}
    
code {
	display:block;
	background-color: lightgrey;
	padding:1px;
	margin:3px 0px;
}

code.inline {
	display:inline;
	background:none;
	margin:0px;	
}

.button.disabled a {
	cursor:no-drop;		
}
.button.disabled:hover {
	border:1px solid #999;	
}

.button.disabled:focus,
.button.disabled:active {
	background:#F0F0F0;	
}
</style>

{foreach $checks as $check}
	{if $check->result eq 1}
	<div class="box notGood">
    {elseif $check->result eq 2}
    <div class="box bad">
    {else}
    <div class="box good">
    {/if}
    	<div class="status clearfix">
    	<p>{$check->caption}</p>
            {if $check->result eq 1}
            <span class="notGood">POOR</span>
            {elseif $check->result eq 2}
            <span class="bad">BAD</span>
            {else}
            <span class="ok">OK</span>
            {/if}
        </div>
        
        {if $check->message neq ''}
        <div class="content">
        	{$check->message}
        </div>
        {/if}
    </div>
{/foreach}

<h3>Extensions</h3>

{foreach $extensions as $check}
	{if $check->result eq 1}
	<div class="box notGood">
    {elseif $check->result eq 2}
    <div class="box bad">
    {else}
    <div class="box good">
    {/if}
    	<div class="status clearfix">
    	<p>{$check->caption}</p>
            {if $check->result eq 1}
            <span class="notGood">POOR</span>
            {elseif $check->result eq 2}
            <span class="bad">BAD</span>
            {else}
            <span class="ok">OK</span>
            {/if}
        </div>
        
        {if $check->message neq ''}
        <div class="content">
        	{$check->message}
        </div>
        {/if}
    </div>
{/foreach}


<div class="buttons">
	<label class="button{if $canProceed eq false} disabled{/if}">
        <a href="{if $canProceed eq true}index.php?action=tables{else}#{/if}">Proceed</a>
    </label>
</div>
{/block}
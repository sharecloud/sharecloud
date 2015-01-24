{extends 'main.tpl'}
{block name=content}
<form action="index.php?action=user" method="post" class="form-horizontal" role="form">
    <fieldset>
    	<legend>Credentials</legend>
        
        <div class="form-group{if $errorUsername neq ''} has-error{/if}">
        	<label class="col-sm-2 control-label">Username:</label>
            <div class="col-sm-10">
            	<input type="text" class="form-control" id="username" name="username" value="{$username}" {if $username eq ''}autofocus="autofocus" {/if}/>
                {if $errorUsername neq ''}<span class="help-block">{$errorUsername}</span>{/if}
            </div>
        </div>
        
        <div class="form-group{if $errorPassword neq ''} has-error{/if}">
        	<label for="password" class="col-sm-2 control-label">Password: *</label>
            <div class="col-sm-10">
            	<input type="password" class="form-control" id="password" name="password" {if $username neq ''}autofocus="autofocus" {/if}/>
                {if $errorPassword neq ''}<span class="help-block">{$errorPassword}</span>{/if}
            </div>
        </div>
    </fieldset>
    
    <div class="buttons">
    	<input type="hidden" name="submit" value="submit" />
    	<button type="submit" class="btn btn-default">Proceed <span class="fa fa-chevron-right"> </span></button>
    </div>
</form>
{/block}
{extends 'main.tpl'}
{block name=content}
<form action="index.php?action=user" method="post" class="form-horizontal" role="form">
    <fieldset>
    	<legend>Credentials</legend>
        
        <div class="form-group">
        	<label class="col-sm-2 control-label">Username:</label>
            <div class="col-sm-10">
            	<p class="form-control-static">admin</p>
            </div>
        </div>
        
        <div class="form-group{if $error neq ''} has-error{/if}">
        	<label for="password" class="col-sm-2 control-label">Password: *</label>
            <div class="col-sm-10">
            	<input type="password" class="form-control" id="password" name="password" />
                {if $error neq ''}<span class="help-block">{$error}</span>{/if}
            </div>
        </div>
    </fieldset>
    
    <div class="buttons">
    	<input type="hidden" name="submit" value="submit" />
    	<button type="submit" class="btn btn-default">Proceed <span class="glyphicon glyphicon-chevron-right"> </span></button>
    </div>
</form>
{/block}
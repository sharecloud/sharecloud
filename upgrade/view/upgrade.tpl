{extends 'main.tpl'}
{block name=content}
<form action="index.php" method="post" class="form-horizontal" role="form">
	<fieldset>
    	<legend>Upgrade</legend>
        
        <div class="form-group">
        	<label for="upgrade" class="col-sm-3 control-label">Select upgrade:</label>
            <div class="col-sm-9">
            	<select id="upgrade" name="upgrade" class="form-control">
{foreach key=k item=value from=$upgrades}
					<option value="{$k}">{$value}</option>
{/foreach}                	
                </select>
            </div>
        </div>

    </fieldset>
    
    <div class="buttons">
    	<input type="hidden" name="submit" value="submit" />
    	<button type="submit" class="btn btn-default">Start upgrade <span class="glyphicon glyphicon-chevron-right"> </span></button>
    </div>
</form>
{/block}
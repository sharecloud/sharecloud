{extends 'main.tpl'}
{block name=content}
<form action="index.php" method="post">
	<fieldset>
    	<legend>Upgrade</legend>
        
        <div style="input select">
        	<label for="upgrade">Select upgrade:</label>
            <select id="upgrade" name="upgrade">
{foreach key=k item=value from=$upgrades}
				<option value="{$k}">{$value}</option>
{/foreach}            
            </select>
        </div>

    </fieldset>
    
    <div class="buttons">
        <label class="button">
            <input type="submit" name="submit" value="Start upgrade" />
        </label>
    </div>
</form>
{/block}
{extends 'main.tpl'}
{block name=content}
<form action="" method="post">
	<fieldset>
    	<legend>Credentials</legend>
        
        <div class="input text">
            <label for="username">Username: *</label>
            <b>admin</b>
        </div>
        
        <div class="input password{if $error neq ''} error{/if}">
        	<label for="password">Password: *</label>
            <input type="password" id="password" name="password" />
            {if $error neq ''}<p class="message">{$error}</p>{/if}
        </div>
    </fieldset>
    
    <div class="buttons">
    	<label class="button">
        	<input type="submit" value="Proceed" name="submit" />
        </label>
    </div>
</form>
{/block}
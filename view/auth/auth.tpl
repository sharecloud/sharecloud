{extends 'main.tpl'}
{block name=content}

<div class="col-sm-4 col-sm-offset-4">
	<form class="form-signin" role="form" data-noajax="true" method="POST" action="{Router->build p1='AuthController' p2='login'}">
		{if $error}
			<div class="form-group has-error has-feedback">
		{/if}
			<input type="text" name="username" class="form-control" placeholder="{'Username'|@lang}" required autofocus>
			<input type="password" name="password" class="form-control" placeholder="{'Password'|@lang}" required>
		{if $error}
			<label class="control-label" for="inputError2">{'LogInFailed'|@lang}</label>
		</div>
		{/if}
		<label class="checkbox">
			<input type="checkbox" value="remember-me">{'RememberMe'|@lang}
		</label>
		<button class="btn btn-lg btn-primary btn-block" name="submit" value="submit" type="submit">{'LogIn'|@lang}</button>
	</form>
</div>
{/block}
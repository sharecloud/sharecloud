{extends 'auth/main.tpl'}
{block name=content}
	<form class="form" role="form" data-noajax="true" method="POST">
		<div class="form-group">
			<input type="password" name="password" class="form-control" placeholder="{'Password'|@lang}" required autofocus>
        </div>
        
        <button class="btn btn-lg btn-primary btn-block" name="submit" value="submit" type="submit">{'Authenticate'|@lang}</button>		
	</form>
{/block}
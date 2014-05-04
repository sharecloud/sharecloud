{extends 'auth/main.tpl'}
{block name=content}

	<form class="form form-lostpw" role="form" data-noajax="true" method="POST" action="{$form_url}">
		<div class="form-group">
			<input type="password" name="password" class="form-control" placeholder="{'Password'|@lang}" required autofocus>
			<input type="password" name="password2" class="form-control" placeholder="{'ReenterPassword'|@lang}" required>
		</div>
        
        <div class="row">
        	<div class="col-md-6">
            	<button class="btn btn-lg btn-primary btn-block" name="submit" value="submit" type="submit">{'Proceed'|@lang}</button>
            </div>
            
            <div class="col-md-6">
            	<a href="{Router->build p1='AuthController' p2='login'}" class="btn btn-default btn-lg btn-block" role="button">{'Cabcel'|@lang}</a>
            </div>
        </div>
		
	</form>
{/block}
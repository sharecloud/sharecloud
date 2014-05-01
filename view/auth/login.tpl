{extends 'auth/main.tpl'}
{block name=content}

	<form class="form form-signin" role="form" data-noajax="true" method="POST" action="{Router->build p1='AuthController' p2='login'}">
		<div class="form-group">
			<input type="text" name="username" class="form-control" placeholder="{'Username'|@lang}" required autofocus>
			<input type="password" name="password" class="form-control" placeholder="{'Password'|@lang}" required>
		</div>
        
        <div class="row">
        	<div class="col-md-6">
            	<button class="btn btn-lg btn-primary btn-block" name="submit" value="submit" type="submit">{'LogIn'|@lang}</button>
            </div>
            
            <div class="col-md-6">
            	<a href="{Router->build p1='AuthController' p2='lostpw'}" class="btn btn-default btn-lg btn-block" role="button">{'LostPW'|@lang}</a>
            </div>
        </div>
		
	</form>
{/block}
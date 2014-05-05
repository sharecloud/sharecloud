{extends 'auth/main.tpl'}
{block name=content}
	
    <form class="form" role="form" data-noajax="true" method="POST" action="{Router->build p1='AuthController' p2='lostpw'}">
		<div class="form-group">
        	<input type="text" name="email" class="form-control input-lg" placeholder="{'EMail'|@lang}" required autofocus>
		</div>
		
        
        <div class="row">
        	<div class="col-md-6">
            	<button class="btn btn-lg btn-primary btn-block" name="submit" value="submit" type="submit">{'Proceed'|@lang}</button>
            </div>
            
            <div class="col-md-6">
            	<a href="{Router->build p1='AuthController' p2='login'}" class="btn btn-default btn-lg btn-block" role="button">{'Cancel'|@lang}</a>
            </div>
        </div>
		
    </form>
{/block}
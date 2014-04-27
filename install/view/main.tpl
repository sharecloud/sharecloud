<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$title}</title>
<link rel="stylesheet" href="../css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="../css/main.css" type="text/css" />
<style type="text/css">
.nav .ul li.done a {
	font-weight:bold;	
}
</style>
</head>

<body>
<div class="page">
    <header>
        <div class="container-fluid">
            <h1>
            	One-Click File Host
            </h1>
    </header>
    
    <nav class="navbar navbar-default" role="navigation">
    	<div class="container-fluid">
            <ul class="nav navbar-nav">
                <li class="{if $curStep lt 2}active{else}done{/if}"><a href="#"><span class="badge">1</span> Configuration Check</a></li>
                <li class="{if $curStep eq 2}active{else if $curStep lt 2}upcoming{else}done{/if}"><a href="#"><span class="badge">2</span> Create database tables</a></li>
                <li class="{if $curStep eq 3}active{else if $curStep lt 3}upcoming{else}done{/if}"><a href="#"><span class="badge">3</span> Add admin account</a></li>
                <li class="{if $curStep eq 4}active{else if $curStep lt 4}upcoming{else}done{/if}"><a href="#"><span class="badge">4</span> Finish</a></li>   	
            </ul>
        </div>
    </nav>

    <div class="main container-fluid">
            <h3>{$heading}</h3>
            
            {block name=content}{/block}
    </div>
        
    <div class="push"> </div>
</div>

<footer>
	<div class="wrapper">
    	© {'Y'|date} | <a href="https://bitbucket.org/frostie/one-click-file-hosting" data-noajax="true">One-Click File Hosting</a>
    </div>
</footer>

</body>
</html>
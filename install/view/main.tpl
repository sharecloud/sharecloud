<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$title}</title>
<link rel="stylesheet" href="../css/main.css" type="text/css" />
<style type="text/css">
nav ul li p {
	margin:0px;
	padding:0px 10px;	
}

nav ul li p.current {
	font-weight:bold;	
}

nav ul li p.upcoming {
	color:#888;	
}

nav ul li p span.step {
	border:1px solid #777;
	border-radius:7px;
	color:#777;
	padding:2px 5px;
	font-size:11px;
	background:#efefef;
	font-weight:bold;
}

nav ul li p.done span.step {
	color:green;	
}
</style>
</head>

<body>
<header>
	<div class="wrapper clearfix">
		<h1>One-Click File Host</h1>
	</div>
</header>

<nav>
	<div class="wrapper">
		<ul>
        	<li><p class="{if $curStep lt 2}current{else}done{/if}"><span class="step">1</span> Choose upgrade</p></li>
            <li><p class="{if $curStep eq 2}current{else if $curStep lt 2}upcoming{else}done{/if}"><span class="step">2</span> Finish</p></li>
		</ul>
	</div>
</nav>

<div class="main">
	<div class="wrapper">
		<h3>{$heading}</h3>
        
        {block name=content}{/block}
	</div>
</div>
</body>
</html>
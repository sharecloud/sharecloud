<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title}</title>
<link rel="stylesheet" href="{$HTTP_BASEDIR}/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="{$HTTP_BASEDIR}/css/main.css" type="text/css" />
{$resources}

<script>
System.config.httpHost = "{$HTTP_BASEDIR}";
System.config.modRewrite = {$MOD_REWRITE};
</script>
<script>
{foreach $LangStrings as $key => $value}System.l10n.add('{$key}','{$value}');{/foreach}
</script>

</head>

<body class="auth preventautosync">
<div class="page">
    <header>
        <div class="container-fluid">
            <h1>
            	sharecloud
            </h1>
    </header>
    
    <div class="container-fluid alerts">
{if $infoMsg|default:FALSE }
        <div class="alert alert-info alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {$infoMsg}
        </div>
{/if}

{if $successMsg|default:FALSE }
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {$successMsg}
        </div>
{/if}

{if $errorMsg|default:FALSE }
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {$errorMsg}
        </div>
{/if}
		<div class="clonable alert alert-dismissable">
        	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <p class="msg"> </p>
        </div>
    </div>
    
    <div class="main container-fluid">
        {if $heading|default:FALSE}
        	<h3>{$heading}</h3>
        {/if}
        {block name=content}{/block}
    </div>
    
    <footer>
        <div class="wrapper">
            © {'Y'|date} | <a href="https://github.com/frostieDE/filehost" data-noajax="true">sharecloud</a>
        </div>
    </footer>
</div>
</body>
</html>
{extends 'mails/lostpw.tpl'}
{block name=content}
<p>Hello {$user} ({$user->username}),</p>

<p>if you want to reset your password, click at the link below. If it was not you who has triggered this mail, keep cool. Your password has not been changed yet.</p>

<a href="{$link}">Reset password</a>

<p class="info">This is a auto-generated mail. Replying is useless.</p>
{/block}
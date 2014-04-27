{extends 'mails/lostpw.tpl'}
{block name=content}
<p>Hallo {$user} ({$user->username}),</p>

<p>wenn du dein Passwort zurücksetzen möchtest, klicke auf den untenstehenden Link. Falls nicht du diese E-Mail ausgelöst haben solltest, ignoriere sie einfach. Dein Passwort wurde bisher nicht zurückgesetzt.</p>

<a href="{$link}">Passwort zurücksetzen</a>

<p class="info">Dies ist eine automatisch generierte E-Mail. Antworten ist daher nutzlos.</p>
{/block}
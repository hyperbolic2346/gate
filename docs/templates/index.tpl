{include file="templates/header.tpl" title="Gate"}

<link rel="stylesheet" type="text/css" href="templates/base.css" media="screen, handheld" />
<link rel="stylesheet" type="text/css" href="templates/enhanced.css" media="screen  and (min-width: 40.5em)" />
<!--[if (lt IE 9)&(!IEMobile)]>
<link rel="stylesheet" type="text/css" href="templates/enhanced.css" />
<![endif]-->


{if isset($camera_data)}
<script type="text/javascript" src="/lib/html5lightbox/html5lightbox.js"></script>
<script type="text/javascript">
function delete_entry(item, event_time) {
	$(item).parent().remove();
//	$('#info').html('gone!');
	$.post('/ajax_delete.php', { delete: event_time }, function (data) { $('#info').html(data); });
//	$('<form action="{$cur}" method="post"><input type="hidden" name="delete" value="' + $event_time + '"></form>').appendTo('body').submit();
}
</script>
{/if}

<div id='day'>{$day}</div>

<div id='info'>{$info}</div>

{if isset($camera_data)}
<div id='camera_events'>

<div id='cal' class='camera_event'>
{$calendar}
</div>

{foreach $camera_data as $ev}
	<div class='camera_event'>
		{if isset($cur)}
		<div class='camera_delete' onClick='delete_entry(this, "{$ev@key}")'><img src='img/x.png' height="25" width="25" /></div>
		{/if}
		<div class='camera_time'>{$ev.pretty_time}</div>
		<div class='camera_video'>
			<div id='camera_video{$ev@index}'>
				<a class="html5lightbox" href="{$ev.movie}.webm" data-ipad="{$ev.movie}.ipad.mp4" data-iphone="{$ev.movie}.ipad.mp4" data-width="640" data-height="480"><img src="{$ev.thumbnail}.thumb.jpg" width="100%" /></a>
			</div>
		</div>
	</div>
{/foreach}
</div>
{else}
<div id='cal' class='camera_event'>
{$calendar}
</div>
No events
{/if}

{if isset($add_user)}
	<form name="adduser" action="{$cur}" method="post"><input type="text" name="new_username" /><input type="password" name="new_pw" /><input type="submit" /></form>
{/if}

{include file="templates/footer.tpl"}

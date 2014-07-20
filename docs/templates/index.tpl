{include file="templates/header.tpl" title="Gate"}

<link rel="stylesheet" type="text/css" href="templates/base.css" media="screen, handheld" />
<link rel="stylesheet" type="text/css" href="templates/enhanced.css" media="screen  and (min-width: 40.5em)" />
<!--[if (lt IE 9)&(!IEMobile)]>
<link rel="stylesheet" type="text/css" href="templates/enhanced.css" />
<![endif]-->

<script type="text/javascript" src="/lib/html5lightbox/html5lightbox.js"></script>
<script type="text/javascript" src="/js/index.js"></script>

<div id='info'>{if isset($info)}{$info}{else}&nbsp;{/if}</div>
<div id='day'>{$day}</div>

{if isset($live_cam)}
<div id='live_video_toggle_div'></div>
<div id='live_video_div'>
	<div id='live_label'>Now</div>
	<div id='live_video'><img id='live_video_img' data-feed='{$live_cam}' /></div>
</div>

{if isset($access) && isset($access['control'])}
<div id='gate_control_div'>
</div>
{/if}
{/if}

<div id='calendar'>
{$calendar}
</div>

{if isset($live_cam) && isset($access['control'])}
<br style="clear:both;"/>
<div id='camera_events_toggle_div'></div>
{/if}

<div id='camera_events'>
{if isset($camera_data)}
{foreach $camera_data as $ev}
	<div class='camera_event'>
		{if isset($access['delete'])}
		<div class='camera_delete' onClick='delete_entry(this, "{$ev@key}")'><img src='img/x.png' height="25" width="25" /></div>
		{/if}
		<div class='camera_time'>{$ev.pretty_time}</div>
		<div class='camera_video'>
			<div id='camera_video{$ev@index}'>
				<a class="html5lightbox" href="{$ev.movie}.webm" data-ipad="{$ev.movie}.ipad.mp4" data-iphone="{$ev.movie}.ipad.mp4" data-width="640" data-height="480"><img {if isset($ev.refresh)}class="refresh" {/if}data-refresh="{$ev.refresh_id}" src="{$ev.thumbnail}.thumb.jpg" width="100%" /></a>
			</div>
		</div>
	</div>
{/foreach}
{else}
No events
{/if}
</div>

{if isset($add_user)}
	<form name="adduser" action="{$cur}" method="post"><input type="text" name="new_username" /><input type="password" name="new_pw" /><input type="submit" /></form>
{/if}

{if isset($edit_user)}
	<form name="edit_user" action="{$cur}" method="post">
          <select name="edit_user_id">
{foreach $users as $user}
            <option value="{$user.user_id}">{$user.username}</option>
{/foreach}
          </select>
          <input type="password" name="new_pw" /><input type="submit" /></form>
{/if}

{include file="templates/footer.tpl"}

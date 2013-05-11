{if isset($camera_data)}
{foreach $camera_data as $ev}
	<div class='camera_event'>
		{if isset($cur)}
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
{/if}

{include file="templates/header.tpl" title="Gate"}
<link rel="stylesheet" href="templates/index.css" type="text/css" media="all" />

{if isset($camera_data)}
<script type="text/javascript" src="/lib/jquery.masonry.min.js"></script>
<script type="text/javascript" src="/lib/html5lightbox/html5lightbox.js"></script>
<script type="text/javascript">
function delete_entry($event_time) {
	$('<form action="{$cur}" method="post"><input type="hidden" name="delete" value="' + $event_time + '"></form>').appendTo('body').submit();
}
</script>
{/if}

<div id='day'>{$day}</div>

{if isset($info)}
<div id='info'>{$info}</div>
{/if}

{if isset($camera_data)}
<div id='camera_events'>

<div id='cal' class='camera_event'>
{$calendar}
</div>

{foreach $camera_data as $ev}
	<div class='camera_event'>
		{if isset($cur)}
		<div class='camera_delete' onClick='delete_entry("{$ev@key}")'><img src='img/x.png' height="25" width="25" /></div>
		{/if}
		<div class='camera_time'>{$ev.pretty_time}</div>
		<div class='camera_video'>
			<div id='camera_video{$ev@index}'>
				<a class="html5lightbox" href="{$ev.movie}.webm" data-ipad="{$ev.movie}.ipad.mp4" data-iphone="{$ev.movie}.ipad.mp4" data-width="640" data-height="480"><img src="{$ev.thumbnail}.thumb.jpg" width='160' height='120'/></a>
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

{if isset($prev)}
<a href={$prev}>&lt;</a>
{else}
&lt;
{/if}

{if isset($next)}
<a href={$next}>&gt;</a>
{else}
&gt;
{/if}

{if isset($camera_data)}
<script type="text/javascript">
$(window).load(function() {
    var $container = $('#camera_events');
    
    $container.imagesLoaded(function(){
        $container.masonry({
            itemSelector: '.camera_event',
                        isFitWidth: true,
            columnWidth: 10,
        });
    });
});
</script>
{/if}

{if isset($add_user)}
	<form name="adduser" action="{$cur}" method="post"><input type="text" name="new_username" /><input type="password" name="new_pw" /><input type="submit" /></form>
{/if}

{include file="templates/footer.tpl"}

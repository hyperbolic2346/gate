{include file="templates/header.tpl" title="Gate"}

<div id='cal'>
{$calendar}
</div>

{if isset($camera_data)}
<div id='camera_events'>
<table>
{foreach $camera_data as $ev}
	<tr class='camera_event'>
		<td>{$ev.pretty_time}</td><td>{$ev.file_size}</td><td><img src="{$ev.thumbnail}" height="150" width="200"/></td><td>{$ev.movie}</td>
	</tr>
{/foreach}
</table>
</div>
{else}
No events
{/if}

{include file="templates/footer.tpl"}

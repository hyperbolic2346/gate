{include file="templates/header.tpl" title="Gate - Login"}

{if isset($info)}
<div id='info_box'>{$info}</div>
{/if}

<form action='http://gate.burntsheep.com/index.php' method='post'>

<div id='login_box'>
  <div id='login_name_label'>Name</div>
  <div id='login_name'><input name='login_name' type='text'/></div>
  <div id='login_pw_label'>Password</div>
  <div id='login_pw'><input type='password' name='login_pw' /></div>
  <div id='login_button'><input type='submit' /></div>
</div>

</form>

{include file="templates/footer.tpl"}

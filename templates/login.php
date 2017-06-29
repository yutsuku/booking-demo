<?php if (!defined("APP")) exit(); ?>
<div data-role="page" id="login">
	<div data-role="header">
		<h1>Log In</h1>
	</div>

	<div role="main" class="ui-content">
		<form action="" id="login-form" method="post" data-ajax="false">
			<input type="hidden" name="login" value=""/>
			<label for="text-basic">User name:</label>
			<input type="text" name="username" value="">
			<label for="text-basic">Password:</label>
			<input type="password" name="password" id="password" value="" autocomplete="off">
			<input type="submit" value="Log In">
		</form>
	</div>

	<?php include "footer.php"; ?>
</div>
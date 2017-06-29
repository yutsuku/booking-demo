<?php if (!defined("APP")) exit(); ?>
<div data-role="page" id="register">
	<div data-role="header">
		<h1>Register new account</h1>
	</div>

	<div role="main" class="ui-content">
		<form action="" id="register-form" method="post" data-ajax="false">
			<input type="hidden" name="register" value=""/>
			<label for="text-basic">User name:</label>
			<input type="text" name="username" value="">
			<label for="text-basic">Password:</label>
			<input type="password" name="password" id="password" value="" autocomplete="off">
			<input type="submit" value="Register">
		</form>
	</div>

	<?php include "footer.php"; ?>
</div>
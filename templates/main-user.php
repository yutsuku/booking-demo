<?php if (!defined("APP")) exit(); ?>
<div data-role="page" id="main-user">

	<div data-role="header">
		<h1>Index</h1>
		<div class="ui-body-b ui-body">
			<h3>Logged In as <?php echo $_SESSION["user_name"]; ?>.</h3>
			<?php include "navigation-bar.php"; ?>
		</div>
	</div>

	<?php include "footer.php"; ?>
</div>
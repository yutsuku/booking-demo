<?php if (!defined("APP")) exit(); ?>
<div data-role="page" id="booking-new">

	<div data-role="header">
		<h1>Place new booking</h1>
		<div class="ui-body-b ui-body">
			<h3>Logged In as <?php echo $_SESSION["user_name"]; ?>.</h3>
			<?php include "navigation-bar.php"; ?>
		</div>
	</div>

	<div role="main" class="ui-content">
		<p>Here you can book a room</p>
		<form action="" id="booking-form" method="post" data-ajax="false">
			<input type="hidden" name="booking-new" value=""/>
			<input type="text" placeholder="YYYY-MM-DD" name="date" data-role="date" data-inline="true" data-beforeShowDay="IsAvaiable" data-dateFormat="yy-mm-dd">
			<input type="submit" value="Book a room">
		</form>
	</div>

	<?php include "footer.php"; ?>
</div>
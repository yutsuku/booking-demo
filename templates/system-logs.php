<?php if (!defined("APP")) exit(); ?>
<div data-role="page" id="system-logs">

	<div data-role="header">
		<h1>System logs</h1>
		<div class="ui-body-b ui-body">
			<h3>Logged In as <?php echo $_SESSION["user_name"] ?>.</h3>
			<?php include "navigation-bar.php"; ?>
		</div>
	</div>

	<div role="main" class="ui-content">
		<ul id="ul-system-logs" data-role="listview" data-inset="true" data-theme="b" data-divider-theme="a" data-count-theme="a">
			<li data-role="list-divider">Log book</li>
			<?php
			$data = $DB->GetBookingsLog();
			for ($i = 0, $size = count($data); $i < $size; ++$i) {
				echo sprintf("<li><h1>%s</h1><p>%s</p></li>", htmlentities($data[$i]["name"]), $data[$i]["date"]);
			}
			?>
		</ul>
	</div>

	<?php include "footer.php"; ?>
</div>
<script>
	/*$.each(my_bookings, function(index, value) {
		$("#user-bookings").append('<li data-icon="delete"><a data-ajax="false" href="?booking-cancel=' + value + '">' + value + '</a></li>');
	});*/
</script>
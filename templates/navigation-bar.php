<?php if (!defined("APP")) exit(); ?>
			<div id="nav" data-role="navbar">
				<ul>
					<li><a href="#booking-new" data-icon="home">Place new booking</a></li>
					<li><a href="#booking-cancel" data-icon="delete">Cancel booking</a></li>
					<?php if ($_SESSION["user_admin"]) { ?>
					<li><a href="#system-logs" data-icon="bullets">System logs</a></li>
					<?php } ?>
					<li><a data-ajax="false" href="?logout=1" data-icon="back">Log out</a></li>
				</ul>
			</div>
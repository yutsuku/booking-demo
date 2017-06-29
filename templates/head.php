<?php if (!defined("APP")) exit(); ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Spartakus needs shit to be made</title>

	<link rel="stylesheet" href="//code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="jquery/mobile/datepicker.css">
	
    <script src="//code.jquery.com/jquery-2.2.4.min.js"></script>
	<script src="//code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
	<script src="jquery/ui/datepicker.js"></script>
    <script src="jquery/mobile/datepicker.js"></script>
	<script>
	    var my_bookings = <?php echo $my_bookings ?>;
	    var all_bookings = <?php echo $all_bookings ?>;
		
		var IsAvaiable = function(date) {
			if($.inArray($.datepicker.formatDate('yy-mm-dd', date ), my_bookings) > -1) {
				return [false, "booked", "You have booking for this day"];
			} else {
				if($.inArray($.datepicker.formatDate('yy-mm-dd', date ), all_bookings) > -1) {
					return [false, "booked-out", "Booked out"];
				} else {
					return [true, ""];
				}
			}
		}
	</script>
</head>
<body>
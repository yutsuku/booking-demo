<?php if (!defined("APP")) exit(); ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>System error</title>
	<style>
		html, body { background: rgb(206, 52, 38); color: white; font: 1em "Open Sans", sans-serif; }
		#contanyan {
			position:fixed;
			top:0;
			right:0;
			bottom:0;
			left:0;
			overflow:auto;
		}
		.box {
			width: 100%;
			height: 100%;
		}
		.flex {
			display:flex;
			flex-direction:column;
			justify-content:center;
			align-items:center;
			align-content:center;
		}
		p { margin: 0px; margin-bottom: 2px; }
	</style>
</head>
<body>

<div id="contanyan">
	<div class="box flex">
		<div style="width: 50%; margin: 0px auto;">
			<h1>Something terrible happened!</h1>
			<p><?php echo $error_line1; ?></p>
			<p><?php echo $error_line2; ?></p>
			<br />
			<p>Stacktrace:</p>
			<pre><?php echo $error_line3; ?></pre>
		</div>
	</div>
</div>

</body>
</html>
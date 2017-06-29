<?php if (!defined("APP")) exit(); ?>
<script>
$(function() {
	$("a").click(function( event ) {
		console.log(this.href)
	});
	$(window).hashchange(function() {
		var hash = location.hash;
		$( "#nav a" ).each(function() {
		  var that = $(this);
		  that[ that.attr( "href" ) === hash ? "addClass" : "removeClass" ]( "ui-btn-active" );
		});
	});
});
</script>

</body>
</html>
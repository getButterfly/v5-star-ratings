jQuery(document).ready(function($){
	$('.mn_basic_rating').jRating({
		phpPath: mnsr_ajax.pluginurl+'ajax/jRating.php',
		bigStarsPath : mnsr_ajax.pluginurl+'images/stars.png', // path of the icon stars.png
		smallStarsPath : mnsr_ajax.pluginurl+'images/small.png',
		type: mnsr_ajax.mn_star_size,
		length : mnsr_ajax.mn_no_of_star,
		rateMax : mnsr_ajax.mn_star_maximum_rating,
		decimalLength : 1,
		ratingColor: mnsr_ajax.mn_star_rating_color,
		hoverColor: mnsr_ajax.mn_star_hover_color,
		rateafterlogin: mnsr_ajax.rate_after_login,
		loggedin: mnsr_ajax.loggedin,
		onSuccess: function(msg, element){
			var msgd = $("#mnstr_msg"+element);
			msgd.addClass('mnstr_success').removeClass('mnstr_err');
			msgd.html(msg);
			msgd.fadeIn();
			setTimeout(function() {
				msgd.fadeOut();
			}, 3000);
		},
		onError: function(msg, element){
			var msgd = $(".mn_overlay_msg");
			msgd.html(msg);
			msgd.fadeIn();
			setTimeout(function() {
				msgd.fadeOut();
			}, 2000);
		}
	});
});
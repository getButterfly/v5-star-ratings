/* global jQuery */
/* global v5Ajax */

jQuery(document).ready(function() {
	jQuery(".mn_basic_rating").jRating({
		phpPath:		v5Ajax.pluginurl + "ajax/jRating.php",
		bigStarsPath:	v5Ajax.pluginurl + "images/stars.png",
		smallStarsPath: v5Ajax.pluginurl + "images/small.png",
		type:			v5Ajax.mn_star_size,
		length: 		v5Ajax.mn_no_of_star,
		rateMax:		v5Ajax.mn_star_maximum_rating,
		decimalLength:	1,
		ratingColor:	v5Ajax.mn_star_rating_color,
		hoverColor: 	v5Ajax.mn_star_hover_color,
		rateafterlogin: v5Ajax.rate_after_login,
		loggedin:		v5Ajax.mn_loggedin,

		onSuccess: function(msg, element) {
			var msgd = jQuery("#mnstr_msg" + element);

			msgd.addClass("mnstr_success").removeClass("mnstr_err");
			msgd.html(msg);
			msgd.fadeIn();

			setTimeout(function() {
				msgd.fadeOut();
			}, 3000);
		},
		onError: function(msg, element) {
			var msgd = jQuery(".mn_overlay_msg");

			msgd.html(msg);
			msgd.fadeIn();

			setTimeout(function() {
				msgd.fadeOut();
			}, 2000);
		}
	});
});

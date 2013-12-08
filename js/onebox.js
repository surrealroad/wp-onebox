// Javascript for rendering a onebox link

// render links on page

// exists
(function($) {
	jQuery.fn.exists = function() {
		return this.length > 0;
	};
})(jQuery);

// onebox construction
(function($) {
	jQuery.fn.onebox = function() {
		if ($(this).exists()) {
			$(this).each(function () {
				var $this = $(this),
					url = $(this).children().eq(0).attr("href"),
					requesturl = OneboxParams.renderURL + '?url=' + encodeURIComponent(url);
				//console.log(requesturl);

				$.getJSON(requesturl, function (onebox) {
					//console.log(onebox.onebox); //uncomment this for debug
					if(onebox.onebox) $this.html(onebox.onebox);
					// convert ratings
					$this.find('.onebox-stars').oneboxstars();
				});
			});
		}
	};
})(jQuery);

// stars
// http://stackoverflow.com/questions/1987524/turn-a-number-into-star-rating-display-using-jquery-and-css
(function($) {
	jQuery.fn.oneboxstars = function() {
		if ($(this).exists()) {
		return $(this).each(function() {
			// Get the value
			var val = parseFloat($(this).html());
			// Make sure that the value is in 0 - 5 range, multiply to get width
			var size = Math.max(0, (Math.min(5, val))) * 16;
			// Create stars holder
			var $span = $('<span />').width(size);
			// Replace the numerical value with stars
			$(this).html($span);
		});
	}
	};
})(jQuery);

jQuery(document).ready(function() {
	jQuery(".onebox-container").onebox();
});

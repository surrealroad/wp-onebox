// Javascript for rendering a onebox link

// render links on page

// exists
(function($) {
	jQuery.fn.exists = function() {
		return this.length > 0;
	};
})(jQuery);

// onebox construnction
(function($) {
	jQuery.fn.onebox = function() {
		if ($(this).exists()) {
			$(this).each(function () {
				var $this = $(this),
					url = $(this).children().eq(0).attr("href"),
					requesturl = OneboxParams.renderURL + '&url=' + encodeURIComponent(url);
				//console.log(requesturl);

				$.getJSON(requesturl, function (onebox) {
					//console.log(onebox.onebox); //uncomment this for debug
					if(onebox.onebox) $this.html(onebox.onebox);
					// convert ratings
					//$this.find('.stars').stars();
				});
			});
		}
	};
})(jQuery);

jQuery(document).ready(function() {
	jQuery(".onebox-container").onebox();
});

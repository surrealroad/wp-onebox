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

				$.getJSON(requesturl, function (data) {
					//console.log(data.data); //uncomment this for debug
					if(data.data) {
						if(data.data.displayurl) var url = data.data.displayurl;
						else var url = data.data.url;

						var template = OneboxParams.template;

						if(OneboxParams.dark) data.classes +=' dark';

						template = template.replace(/{url}/g, url);
						template = template.replace(/{class}/g, data.classes);
						template = template.replace(/{favicon}/g, '<img src="' + data.data.favicon + '" class="onebox-favicon"/>');
						template = template.replace(/{sitename}/g, data.data.sitename);
						template = template.replace(/{image}/g, '<img src="' + data.data.image + '" class="onebox-thumbnail"/>');
						template = template.replace(/{title}/g, data.data.title);
						template = template.replace(/{description}/g, data.data.description);
						template = template.replace(/{additional}/g, data.data.additional);
						template = template.replace(/{footer}/g, data.data.footer);
						template = template.replace(/{title-button}/g, data.data.titlebutton);
						template = template.replace(/{footer-button}/g, data.data.footerbutton);

						$this.html(template);
						// convert ratings
						$this.find('.onebox-stars').oneboxstars();
					}
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

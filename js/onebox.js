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

						var template = '<div class="{class}">'
							+ '<div class="onebox-source"><div class="onebox-info">'
							+ '<a href="{url}" target="_blank" rel="nofollow">'
							+ '{favicon}'
							+ '<span>{sitename}</span></a>'
							+ '</div></div>'
							+ '<div class="onebox-result-body">'
							+ '<a href="{url}" target="_blank" rel="nofollow">{image}</a>'
							+ '<h4><a href="{url}" target="_blank" rel="nofollow">{title}</a></h4>'
							+ '<p class="onebox-description">{description}</p>'
							+ '<p class="onebox-additional">{additional}</p>'
							+ '</div>'
							+ '<div class="onebox-clearfix"></div>'
							+ '</div>';

						template = template.replace('{url}', url);
						template = template.replace('{class}', data.classes);
						template = template.replace('{favicon}', '<img src="' + data.data.favicon + '" class="onebox-favicon"/>');
						template = template.replace('{sitename}', data.data.sitename);
						template = template.replace('{image}', '<img src="' + data.data.image + '" class="onebox-thumbnail"/>');
						template = template.replace('{title}', data.data.title);
						template = template.replace('{description}', data.data.description);
						template = template.replace('{additional}', data.data.additional);

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

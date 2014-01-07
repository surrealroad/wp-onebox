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
					title ="",
					description ="";
				if($(this).data("title")) title = $(this).data("title");
				if($(this).data("description")) description = $(this).data("description");

				var	requesturl = OneboxParams.renderURL
						+ (OneboxParams.renderURL.indexOf('?') != -1 ? "&onebox_url=" : "?onebox_url=") + encodeURIComponent(url)
						+ "&onebox_title=" + encodeURIComponent(title)
						+ "&onebox_description=" + encodeURIComponent(description);
				//console.log(requesturl);

				$.getJSON(requesturl, function (data) {
					//console.log(data.data); //uncomment this for debug
					if(data.data) {
						if(data.data.displayurl) url = data.data.displayurl;
						else url = data.data.url;

						var template = OneboxParams.template;

						if(OneboxParams.dark) data.classes +=' dark';

						template = template.replace(/{url}/g, url);
						template = template.replace(/{class}/g, data.classes);
						if(data.data.favicon) template = template.replace(/{favicon}/g, '<img src="' + data.data.favicon + '" class="onebox-favicon"/>');
						else template = template.replace(/{favicon}/g, '');
						template = template.replace(/{sitename}/g, data.data.sitename);
						if(data.data.image) template = template.replace(/{image}/g, '<img src="' + data.data.image + '" class="onebox-thumbnail"/>');
						else template = template.replace(/{image}/g, '');
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
	if(OneboxParams.selector) jQuery(OneboxParams.selector).onebox();
});

// attempt to leverage the wp.mce api

( function( $ ) {
	wp.mce.views.register( 'onebox', {
		type: "onebox",

		overlay: true,

		View: {

			initialize: function(options) {
				this.shortcode = options.shortcode;
				this.url = options.shortcode.attrs.named.url;
				this.fetch();
				console.log("initialised");
			},

			loadingPlaceholder: function(){
				console.log("loadingPlaceholder");
				return '<a href="'+this.url+'">Link</a>';
			},

			fetch: function() {
				console.log("fetch");
				this.onebox = $('<div class="onebox-container"><a href="'+this.url+'">Link</a></div>').onebox();
				var t = this;
				this.onebox[0].dfd.done(function(){t.render();});
			},

			getHtml: function() {
				console.log("getHTML");
				//console.log(this.onebox[0].state);
				if(this.onebox[0].state != "done") return '';
				return this.onebox.html();
			}
		},

		/**
		* Called when a TinyMCE view is clicked for editing.
		* - Parses the shortcode out of the element's data attribute
		* - Calls the `edit` method on the shortcode model
		* - Launches the model window
		* - Bind's an `update` callback which updates the element's data attribute
		*   re-renders the view
		*
		* @param {HTMLElement} node
		*/
		edit: function( node ) {
			console.log("edit");
		}

	});
}(jQuery));
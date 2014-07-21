// deprecated
/* onebox tinymce plugin to render live previews (can be switched off) */

// instantiate plugin
tinymce.PluginManager.add( 'oneboxPreview', function( editor ) {

	// overwrite template to include comment
	OneboxParams.template += '<!--onebox-container-end-->';

	// convert from shortcode to html
	function fromShortcode( content ) {
		// http://regexr.com/395l4
		// http://stackoverflow.com/questions/24769465/how-can-i-use-regex-to-match-a-string-without-double-characters
		return content.replace(/\[(?!\[)onebox\ .*url="(.*)"\](?!\])/g, function(shortcode, url) {
			return '<div class="onebox-container render mceNonEditable" data-shortcode="'+window.encodeURIComponent(shortcode)+'" contenteditable="false"><a href="'+url+'">Link</a><!--onebox-container-end--></div>';
		});
	}

	function toShortcode( content ) {
		// http://regexr.com/395ov
		return content.replace(/<div class="onebox-container(?:.*?)data-shortcode="(.*?)"(.*?)<!--onebox-container-end--><\/div>/g, function(container, shortcode) {
			return window.decodeURIComponent(shortcode);
		});
	}

	editor.setOnebox = function( content ) {
		return fromShortcode( content );
	};
	//replace shortcode before editor content set
	editor.on('BeforeSetContent', function(event) {
		if ( event.format !== 'raw' ) {
			event.content = editor.setOnebox( event.content );
		}
	});

	// render the onebox previews as required
	editor.on('PostProcess', function(event) {
		// get jQuery from parent window
		$ = editor.getWin().parent.jQuery;
		// apply onebox plugin to tinyMCE content, make sure it only renders once
		$(".onebox-container.render", editor.getDoc()).removeClass("render").onebox().on("click", function(event){
			event.preventDefault();
		});

		// convert back to shortcode
		if(event.get) event.content = toShortcode( event.content );
	});
});
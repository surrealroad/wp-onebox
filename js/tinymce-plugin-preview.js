/* onebox tinymce plugin */

// instantiate plugin
tinymce.PluginManager.add( 'oneboxPreview', function( editor ) {

	// render live previews
	function renderOnebox( content ) {
		// http://regexr.com/395l4
		// http://stackoverflow.com/questions/24769465/how-can-i-use-regex-to-match-a-string-without-double-characters
		return content.replace(/\[(?!\[)onebox\ .*url="(.*)"\](?!\])/g, function(a, url){
			return '<div class="onebox-container render"><a href="'+url+'">Link</a></div>';
		});
	}
	editor.wpSetOnebox = function( content ) {
		return renderOnebox( content );
	};
	//replace shortcode before editor content set
	editor.on('BeforeSetContent', function(event) {
		if ( event.format !== 'raw' ) {
			event.content = editor.wpSetOnebox( event.content );
		}
	});

	// render the onebox previews as required
	editor.on('PostProcess', function(event) {
		// get jQuery from parent window
		$ = editor.getWin().parent.jQuery;
		// apply onebox plugin to tinyMCE content, make sure it only renders once
		$(".onebox-container.render", editor.getDoc()).removeClass("render").onebox();
	});
});
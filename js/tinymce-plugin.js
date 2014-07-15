/* onebox tinymce plugin */

// instantiate plugin
tinymce.PluginManager.add( 'onebox', function( editor ) {

	// create buttons
	editor.addButton( 'onebox', {
		icon: 'onebox',
		tooltip: 'Insert/edit onebox link',
		cmd: 'onebox',

		onPostRender: function() {

		}
	});
	editor.addButton( 'unOnebox', {
		icon: 'unlink',
		tooltip: 'Remove onebox link',
		cmd: 'unOnebox',

		onPostRender: function() {

		}
	});

	// render live previews
	function renderOnebox( content ) {
		// http://regexr.com/395l4
		// http://stackoverflow.com/questions/24769465/how-can-i-use-regex-to-match-a-string-without-double-characters
		return content.replace(/\[(?!\[)onebox\ .*url="(.*)"\](?!\])/g, function(a, url){
			return '<div class="onebox-container"><a href="'+url+'">Link</a></div>';
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
});
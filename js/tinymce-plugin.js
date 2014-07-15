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
	function parseOnebox( content ) {
		return content.replace(/\[onebox\ .*url="(.*)".*\]/g, function(url){
			return '<a href="'+url+'">Link</a>';
		}
		);
	}
	editor.wpSetOnebox = function( content ) {
		return parseOnebox( content );
	};
	//replace shortcode before editor content set
	editor.on('BeforeSetContent', function(event) {
		if ( event.format !== 'raw' ) {
			event.content = editor.wpSetOnebox( event.content );
		}
	});
});
// http://www.wpexplorer.com/wordpress-tinymce-tweaks/
(function() {
    tinymce.create('tinymce.plugins.oneboxEditor', {
        init : function(editor, url) {

            editor.addButton('oneboxButton', {
                title : 'Insert Onebox',
                text: 'Onebox',
                cmd : 'oneboxAdd',
                icon : false
            });

            function oneboxForm(editor, ui, options) {
				editor.windowManager.open( {
					title: options.label,
					body: [
						{
							type: 'textbox',
							name: 'url',
							label: 'URL',
							value: options.url
						},
						{
							type: 'textbox',
							name: 'title',
							label: 'Optional title',
							value: options.title
						},
						{
							type: 'textbox',
							name: 'description',
							label: 'Optional description',
							value: options.description,
							multiline: true,
							minWidth: 300,
							minHeight: 100
						}
					],
					onsubmit: function( e ) {
						console.log(e);
						var url = ' url="'+e.data.url+'"',
							title = '',
							description = '';

						if(e.data.title) title = ' title="' + e.data.title + '"';
						if(e.data.description) description = ' description="' + e.data.description + '"';
						var shortcodeString = '[onebox' + url + title + description + ']';

						if(options.callback) options.callback(shortcodeString);
						else editor.insertContent(shortcodeString);
					}
				});
            }

            editor.addCommand('oneboxAdd', function(ui) {
				return oneboxForm(editor, ui, {url: "", title: "", description: "", label: 'Insert Onebox'});
            });

            editor.addCommand('oneboxEditLink', function(ui, options) {
				options.label = 'Edit Onebox';
				return oneboxForm(editor, ui, options);
            });
        }
    });
    // Register plugin
    tinymce.PluginManager.add( 'oneboxEditor', tinymce.plugins.oneboxEditor );
})();
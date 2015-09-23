( function() {
	tinymce.PluginManager.add( 'essb_ctt', function( editor, url ) {

		// Add a button that opens a window
		editor.addButton( 'essb_ctt', {

			text: '',
			tooltip: 'Easy Social Share Buttons: Click To Tweet',
			icon: 'essb-ctt-tweet',
			onclick: function() {
				// Open window
				editor.windowManager.open( {
					title: 'Easy Social Share Buttons: Click To Tweet Shortcode Generator',
					body: [
						{
							type: 'textbox',
							name: 'tweet',
							label: 'Tweetable Quote',
							multiline : true,
							minHeight : 60
						},
						{
							type: 'checkbox',
							checked: true,
							name: 'viamark',
							value: true,
							text: 'Add \"via @YourTwitterName\" to this tweet',
							label: 'Include "via"?',
						},
						{
							type: 'textbox',
							name: 'twitteruser',
							label: 'Via Twitter User'
						},
						{
							type: 'checkbox',
							checked: true,
							name: 'addhastags',
							value: true,
							text: 'Add \"#hashtag\" to this tweet',
							label: 'Include "hashtags"?',
						},
						{
							type: 'textbox',
							name: 'twitterhastags',
							label: 'Hashtags separated with , (comma)'
						}
						
					],
					width: 500,
					height: 220,
					onsubmit: function( e ) {

						// bail without tweet text
						if ( e.data.tweet === '' ) {
							return;
						}

						// build my content
						var essbcttBuild   = '';

						// set initial
						essbcttBuild  += '[easy-tweet tweet="' + e.data.tweet + '"';

						// check for via
						if ( e.data.viamark === false ) {
							essbcttBuild  += ' via="no"';
						}
						
						if (e.data.twitteruser != '') {
							essbcttBuild += ' user="'+e.data.twitteruser+'"';
						}

						if ( e.data.addhastags === false ) {
							essbcttBuild  += ' usehashtags="no"';
						}
						
						if (e.data.twitterhastags != '') {
							essbcttBuild += ' hashtags="'+e.data.twitterhastags+'"';
						}
						
						// close it up
						essbcttBuild  += ']';

						// Insert content when the window form is submitted
						editor.insertContent( essbcttBuild );
					}
				});
			}
		});
	});
})();
jQuery( function( $ )
{
	function instapage_ajax_call( form_id, param_action, param_method, param_params )
	{
		$.post(
			INSTAPAGE.ajax_url,
			{
				'action': 'instapage',
				'data': {
					action: param_action,
					method: param_method,
					params: param_params
				}
			},
			function( response )
			{
				response = $.parseJSON( response );
				console.log( 'DEBUG: ' + response.debug );
				console.log( response.data );

				$( form_id ).find( 'input, select, textarea' ).parent( '.form-group' ).removeClass( 'has-error' ).removeClass( 'has-feedback' );
				$( form_id ).find( '.form-control-feedback, .help-block' ).remove();
				$( form_id ).find( '.alert' ).remove();

				if ( response.data.errors )
				{
					$( form_id ).prepend( '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' + response.data.html + '</div>' );

					$.each( response.data.errors, function( field_name, error_text )
					{
						$( form_id ).find( '*[name="'+field_name+'"]' ).parent( '.form-group' ).addClass( 'has-error' ).addClass( 'has-feedback' ) ;
						$( form_id ).find( '*[name="' + field_name + '"]' ).parent( '.form-group' ).append( '<span class="glyphicon glyphicon-remove form-control-feedback"></span><div class="help-block">' + error_text + '</div>' );
					});
				}
				else {

					$( form_id ).prepend( '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' + response.data.html + '</div>' );
				}

				$( form_id ).find( '.ajax-loader' ).remove();
			}
		);

		return false;
	}

	$( 'button[role="ajax-submit"]' ).click( function( e )
	{
		e.preventDefault();

		$( this ).append( '<i class="ajax-loader"></i>' );

		var formaction = $( this ).attr( 'formaction' ).split( '/' );
		var action = formaction[0];
		var method = formaction[1];
		var form_id = '#' + $( this ).attr( 'form' );
		var params = $( form_id ).serializeArray();

		instapage_ajax_call( form_id, action, method, params );
	});
}) ;
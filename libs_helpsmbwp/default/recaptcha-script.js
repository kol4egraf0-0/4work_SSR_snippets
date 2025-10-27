( function() {
	document.addEventListener( 'DOMContentLoaded', function( event ) {
		var recaptcha_execute = function() {
			grecaptcha.execute('токен_тута', { action: 'callback' } ).then( function( token ) {
				var event = new CustomEvent( 'exec', {
					detail: {
						token: token,
					},
				} );
				document.dispatchEvent( event );
			} );
		};

		grecaptcha.ready(
			recaptcha_execute
		);

		document.addEventListener( 'submit',
			recaptcha_execute
		);
		
	} );

	document.addEventListener( 'exec', function( event ) {
		var fields = document.querySelectorAll(
			"form input[name='recaptcha_response']"
		);

		for ( var i = 0; i < fields.length; i++ ) {
			var field = fields[ i ];
			field.setAttribute( 'value', event.detail.token );
		}
	} );

} )();

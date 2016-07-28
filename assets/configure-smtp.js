jQuery( function($) {
	$('#use_gmail').change(function(e) {
		if ( $('#use_gmail').is(':checked') ) {
			$('#host').val( c2c_configure_smtp.host );
			$('#port').val( c2c_configure_smtp.port );
			$('#smtp_auth').prop( 'checked', c2c_configure_smtp.checked );
			$('#smtp_secure').val( c2c_configure_smtp.smtp_secure );
			if ( ! $('#smtp_user').val().match(/.+@gmail.com$/) ) {
				$('#smtp_user').val('USERNAME@gmail.com').focus().get(0).setSelectionRange(0,8);
			}
			alert( c2c_configure_smtp.alert );
			return true;
		}
	});
});

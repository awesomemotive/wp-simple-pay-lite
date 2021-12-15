/* global grecaptcha */

export function createToken( action ) {
	if ( ! window.simpayGoogleRecaptcha ) {
		return Promise.resolve( '' );
	}

	const { siteKey, i18n } = window.simpayGoogleRecaptcha;

	return new Promise( ( resolve, reject ) => {
		try {
			grecaptcha
				.execute( siteKey, {
					action,
				} )
				.then( ( token ) => resolve( token ) );
		} catch ( error ) {
			reject( i18n.invalid );
		}
	} );
}

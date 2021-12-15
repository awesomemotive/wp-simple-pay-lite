// @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/isInteger#Polyfill
Number.isInteger =
	Number.isInteger ||
	function ( value ) {
		return (
			typeof value === 'number' &&
			isFinite( value ) &&
			Math.floor( value ) === value
		);
	};

// @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/isSafeInteger#Polyfill
Number.isSafeInteger =
	Number.isSafeInteger ||
	function ( value ) {
		return (
			Number.isInteger( value ) &&
			Math.abs( value ) <= Number.MAX_SAFE_INTEGER
		);
	};

// @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/MAX_SAFE_INTEGER#Polyfill
if ( ! Number.MAX_SAFE_INTEGER ) {
	Number.MAX_SAFE_INTEGER = 9007199254740991; // Math.pow(2, 53) - 1;
}

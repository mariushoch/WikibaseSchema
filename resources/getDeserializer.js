( function () {
	'use strict';
	var SchemaDeserializer = require( './serialization/SchemaDeserializer.js' );

	module.exports = function () {
		return new SchemaDeserializer();
	};
}() );

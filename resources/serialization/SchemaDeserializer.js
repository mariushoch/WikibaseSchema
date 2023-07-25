( function ( wb, util ) {
	'use strict';

	var SERIALIZER = require( 'wikibase.serialization' ),
		PARENT = SERIALIZER.Deserializer,
		Schema = require( '../datamodel/Schema.js' );

	/**
	 * @class SchemaDeserializer
	 * @extends SERIALIZER.Deserializer
	 * @license GNU GPL v2+
	 *
	 * @constructor
	 */
	module.exports = util.inherit( 'WbSchemaDeserializer', PARENT, {
		/**
		 * @inheritdoc
		 *
		 * @return {wikibase.schema.datamodel.Schema}
		 *
		 * @throws {Error} if serialization does not resolve to a serialized Schema.
		 */
		deserialize: function ( serialization ) {
			if ( serialization.type !== Schema.TYPE ) {
				throw new Error( 'Serialization does not resolve to a Schema' );
			}

			var fingerprintDeserializer = new SERIALIZER.FingerprintDeserializer();

			return new Schema(
				serialization.id,
				fingerprintDeserializer.deserialize( serialization ),
				serialization.schemaText
			);
		},

	} );

}( wikibase, util ) );

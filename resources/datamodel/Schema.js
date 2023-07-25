( function ( util ) {
	'use strict';

	var datamodel = require( 'wikibase.datamodel' ),
		PARENT = datamodel.Entity;

	/**
	 * @class wikibase.schema.datamodel.Schema
	 * @extends datamodel.Entity
	 * @license GNU GPL v2+
	 *
	 * @constructor
	 *
	 * @param {string} schemaId
 	 * @param {Fingerprint|null} [fingerprint=new Fingerprint()]
	 * @param {string} [schemaText]
	 *
	 * @throws {Error} if a required parameter is not specified properly.
	 */
	var SELF = util.inherit(
		'WbDataModelSchema',
		PARENT,
		function( entityId, fingerprint, schemaText ) {
			fingerprint = fingerprint || new datamodel.Fingerprint();
	
			if(
				typeof entityId !== 'string'
				|| !( fingerprint instanceof datamodel.Fingerprint )
			) {
				throw new Error( 'Required parameter(s) missing or not defined properly' );
			}
	
			this._id = entityId;
			this._fingerprint = fingerprint;
			this._schemaText = schemaText;
		},
	{
		/**
		 * @property {string}
		 * @private
		 */
		_schemaText: null,

		/**
		 * @return {boolean}
		 */
		isEmpty: function() {
			return this._fingerprint.isEmpty() && this._schemaText === '';
		},
	
		/**
		 * @param {*} schema
		 * @return {boolean}
		 */
		equals: function( schema ) {
			return schema === this
				|| schema instanceof SELF
					&& this._id === schema.getId()
					&& this._schemaText === schema.getSchemaText()
					&& this._fingerprint.equals( schema.getFingerprint() );
		}
	} );
	
	/**
	 * @inheritdoc
	 * @property {string} [TYPE='schema']
	 * @static
	 */
	SELF.TYPE = 'schema';
	
	module.exports = SELF;
	
	}( util ) );
	
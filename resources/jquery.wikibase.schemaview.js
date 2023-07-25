( function () {
	'use strict';

	var PARENT = $.wikibase.entityview;

	/**
	 * View for displaying a Wikibase `Schema`.
	 *
	 * @see wikibase.datamodel.Schema
	 * @class jQuery.wikibase.schemaview
	 * @extends jQuery.wikibase.entityview
	 * @license GPL-2.0-or-later
	 * @author H. Snater < mediawiki@snater.com >
	 *
	 * @param {Object} options
	 * @param {Function} options.buildStatementGroupListView
	 *
	 * @constructor
	 */
	$.widget( 'wikibase.schemaview', PARENT, {
		/**
		 * @inheritdoc
		 * @protected
		 */
		options: {
		},

		/**
		 * @inheritdoc
		 * @protected
		 */
		_create: function () {
			this._createEntityview();
		},

	} );

	$.wikibase.entityview.TYPES.push( $.wikibase.schemaview.prototype.widgetName );

}() );

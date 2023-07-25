<?php

declare( strict_types = 1 );

namespace Wikibase\Schema;

/**
 * MediaWiki hook handlers for the Wikibase Lexeme extension.
 *
 * @license GPL-2.0-or-later
 * @author Amir Sarabadani <ladsgroup@gmail.com>
 */
class WikibaseSchemaHooks {

	/**
	 * Hook to register the schema and other entity namespaces for EntityNamespaceLookup.
	 *
	 * @param int[] $entityNamespacesSetting
	 */
	public static function onWikibaseRepoEntityNamespaces( array &$entityNamespacesSetting ) {
		$entityNamespacesSetting['schema'] = 642;
	}

	/**
	 * Adds the definition of the schema entity type to the definitions array Wikibase uses.
	 *
	 * @see WikibaseSchema.entitytypes.php
	 * @see WikibaseSchema.entitytypes.repo.php
	 *
	 * @param array[] $entityTypeDefinitions
	 */
	public static function onWikibaseRepoEntityTypes( array &$entityTypeDefinitions ) {
		$entityTypeDefinitions = array_merge(
			$entityTypeDefinitions,
			wfArrayPlus2d(
				require __DIR__ . '/../WikibaseSchema.entitytypes.repo.php',
				require __DIR__ . '/../WikibaseSchema.entitytypes.php'
			)
		);
	}

	/**
	 * Adds the definition of the schema entity type to the definitions array Wikibase uses.
	 *
	 * @see WikibaseSchema.entitytypes.php
	 *
	 * @note This is bootstrap code, it is executed for EVERY request. Avoid instantiating
	 * objects or loading classes here!
	 *
	 * @param array[] $entityTypeDefinitions
	 */
	public static function onWikibaseClientEntityTypes( array &$entityTypeDefinitions ) {
		$entityTypeDefinitions = array_merge(
			$entityTypeDefinitions,
			require __DIR__ . '/../WikibaseSchema.entitytypes.php'
		);
	}

}

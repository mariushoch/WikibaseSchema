<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\DataAccess;

use InvalidArgumentException;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

/**
 * @license GPL-2.0-or-later
 */
class DatabaseSchemaUpdater implements LoadExtensionSchemaUpdatesHook {

	public function onLoadExtensionSchemaUpdates( $updater ): void {
		$db = $updater->getDB();
		$type = $db->getType();

		if ( $type !== 'mysql' && $type !== 'sqlite' && $type !== 'postgres' ) {
			wfWarn( "Database type '$type' is not supported by the WikibaseSchema." );
			return;
		}

		// TODO: How/where is WikibaseRepo creating the wbt_item_terms table?
	}

	private function getScriptPath( $name, $requestedDbType ) {
		$dbTypes = [
			$requestedDbType,
			'mysql',
		];

		foreach ( $dbTypes as $dbType ) {
			$path = __DIR__ . '/../../sql/' . $dbType . '/' . $name . '.sql';

			if ( file_exists( $path ) ) {
				return $path;
			}
		}

		throw new InvalidArgumentException( "Could not find schema script '$name'" );
	}
}

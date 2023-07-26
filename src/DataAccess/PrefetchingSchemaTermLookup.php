<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\DataAccess;

use Wikibase\Lib\Store\Sql\Terms\NormalizedTermStorageMapping;
use Wikibase\Lib\Store\Sql\Terms\PrefetchingEntityTermLookupBase;
use Wikibase\Schema\Domain\Model\Schema;
use Wikibase\Schema\Domain\Model\SchemaId;

/**
 * @license GPL-2.0-or-later
 */
class PrefetchingSchemaTermLookup extends PrefetchingEntityTermLookupBase {

	protected $entityIdClass = SchemaId::class;
	protected $statsPrefix = 'PrefetchingSchemaTermLookup';

	protected function makeMapping(): NormalizedTermStorageMapping {
		return new NormalizedTermStorageMapping( 'wbst', 'wbt_schema_terms', Schema::ENTITY_TYPE );
	}
}

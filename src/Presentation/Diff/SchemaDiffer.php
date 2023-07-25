<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\Presentation\Diff;

use Diff\Differ\MapDiffer;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Services\Diff\EntityDiff;
use Wikibase\DataModel\Services\Diff\EntityDifferStrategy;
use Wikibase\Schema\Domain\Model\Schema;

/**
 * @license GPL-2.0-or-later
 */
class SchemaDiffer implements EntityDifferStrategy {

	private MapDiffer $recursiveMapDiffer;

	public function __construct() {
		$this->recursiveMapDiffer = new MapDiffer( true );
	}

	public function canDiffEntityType( $entityType ): bool {
		return $entityType === 'schema';
	}

	public function diffEntities( EntityDocument $from, EntityDocument $to ): EntityDiff {
		if ( !( $from instanceof Schema ) || !( $to instanceof Schema ) ) {
			throw new InvalidArgumentException( '$from and $to must be instances of Schema' );
		}

		$diffOps = $this->diffSchemaArrays(
			$this->toDiffArray( $from ),
			$this->toDiffArray( $to )
		);

		// FIXME: do schema text diff here?

		return new EntityDiff( $diffOps );
	}

	public function getConstructionDiff( EntityDocument $entity ): EntityDiff {
		if ( !( $entity instanceof Schema ) ) {
			throw new InvalidArgumentException( '$entity must be an instance of Schema' );
		}

		$diffOps = $this->diffSchemaArrays( [], $this->toDiffArray( $entity ) );

		// FIXME: do schema text diff here?
		return new EntityDiff( $diffOps );
	}

	public function getDestructionDiff( EntityDocument $entity ): EntityDiff {
		if ( !( $entity instanceof Schema ) ) {
			throw new InvalidArgumentException( '$entity must be an instance of Schema' );
		}

		$diffOps = $this->diffSchemaArrays( $this->toDiffArray( $entity ), [] );

		// FIXME: do schema text diff here?
		return new EntityDiff( $diffOps );
	}

	private function diffSchemaArrays( array $from, array $to ): array {
		return $this->recursiveMapDiffer->doDiff( $from, $to );
	}

	private function toDiffArray( Schema $schema ): array {
		$array = [];

		$array['aliases'] = $schema->getAliasGroups()->toTextArray();
		$array['label'] = $schema->getLabels()->toTextArray();
		$array['description'] = $schema->getDescriptions()->toTextArray();
		$array['schemaText'] = $schema->getSchemaText();

		return $array;
	}
}

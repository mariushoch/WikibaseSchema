<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\Presentation\Diff;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Services\Diff\EntityDiff;
use Wikibase\DataModel\Services\Diff\EntityPatcherStrategy;
use Wikibase\DataModel\Services\Diff\Internal\FingerprintPatcher;
use Wikibase\Schema\Domain\Model\Schema;

/**
 * @license GPL-2.0-or-later
 */
class SchemaPatcher implements EntityPatcherStrategy {

	private FingerprintPatcher $fingerprintPatcher;

	public function __construct() {
		$this->fingerprintPatcher = new FingerprintPatcher();
	}

	/**
	 * @inheritDoc
	 */
	public function canPatchEntityType( $entityType ): bool {
		return $entityType === 'schema';
	}

	/**
	 * @inheritDoc
	 */
	public function patchEntity( EntityDocument $entity, EntityDiff $patch ): void {
		if ( !( $entity instanceof Schema ) ) {
			throw new InvalidArgumentException( '$entity must be an instance of Schema' );
		}
		$this->fingerprintPatcher->patchFingerprint( $entity->getFingerprint(), $patch );
		/**
		 * FIXME: patch schema text here
		 * @see \EntitySchema\Services\Diff\SchemaPatcher
		 */
	}
}

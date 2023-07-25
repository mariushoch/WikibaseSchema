<?php

namespace Wikibase\Schema\Domain\Model;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Term\AliasesProvider;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\DescriptionsProvider;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;
use Wikibase\DataModel\Term\LabelsProvider;
use Wikibase\DataModel\Term\TermList;

/**
 * @license GPL-2.0-or-later
 */
class Schema implements
	EntityDocument,
	FingerprintProvider,
	LabelsProvider,
	DescriptionsProvider,
	AliasesProvider {

	private SchemaId $id;
	private Fingerprint $fingerprint;
	private string $schemaText;

	public const ENTITY_TYPE = 'schema';

	public function __construct( SchemaId $id, Fingerprint $fingerprint, string $schemaText ) {
		$this->id = $id;
		$this->fingerprint = $fingerprint;
		$this->schemaText = $schemaText;
	}

	public function getType(): string {
		return self::ENTITY_TYPE;
	}

	public function getId(): ?SchemaId {
		return $this->id;
	}

	public function setId( $id ): void {
		if ( $id instanceof SchemaId ) {
			$this->id = $id;
		} else {
			throw new InvalidArgumentException(
				'$id must be an instance of LexemeId.'
			);
		}
	}

	public function isEmpty() {
		return $this->fingerprint->isEmpty() && $this->schemaText === '';
	}

	public function equals( $target ): bool {
		if ( $this === $target ) {
			return true;
		}

		return $target instanceof self
			&& $this->fingerprint->equals( $target->fingerprint )
			&& $this->schemaText === $target->schemaText;
	}

	public function copy(): self {
		return clone $this;
	}

	public function getAliasGroups(): AliasGroupList {
		return $this->fingerprint->getAliasGroups();
	}

	public function getDescriptions(): TermList {
		return $this->fingerprint->getDescriptions();
	}

	public function getFingerprint(): Fingerprint {
		return $this->fingerprint;
	}

	public function getLabels(): TermList {
		return $this->fingerprint->getLabels();
	}

}

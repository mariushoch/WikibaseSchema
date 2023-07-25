<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\ChangeOp;

use InvalidArgumentException;
use ValueValidators\Result;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\Lib\Summary;
use Wikibase\Repo\ChangeOp\ChangeOpBase;
use Wikibase\Repo\ChangeOp\ChangeOpResult;
use Wikibase\Repo\ChangeOp\GenericChangeOpResult;
use Wikibase\Schema\Domain\Model\Schema;

/**
 * @license GPL-2.0-or-later
 */
class ChangeOpSchemaText extends ChangeOpBase {

	private string $newSchemaText;

	public function __construct( string $newSchemaText) {
		$this->newSchemaText = $newSchemaText;
	}

	/**
	 * @inheritDoc
	 */
	public function validate( EntityDocument $entity ): Result {
		if ( !( $entity instanceof Schema ) ) {
			throw new InvalidArgumentException( '$entity must be a Schema' );
		}

		return Result::newSuccess();
	}

	/**
	 * @inheritDoc
	 */
	public function apply( EntityDocument $entity, Summary $summary = null ): ChangeOpResult {
		if ( !( $entity instanceof Schema ) ) {
			throw new InvalidArgumentException( '$entity must be a Schema' );
		}

		if ( $this->newSchemaText === $entity->getSchemaText() ) {
			return new GenericChangeOpResult( $entity->getId(), false );
		}

		$oldSchemaText = $entity->getSchemaText();
		if ($oldSchemaText === '' && $this->newSchemaText !== '') {
			$this->updateSummary( $summary, 'add');
		} elseif ($oldSchemaText !== '' && $this->newSchemaText === '') {
			$this->updateSummary( $summary, 'remove');
		} else {
			$this->updateSummary( $summary, 'set');
		}

		$entity->setSchemaText( $this->newSchemaText );

		return new GenericChangeOpResult( $entity->getId(), true );
	}
}

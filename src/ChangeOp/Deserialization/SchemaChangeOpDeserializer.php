<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\ChangeOp\Deserialization;

use Wikibase\Repo\ChangeOp\ChangeOpDeserializer;
use Wikibase\Repo\ChangeOp\ChangeOps;
use Wikibase\Repo\ChangeOp\Deserialization\ChangeOpDeserializerFactory;
use Wikibase\Schema\ChangeOp\ChangeOpSchemaText;

/**
 * @license GPL-2.0-or-later
 */
class SchemaChangeOpDeserializer implements ChangeOpDeserializer {

	private ChangeOpDeserializerFactory $factory;

	public function __construct(ChangeOpDeserializerFactory $factory) {
		$this->factory = $factory;
	}

	/**
	 * @inheritDoc
	 */
	public function createEntityChangeOp( array $changeRequest ) {
		$changeOps = new ChangeOps();

		$changeOps->add( $this->factory
			->getFingerprintChangeOpDeserializer()
			->createEntityChangeOp( $changeRequest )
		);

		if ( isset( $changeRequest['schemaText'] ) ) {
			$changeOps->add( new ChangeOpSchemaText( $changeRequest['schemaText'] ) );
		}

		return $changeOps;
	}
}

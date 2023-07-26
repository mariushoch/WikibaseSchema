<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\MediaWiki\Content;

use EntitySchema\Domain\Model\SchemaId;
use IContextSource;
use Psr\Container\ContainerInterface;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\Repo\Actions\ViewEntityAction;
use Wikibase\Schema\Domain\Model\Schema;
use Wikibase\Lib\Store\EntityContentDataCodec;
use Wikibase\Repo\Content\EntityHandler;
use Wikibase\Repo\Content\EntityHolder;
use Wikibase\Repo\Search\Fields\FieldDefinitions;
use Wikibase\Repo\Validators\EntityConstraintProvider;
use Wikibase\Repo\Validators\ValidatorErrorLocalizer;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Schema\MediaWiki\Content\SchemaContent;

/**
 * @license GPL-2.0-or-later
 * @author Amir Sarabadani <ladsgroup@gmail.com>
 */
class SchemaHandler extends EntityHandler {

	public function __construct(
		EntityContentDataCodec $contentCodec,
		EntityConstraintProvider $constraintProvider,
		ValidatorErrorLocalizer $errorLocalizer,
		EntityIdParser $entityIdParser,
		FieldDefinitions $lexemeFieldDefinitions,
		callable $legacyExportFormatDetector = null
	) {
		parent::__construct(
			SchemaContent::CONTENT_MODEL_ID,
			null, // TODO: this is unused in the parent class and has a TODO to be removed
			$contentCodec,
			$constraintProvider,
			$errorLocalizer,
			$entityIdParser,
			$lexemeFieldDefinitions,
			$legacyExportFormatDetector
		);
	}

	/**
	 * This is intended to be used in the entity types wiring.
	 */
	public static function factory( ContainerInterface $services, IContextSource $context ): self {
		return new self(
			WikibaseRepo::getEntityContentDataCodec( $services ),
			WikibaseRepo::getEntityConstraintProvider( $services ),
			WikibaseRepo::getValidatorErrorLocalizer( $services ),
			WikibaseRepo::getEntityIdParser( $services ),
			WikibaseRepo::getFieldDefinitionsFactory( $services )
				->getFieldDefinitionsByType( Schema::ENTITY_TYPE ),
		);
	}

	/**
	 * @see ContentHandler::getActionOverrides
	 */
	public function getActionOverrides(): array {
		// TODO
		return [
			'view' => ViewEntityAction::class,
		];
	}

	public function makeEmptyEntity(): Schema {
		// XXX: Should text be nullable instead?
		return new Schema( null, new Fingerprint(), '' );
	}

	/** @inheritDoc */
	public function supportsRedirects(): bool {
		return false;
	}

	/**
	 * @see EntityHandler::newEntityContent
	 */
	protected function newEntityContent( EntityHolder $entityHolder = null ): SchemaContent {
		return new SchemaContent( $entityHolder );
	}

	/**
	 * @param string $id
	 */
	public function makeEntityId( $id ): SchemaId {
		return new SchemaId( $id );
	}

	public function getEntityType(): string {
		return Schema::ENTITY_TYPE;
	}

	public function getSpecialPageForCreation(): string {
		return 'NewEntitySchema';
	}

}

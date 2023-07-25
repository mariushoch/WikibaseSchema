<?php

use MediaWiki\MediaWikiServices;
use Wikibase\DataModel\Deserializers\TermDeserializer;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Serializers\SerializerFactory;
use Wikibase\DataModel\Services\EntityId\EntityIdFormatter;
use Wikibase\Lexeme\DataAccess\ChangeOp\Validation\LexemeTermLanguageValidator;
use Wikibase\Lexeme\DataAccess\ChangeOp\Validation\LexemeTermSerializationValidator;
use Wikibase\Lexeme\Domain\EntityReferenceExtractors\FormsStatementEntityReferenceExtractor;
use Wikibase\Lexeme\Domain\EntityReferenceExtractors\GrammaticalFeatureItemIdsExtractor;
use Wikibase\Lexeme\Domain\EntityReferenceExtractors\LanguageItemIdExtractor;
use Wikibase\Lexeme\Domain\EntityReferenceExtractors\LexicalCategoryItemIdExtractor;
use Wikibase\Lexeme\Domain\EntityReferenceExtractors\SensesStatementEntityReferenceExtractor;
use Wikibase\Lexeme\Domain\Model\Lexeme;
use Wikibase\Lexeme\MediaWiki\Content\LexemeContent;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\EditSenseChangeOpDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\FormChangeOpDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\FormIdDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\FormListChangeOpDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\GlossesChangeOpDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\LanguageChangeOpDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\LemmaChangeOpDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\LexemeChangeOpDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\LexicalCategoryChangeOpDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\SenseChangeOpDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\SenseIdDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\SenseListChangeOpDeserializer;
use Wikibase\Lexeme\Presentation\ChangeOp\Deserialization\ValidationContext;
use Wikibase\Lexeme\Presentation\View\LexemeViewFactory;
use Wikibase\Lexeme\WikibaseLexemeServices;
use Wikibase\Lib\EntityTypeDefinitions as Def;
use Wikibase\Lib\Store\LookupConstants;
use Wikibase\Lib\TermLanguageFallbackChain;
use Wikibase\Repo\Api\EditEntity;
use Wikibase\Repo\ChangeOp\Deserialization\ClaimsChangeOpDeserializer;
use Wikibase\Repo\Diff\BasicEntityDiffVisualizer;
use Wikibase\Repo\Diff\ClaimDiffer;
use Wikibase\Repo\Diff\ClaimDifferenceVisualizer;
use Wikibase\Repo\EntityReferenceExtractors\EntityReferenceExtractorCollection;
use Wikibase\Repo\EntityReferenceExtractors\StatementEntityReferenceExtractor;
use Wikibase\Repo\Store\Store;
use Wikibase\Repo\Validators\EntityExistsValidator;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Schema\Serialization\SchemaSerializer;

return [
	'schema' => [
		Def::STORAGE_SERIALIZER_FACTORY_CALLBACK => static function ( SerializerFactory $serializerFactory ) {
			return new SchemaSerializer(
				$serializerFactory->newTermListSerializer(),
				$serializerFactory->newAliasGroupListSerializer()
			);
		},
		Def::VIEW_FACTORY_CALLBACK => static function (
			Language $language,
			TermLanguageFallbackChain $termFallbackChain,
			EntityDocument $entity
		) {
			throw new LogicException( 'TODO' );
			$factory = new LexemeViewFactory(
				$language,
				$termFallbackChain
			);

			return $factory->newLexemeView();
		},
		Def::CONTENT_MODEL_ID => LexemeContent::CONTENT_MODEL_ID,
		Def::CONTENT_HANDLER_FACTORY_CALLBACK => static function () {
			$services = MediaWikiServices::getInstance();
			$requestContext = RequestContext::getMain();
			return SchemaHandler::factory( $services, $requestContext );
		},
		Def::ENTITY_FACTORY_CALLBACK => static function () {
			return new Lexeme();
		},
		Def::CHANGEOP_DESERIALIZER_CALLBACK => static function () {
			$services = MediaWikiServices::getInstance();
			$changeOpFactoryProvider = WikibaseRepo::getChangeOpFactoryProvider( $services );
			$statementChangeOpDeserializer = new ClaimsChangeOpDeserializer(
				WikibaseRepo::getExternalFormatStatementDeserializer( $services ),
				$changeOpFactoryProvider->getStatementChangeOpFactory()
			);
			$entityLookup = WikibaseRepo::getStore( $services )->getEntityLookup(
				Store::LOOKUP_CACHING_DISABLED,
				LookupConstants::LATEST_FROM_MASTER
			);
			$itemValidator = new EntityExistsValidator( $entityLookup, 'item' );
			$entityIdParser = WikibaseRepo::getEntityIdParser( $services );
			$stringNormalizer = WikibaseRepo::getStringNormalizer( $services );
			$lexemeChangeOpDeserializer = new LexemeChangeOpDeserializer(
				new LemmaChangeOpDeserializer(
				// TODO: WikibaseRepo should probably provide this validator?
				// TODO: WikibaseRepo::getTermsLanguage is not necessarily the list of language codes
				// that should be allowed as "languages" of lemma terms
					new LexemeTermSerializationValidator(
						new LexemeTermLanguageValidator( WikibaseLexemeServices::getTermLanguages() )
					),
					WikibaseLexemeServices::getLemmaTermValidator( $services ),
					$stringNormalizer
				),
				new LexicalCategoryChangeOpDeserializer(
					$itemValidator,
					$stringNormalizer
				),
				new LanguageChangeOpDeserializer(
					$itemValidator,
					$stringNormalizer
				),
				$statementChangeOpDeserializer,
				new FormListChangeOpDeserializer(
					new FormIdDeserializer( $entityIdParser ),
					new FormChangeOpDeserializer(
						$entityLookup,
						$entityIdParser,
						WikibaseLexemeServices::getEditFormChangeOpDeserializer()
					)
				),
				new SenseListChangeOpDeserializer(
					new SenseIdDeserializer( $entityIdParser ),
					new SenseChangeOpDeserializer(
						$entityLookup,
						$entityIdParser,
						new EditSenseChangeOpDeserializer(
							new GlossesChangeOpDeserializer(
								new TermDeserializer(),
								$stringNormalizer,
								new LexemeTermSerializationValidator(
									new LexemeTermLanguageValidator( WikibaseLexemeServices::getTermLanguages() )
								)
							),
							new ClaimsChangeOpDeserializer(
								WikibaseRepo::getExternalFormatStatementDeserializer(),
								$changeOpFactoryProvider->getStatementChangeOpFactory()
							)
						)
					)
				)
			);
			$lexemeChangeOpDeserializer->setContext(
				ValidationContext::create( EditEntity::PARAM_DATA )
			);
			return $lexemeChangeOpDeserializer;
		},
		Def::ENTITY_DIFF_VISUALIZER_CALLBACK => static function (
			MessageLocalizer $messageLocalizer,
			ClaimDiffer $claimDiffer,
			ClaimDifferenceVisualizer $claimDiffView,
			SiteLookup $siteLookup,
			EntityIdFormatter $entityIdFormatter
		) {
			return new BasicEntityDiffVisualizer(
				$messageLocalizer,
				$claimDiffer,
				$claimDiffView
			);
		},
		Def::ENTITY_SEARCH_CALLBACK => static function ( WebRequest $request ) {
			throw new LogicException( 'TODO' );
		},
		Def::ENTITY_REFERENCE_EXTRACTOR_CALLBACK => static function () {
			$statementEntityReferenceExtractor = new StatementEntityReferenceExtractor(
				WikibaseRepo::getItemUrlParser()
			);
			return new EntityReferenceExtractorCollection( [
				new LanguageItemIdExtractor(),
				new LexicalCategoryItemIdExtractor(),
				new GrammaticalFeatureItemIdsExtractor(),
				$statementEntityReferenceExtractor,
				new FormsStatementEntityReferenceExtractor( $statementEntityReferenceExtractor ),
				new SensesStatementEntityReferenceExtractor( $statementEntityReferenceExtractor ),
			] );
		},
	],
];

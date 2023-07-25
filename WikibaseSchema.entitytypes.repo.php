<?php

use MediaWiki\MediaWikiServices;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Serializers\SerializerFactory;
use Wikibase\DataModel\Services\EntityId\EntityIdFormatter;
use Wikibase\Lib\EntityTypeDefinitions as Def;
use Wikibase\Lib\Store\TitleLookupBasedEntityExistenceChecker;
use Wikibase\Lib\Store\TitleLookupBasedEntityTitleTextLookup;
use Wikibase\Lib\Store\TitleLookupBasedEntityUrlLookup;
use Wikibase\Lib\TermLanguageFallbackChain;
use Wikibase\Repo\Diff\BasicEntityDiffVisualizer;
use Wikibase\Repo\Diff\ClaimDiffer;
use Wikibase\Repo\Diff\ClaimDifferenceVisualizer;
use Wikibase\Repo\EntityReferenceExtractors\EntityReferenceExtractorCollection;
use Wikibase\Repo\ParserOutput\EntityTermsViewFactory;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Schema\ChangeOp\Deserialization\SchemaChangeOpDeserializer;
use Wikibase\Schema\Domain\Model\Schema;
use Wikibase\Schema\MediaWiki\Content\SchemaContent;
use Wikibase\Schema\MediaWiki\Content\SchemaHandler;
use Wikibase\Schema\Presentation\View\SchemaView;
use Wikibase\Schema\Serialization\SchemaSerializer;
use Wikibase\View\Template\TemplateFactory;

return [
	'schema' => [
		Def::STORAGE_SERIALIZER_FACTORY_CALLBACK => static function ( SerializerFactory $serializerFactory ) {
			return new SchemaSerializer(
				$serializerFactory->newTermListSerializer(),
				$serializerFactory->newAliasGroupListSerializer()
			);
		},
		Def::VIEW_FACTORY_CALLBACK => static function(
			Language $language,
			TermLanguageFallbackChain $fallbackChain,
			EntityDocument $entity
		) {
			return new SchemaView(
				TemplateFactory::getDefaultInstance(),
				WikibaseRepo::getLanguageDirectionalityLookup(),
				$language->getCode(),
				( new EntityTermsViewFactory() )
					->newEntityTermsView(
						$entity,
						$language,
						$fallbackChain,
						false // TODO: use modern termbox?
					)
			);
		},
		Def::CONTENT_MODEL_ID => SchemaContent::CONTENT_MODEL_ID,
		Def::CONTENT_HANDLER_FACTORY_CALLBACK => static function () {
			$services = MediaWikiServices::getInstance();
			$requestContext = RequestContext::getMain();
			return SchemaHandler::factory( $services, $requestContext );
		},
		Def::ENTITY_FACTORY_CALLBACK => static function () {
			return new Schema();
		},
		Def::CHANGEOP_DESERIALIZER_CALLBACK => static function () {
			$services = MediaWikiServices::getInstance();
			return new SchemaChangeOpDeserializer(
				WikibaseRepo::getChangeOpDeserializerFactory( $services )
			);
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
			return new EntityReferenceExtractorCollection( [] );
		},
		Def::URL_LOOKUP_CALLBACK => static function () {
			return new TitleLookupBasedEntityUrlLookup( WikibaseRepo::getEntityTitleLookup() );
		},
		Def::EXISTENCE_CHECKER_CALLBACK => static function () {
			$services = MediaWikiServices::getInstance();
			return new TitleLookupBasedEntityExistenceChecker(
					WikibaseRepo::getEntityTitleLookup( $services ),
					$services->getLinkBatchFactory()
			);
		},
		Def::TITLE_TEXT_LOOKUP_CALLBACK => static function () {
			return new TitleLookupBasedEntityTitleTextLookup(
				WikibaseRepo::getEntityTitleLookup()
			);
		},
	],
];

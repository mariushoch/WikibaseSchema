<?php
/**
 * Definition of the lexeme entity type.
 * The array returned by the code below is supposed to be merged into the Repo entity types.
 *
 * @note: Keep in sync with Wikibase
 *
 * @note: This is bootstrap code, it is executed for EVERY request. Avoid instantiating
 * objects or loading classes here!
 *
 * @license GPL-2.0-or-later
 * @author Amir Sarabadani <ladsgroup@gmail.com>
 */

use EntitySchema\Domain\Model\SchemaId;
use Wikibase\DataAccess\NullPrefetchingTermLookup;
use Wikibase\DataModel\Deserializers\DeserializerFactory;
use Wikibase\DataModel\Entity\SerializableEntityId;
use Wikibase\DataModel\Serializers\SerializerFactory;
use Wikibase\Lib\EntityTypeDefinitions as Def;
use Wikibase\Lib\Store\TitleLookupBasedEntityArticleIdLookup;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Schema\Serialization\SchemaDeserializer;
use Wikibase\Schema\Serialization\SchemaSerializer;

return [
	'schema' => [
		Def::ARTICLE_ID_LOOKUP_CALLBACK => static function () {
			return new TitleLookupBasedEntityArticleIdLookup(
				WikibaseRepo::getEntityTitleLookup()
			);
		},
		Def::SERIALIZER_FACTORY_CALLBACK => static function ( SerializerFactory $serializerFactory ) {
			return new SchemaSerializer(
				$serializerFactory->newTermListSerializer(),
				$serializerFactory->newAliasGroupListSerializer()
			);
		},
		Def::DESERIALIZER_FACTORY_CALLBACK => static function ( DeserializerFactory $deserializerFactory ) {
			return new SchemaDeserializer(
				$deserializerFactory->newEntityIdDeserializer(),
				$deserializerFactory->newTermListDeserializer(),
				$deserializerFactory->newAliasGroupListDeserializer(),
			);
		},

		Def::ENTITY_ID_PATTERN => SchemaId::PATTERN,
		Def::ENTITY_ID_BUILDER => static function ( $serialization ) {
			return new SchemaId( $serialization );
		},
		Def::ENTITY_ID_COMPOSER_CALLBACK => static function ( $repositoryName, $uniquePart ) {
			return new SchemaId( SerializableEntityId::joinSerialization( [
				$repositoryName,
				'',
				'L' . $uniquePart
			] ) );
		},

		// Identifier of a resource loader module that, when `require`d, returns a function
		// returning a deserializer
		Def::JS_DESERIALIZER_FACTORY_FUNCTION => 'TODO',
		Def::PREFETCHING_TERM_LOOKUP_CALLBACK => static function () {
			return new NullPrefetchingTermLookup();
		},
	],
];

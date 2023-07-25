<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\Serialization;

use Deserializers\Deserializer;
use Deserializers\TypedObjectDeserializer;
use Wikibase\DataModel\Term\AliasGroupList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\TermList;
use Wikibase\Schema\Domain\Model\Schema;

/**
 * @license GPL-2.0-or-later
 * @author Amir Sarabadani <ladsgroup@gmail.com>
 */
class SchemaDeserializer extends TypedObjectDeserializer {

	/**
	 * @var Deserializer
	 */
	private $entityIdDeserializer;

	/**
	 * @var Deserializer
	 */
	private $termListDeserializer;

	/**
	 * @var Deserializer
	 */
	private $aliasGroupListDeserializer;

	public function __construct(
		Deserializer $entityIdDeserializer,
		Deserializer $termListDeserializer,
		Deserializer $aliasGroupListDeserializer,
	) {
		parent::__construct( 'schema', 'type' );

		$this->entityIdDeserializer = $entityIdDeserializer;
		$this->termListDeserializer = $termListDeserializer;
		$this->aliasGroupListDeserializer = $aliasGroupListDeserializer;
	}

	/**
	 * @see Deserializer::deserialize
	 *
	 * @param array $serialization
	 *
	 * @throws DeserializationException
	 * @return Property
	 */
	public function deserialize( $serialization ): Schema {
		$this->assertCanDeserialize( $serialization );

		return $this->getDeserialized( $serialization );
	}

	/**
	 * @param array $serialization
	 *
	 * @return Property
	 */
	private function getDeserialized( array $serialization ): Schema {
		// TODO: This should also be in its own method
		$schemaText = $serialization['schemaText'];

		$schema = new Schema( null, new Fingerprint(), $schemaText );

		$this->setIdFromSerialization( $serialization, $schema );
		$this->setTermsFromSerialization( $serialization, $schema );

		return $schema;
	}

	private function setIdFromSerialization( array $serialization, Schema $schema ) {
		if ( !array_key_exists( 'id', $serialization ) ) {
			return;
		}

		/** @var PropertyId $id */
		$id = $this->entityIdDeserializer->deserialize( $serialization['id'] );
		$schema->setId( $id );
	}

	private function setTermsFromSerialization( array $serialization, Schema $schema ) {
		if ( array_key_exists( 'labels', $serialization ) ) {
			$this->assertAttributeIsArray( $serialization, 'labels' );
			/** @var TermList $labels */
			$labels = $this->termListDeserializer->deserialize( $serialization['labels'] );
			$schema->getFingerprint()->setLabels( $labels );
		}

		if ( array_key_exists( 'descriptions', $serialization ) ) {
			$this->assertAttributeIsArray( $serialization, 'descriptions' );
			/** @var TermList $descriptions */
			$descriptions = $this->termListDeserializer->deserialize( $serialization['descriptions'] );
			$schema->getFingerprint()->setDescriptions( $descriptions );
		}

		if ( array_key_exists( 'aliases', $serialization ) ) {
			$this->assertAttributeIsArray( $serialization, 'aliases' );
			/** @var AliasGroupList $aliases */
			$aliases = $this->aliasGroupListDeserializer->deserialize( $serialization['aliases'] );
			$schema->getFingerprint()->setAliasGroups( $aliases );
		}
	}
}

<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\Serialization;

use Serializers\DispatchableSerializer;
use Serializers\Exceptions\UnsupportedObjectException;
use Serializers\Serializer;
use Wikibase\Schema\Domain\Model\Schema;

class SchemaSerializer implements DispatchableSerializer {

	/**
	 * @var Serializer
	 */
	private $termListSerializer;

	/**
	 * @var Serializer
	 */
	private $aliasGroupListSerializer;

	public function __construct(
		Serializer $termListSerializer,
		Serializer $aliasGroupListSerializer
	) {
		$this->termListSerializer = $termListSerializer;
		$this->aliasGroupListSerializer = $aliasGroupListSerializer;
	}

	/**
	 * @param mixed $object
	 *
	 * @return boolean
	 */
	public function isSerializerFor( $object ) {
		return $object instanceof Schema;
	}

	/**
	 * @see Serializer::serialize
	 *
	 * @param Schema $object
	 *
	 * @throws SerializationException
	 * @return array
	 */
	public function serialize( $object ): array {
		if ( !$this->isSerializerFor( $object ) ) {
			throw new UnsupportedObjectException(
				$object,
				'SchemaSerializer can only serialize Schema objects.'
			);
		}

		return $this->getSerialized( $object );
	}

	private function getSerialized( Schema $schema ) {
		$serialization = [
			'type' => $schema->getType(),
			'schemaText' => $schema->getSchemaText(),
			// XXX: Format version?
		];

		$this->addIdToSerialization( $schema, $serialization );
		$this->addTermsToSerialization( $schema, $serialization );

		return $serialization;
	}

	private function addIdToSerialization( Schema $schema, array &$serialization ) {
		$id = $schema->getId();

		if ( $id !== null ) {
			$serialization['id'] = $id->getSerialization();
		}
	}

	private function addTermsToSerialization( Schema $schema, array &$serialization ) {
		$fingerprint = $schema->getFingerprint();

		$serialization['labels'] = $this->termListSerializer->serialize( $fingerprint->getLabels() );
		$serialization['descriptions'] =
			$this->termListSerializer->serialize( $fingerprint->getDescriptions() );
		$serialization['aliases'] =
			$this->aliasGroupListSerializer->serialize( $fingerprint->getAliasGroups() );
	}

}

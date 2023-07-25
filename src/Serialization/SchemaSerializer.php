<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\Serialization;

use Serializers\DispatchableSerializer;
use Wikibase\Schema\Domain\Model\Schema;

class SchemaSerializer implements DispatchableSerializer {

	/**
	 * @param mixed $object
	 *
	 * @return boolean
	 */
	public function isSerializerFor( $object ) {
		return $object instanceof Schema;
	}

	public function serialize( $object ) {
		// TODO
	}
}

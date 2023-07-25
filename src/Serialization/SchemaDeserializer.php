<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\Serialization;

use Deserializers\TypedObjectDeserializer;
use Wikibase\Schema\Domain\Model\Schema;

/**
 * @license GPL-2.0-or-later
 * @author Amir Sarabadani <ladsgroup@gmail.com>
 */
class SchemaDeserializer extends TypedObjectDeserializer {

	public function deserialize( $serialization ): Schema {
		// TODO
		return new Schema();
	}
}

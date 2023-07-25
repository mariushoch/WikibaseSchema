<?php

namespace Wikibase\Lexeme\Domain\Model;

use Wikibase\DataModel\Entity\ClearableEntity;
use Wikibase\DataModel\Entity\EntityDocument;

/**
 * Mutable (e.g. the provided StatementList can be changed) implementation of a Lexeme in the
 * lexicographical data model.
 *
 * @see https://www.mediawiki.org/wiki/Extension:WikibaseLexeme/Data_Model#Lexeme
 *
 * @license GPL-2.0-or-later
 */
class Schema implements EntityDocument, ClearableEntity {

}
<?php

namespace Wikibase\Schema\MediaWiki\Content;

use InvalidArgumentException;
use LogicException;
use Title;
use Wikibase\DataModel\Entity\EntityRedirect;
use Wikibase\Schema\Domain\Model\Schema;
use Wikibase\Repo\Content\EntityContent;
use Wikibase\Repo\Content\EntityHolder;

/**
 * @license GPL-2.0-or-later
 */
class SchemaContent extends EntityContent {

	public const CONTENT_MODEL_ID = 'wikibase-schema';

	/**
	 * @var EntityHolder|null
	 */
	private $schemaHolder;

	/**
	 * @var EntityRedirect
	 */
	private $redirect;

	/**
	 * @var Title
	 */
	private $redirectTitle;

	/**
	 * @param EntityHolder|null $schemaHolder
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		EntityHolder $schemaHolder = null,
		EntityRedirect $redirect = null,
		Title $redirectTitle = null
	) {
		parent::__construct( self::CONTENT_MODEL_ID );

		if ( $schemaHolder !== null && $redirect !== null ) {
			throw new InvalidArgumentException(
				'Cannot contain lexeme and be a redirect at the same time'
			);
		}

		$this->schemaHolder = $schemaHolder;
	}

	public static function newFromRedirect( $redirect, $title ) {
		return new self( null, $redirect, $title );
	}

	protected function getIgnoreKeysForFilters() {
		// FIXME: This was the default list of keys as extracted form EntityContent
		// Lexemes should probably have different keys set here but we need to know what
		// is already being used in AbuseFilter on wikidata.org
		// https://phabricator.wikimedia.org/T205254
		return [
			'language',
			'site',
			'type',
			'hash'
		];
	}

	/**
	 * @see EntityContent::getEntity
	 *
	 * @return Schema
	 */
	public function getEntity(): Schema {
		if ( !$this->schemaHolder ) {
			throw new LogicException( 'This content object is empty!' );
		}

		// @phan-suppress-next-line PhanTypeMismatchReturnSuperType
		return $this->schemaHolder->getEntity( Schema::class );
	}

	/**
	 * @see EntityContent::isCountable
	 *
	 * @param bool|null $hasLinks
	 *
	 * @return bool
	 */
	public function isCountable( $hasLinks = null ) {
		return true;
	}

	/**
	 * @see EntityContent::getEntityHolder
	 *
	 * @return EntityHolder|null
	 */
	public function getEntityHolder() {
		return $this->schemaHolder;
	}

	public function getEntityRedirect() {
		return $this->redirect;
	}

	public function getRedirectTarget() {
		return $this->redirectTitle;
	}

	/**
	 * Make text representation of the Lexeme as list of all lemmas and form representations.
	 * @see EntityContent::getTextForSearchIndex()
	 */
	public function getTextForSearchIndex() {
		return 'TODO';
	}

}

<?php

namespace Wikibase\Schema\MediaWiki\Content;

use InvalidArgumentException;
use LogicException;
use MediaWiki\MediaWikiServices;
use Title;
use Wikibase\DataModel\Entity\EntityRedirect;
use Wikibase\Lexeme\Domain\Model\Lexeme;
use Wikibase\Lexeme\Domain\Model\Schema;
use Wikibase\Lexeme\Presentation\Content\LemmaTextSummaryFormatter;
use Wikibase\Repo\Content\EntityContent;
use Wikibase\Repo\Content\EntityHolder;
use Wikimedia\Assert\Assert;

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
	 * @var LemmaTextSummaryFormatter
	 */
	private $summaryFormatter;

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

		$this->summaryFormatter = new LemmaTextSummaryFormatter(
			MediaWikiServices::getInstance()->getContentLanguage()
		);
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
	 * @return Lexeme
	 */
	public function getEntity() {
		if ( !$this->schemaHolder ) {
			throw new LogicException( 'This content object is empty!' );
		}

		// @phan-suppress-next-line PhanTypeMismatchReturnSuperType
		return $this->schemaHolder->getEntity( Lexeme::class );
	}

	/**
	 * @see EntityContent::isCountable
	 *
	 * @param bool|null $hasLinks
	 *
	 * @return bool
	 */
	public function isCountable( $hasLinks = null ) {
		return !$this->isRedirect() && !$this->getEntity()->isEmpty();
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
	 * @see EntityContent::isValid
	 *
	 * @return bool
	 */
	public function isValid() {
		return parent::isValid()
			&& ( $this->isRedirect()
			|| $this->getEntity()->isSufficientlyInitialized() );
	}

	/**
	 * Make text representation of the Lexeme as list of all lemmas and form representations.
	 * @see EntityContent::getTextForSearchIndex()
	 */
	public function getTextForSearchIndex() {
		return 'TODO';
	}

	private function constructAsRedirect( EntityRedirect $redirect, Title $redirectTitle = null ) {
		if ( $redirectTitle === null ) {
			throw new InvalidArgumentException(
				'$redirect and $redirectTitle must both be provided or both be empty.'
			);
		}

		$this->redirect = $redirect;
		$this->redirectTitle = $redirectTitle;
	}

	/**
	 * Returns a textual representation of the content suitable for use in edit summaries and log messages.
	 *
	 * @param int $maxLength maximum length of the summary text
	 * @return string
	 */
	public function getTextForSummary( $maxLength = 250 ) {
		if ( $this->isRedirect() ) {
			return $this->getRedirectText();
		}

		return $this->summaryFormatter->getSummary(
			$this->getEntity()->getLemmas(),
			$maxLength
		);
	}
}

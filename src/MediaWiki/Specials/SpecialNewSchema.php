<?php

namespace Wikibase\Schema\MediaWiki\Specials;

use OutputPage;
use Status;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;
use Wikibase\Lib\SettingsArray;
use Wikibase\Lib\Store\EntityNamespaceLookup;
use Wikibase\Lib\Store\EntityTitleLookup;
use Wikibase\Lib\Summary;
use Wikibase\Repo\CopyrightMessageBuilder;
use Wikibase\Repo\EditEntity\MediaWikiEditEntityFactory;
use Wikibase\Repo\Specials\HTMLForm\HTMLAliasesField;
use Wikibase\Repo\Specials\HTMLForm\HTMLContentLanguageField;
use Wikibase\Repo\Specials\HTMLForm\HTMLTrimmedTextField;
use Wikibase\Repo\Specials\SpecialNewEntity;
use Wikibase\Repo\Specials\SpecialPageCopyrightView;
use Wikibase\Repo\SummaryFormatter;
use Wikibase\Repo\Validators\TermValidatorFactory;
use Wikibase\Repo\Validators\ValidatorErrorLocalizer;
use Wikibase\Schema\Domain\Model\Schema;

/**
 * Page for creating new Wikibase Schemas.
 *
 * @license GPL-2.0-or-later
 * @author John Erling Blad < jeblad@gmail.com >
 */
class SpecialNewSchema extends SpecialNewEntity {
	private const FIELD_LANG = 'lang';
	private const FIELD_LABEL = 'label';
	private const FIELD_DESCRIPTION = 'description';
	private const FIELD_ALIASES = 'aliases';

	/** @var TermValidatorFactory */
	private $termValidatorFactory;

	/** @var ValidatorErrorLocalizer */
	private $errorLocalizer;

	public function __construct(
		array $tags,
		SpecialPageCopyrightView $specialPageCopyrightView,
		EntityNamespaceLookup $entityNamespaceLookup,
		SummaryFormatter $summaryFormatter,
		EntityTitleLookup $entityTitleLookup,
		MediaWikiEditEntityFactory $editEntityFactory,
		TermValidatorFactory $termValidatorFactory,
		ValidatorErrorLocalizer $errorLocalizer,
		bool $isMobileView
	) {
		parent::__construct(
			'NewSchema',
			'createpage',
			$tags,
			$specialPageCopyrightView,
			$entityNamespaceLookup,
			$summaryFormatter,
			$entityTitleLookup,
			$editEntityFactory,
			$isMobileView
		);

		$this->termValidatorFactory = $termValidatorFactory;
		$this->errorLocalizer = $errorLocalizer;
	}

	public static function factory(
		MediaWikiEditEntityFactory $editEntityFactory,
		EntityNamespaceLookup $entityNamespaceLookup,
		EntityTitleLookup $entityTitleLookup,
		bool $isMobileView,
		SettingsArray $repoSettings,
		SummaryFormatter $summaryFormatter,
		TermValidatorFactory $termValidatorFactory,
		ValidatorErrorLocalizer $errorLocalizer
	): self {
		$copyrightView = new SpecialPageCopyrightView(
			new CopyrightMessageBuilder(),
			$repoSettings->getSetting( 'dataRightsUrl' ),
			$repoSettings->getSetting( 'dataRightsText' )
		);

		return new self(
			$repoSettings->getSetting( 'specialPageTags' ),
			$copyrightView,
			$entityNamespaceLookup,
			$summaryFormatter,
			$entityTitleLookup,
			$editEntityFactory,
			$termValidatorFactory,
			$errorLocalizer,
			$isMobileView
		);
	}

	/**
	 * @see SpecialNewEntity::doesWrites
	 *
	 * @return bool
	 */
	public function doesWrites() {
		return true;
	}

	/**
	 * @see SpecialNewEntity::createEntityFromFormData
	 *
	 * @param array $formData
	 *
	 * @return Property
	 */
	protected function createEntityFromFormData( array $formData ) {
		$languageCode = $formData[ self::FIELD_LANG ];

		$fingerprint = new Fingerprint();

		$fingerprint->setLabel( $languageCode, $formData[ self::FIELD_LABEL ] );
		$fingerprint->setDescription( $languageCode, $formData[ self::FIELD_DESCRIPTION ] );
		$fingerprint->setAliasGroup( $languageCode, $formData[ self::FIELD_ALIASES ] );

		return new Schema( null, $fingerprint, '' );
	}

	/**
	 * @see SpecialNewEntity::getFormFields()
	 *
	 * @return array[]
	 */
	protected function getFormFields() {
		return [
			self::FIELD_LANG => [
				'name' => self::FIELD_LANG,
				'class' => HTMLContentLanguageField::class,
				'id' => 'wb-newentity-language',
			],
			self::FIELD_LABEL => [
				'name' => self::FIELD_LABEL,
				'default' => $this->parts[0] ?? '',
				'class' => HTMLTrimmedTextField::class,
				'id' => 'wb-newentity-label',
				'placeholder-message' => 'wikibase-label-edit-placeholder',
				'label-message' => 'wikibase-newentity-label',
			],
			self::FIELD_DESCRIPTION => [
				'name' => self::FIELD_DESCRIPTION,
				'default' => $this->parts[1] ?? '',
				'class' => HTMLTrimmedTextField::class,
				'id' => 'wb-newentity-description',
				'placeholder-message' => 'wikibase-description-edit-placeholder',
				'label-message' => 'wikibase-newentity-description',
			],
			self::FIELD_ALIASES => [
				'name' => self::FIELD_ALIASES,
				'class' => HTMLAliasesField::class,
				'id' => 'wb-newentity-aliases',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getLegend() {
		return $this->msg( 'wikibase-newproperty-fieldset' );
	}

	/**
	 * @see SpecialNewEntity::getWarnings
	 *
	 * @return string[]
	 */
	protected function getWarnings() {
		if ( !$this->getUser()->isRegistered() ) {
			return [
				$this->msg(
					'wikibase-anonymouseditwarning',
					$this->msg( 'wikibase-entity-property' )
				)->parse(),
			];
		}

		return [];
	}

	/**
	 * @param array $formData
	 *
	 * @return Status
	 */
	protected function validateFormData( array $formData ) {
		$status = Status::newGood();

		if ( $formData[ self::FIELD_LABEL ] == ''
			 && $formData[ self::FIELD_DESCRIPTION ] == ''
			 && $formData[ self::FIELD_ALIASES ] === []
		) {
			$status->fatal( 'wikibase-newproperty-insufficient-data' );
		}

		if ( $formData[ self::FIELD_LABEL ] !== '' &&
			$formData[ self::FIELD_LABEL ] === $formData[ self::FIELD_DESCRIPTION ]
		) {
			$status->fatal( 'wikibase-newproperty-same-label-and-description' );
		}

		if ( $formData[self::FIELD_LABEL] != '' ) {
			$validator = $this->termValidatorFactory->getLabelValidator( $this->getEntityType() );
			$result = $validator->validate( $formData[self::FIELD_LABEL] );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );

			$validator = $this->termValidatorFactory->getLabelLanguageValidator();
			$result = $validator->validate( $formData[self::FIELD_LANG] );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );
		}

		if ( $formData[self::FIELD_DESCRIPTION] != '' ) {
			$validator = $this->termValidatorFactory->getDescriptionValidator();
			$result = $validator->validate( $formData[self::FIELD_DESCRIPTION] );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );

			$validator = $this->termValidatorFactory->getDescriptionLanguageValidator();
			$result = $validator->validate( $formData[self::FIELD_LANG] );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );
		}

		if ( $formData[self::FIELD_ALIASES] !== [] ) {
			$validator = $this->termValidatorFactory->getAliasValidator();
			foreach ( $formData[self::FIELD_ALIASES] as $alias ) {
				$result = $validator->validate( $alias );
				$status->merge( $this->errorLocalizer->getResultStatus( $result ) );
			}

			$result = $validator->validate( implode( '|', $formData[self::FIELD_ALIASES] ) );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );

			$validator = $this->termValidatorFactory->getAliasLanguageValidator();
			$result = $validator->validate( $formData[self::FIELD_LANG] );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );
		}

		return $status;
	}

	/**
	 * @param Schema $schema
	 *
	 * @return Summary
	 * @suppress PhanParamSignatureMismatch Uses intersection types
	 */
	protected function createSummary( EntityDocument $schema ) {
		$uiLanguageCode = $this->getLanguage()->getCode();

		$summary = new Summary( 'wbeditentity', 'create' );
		$summary->setLanguage( $uiLanguageCode );
		/** @var Term|null $labelTerm */
		$labelTerm = $schema->getFingerprint()->getLabels()->getIterator()->current();
		/** @var Term|null $descriptionTerm */
		$descriptionTerm = $schema->getFingerprint()->getDescriptions()->getIterator()->current();
		$summary->addAutoSummaryArgs(
			$labelTerm ? $labelTerm->getText() : '',
			$descriptionTerm ? $descriptionTerm->getText() : ''
		);

		return $summary;
	}

	protected function displayBeforeForm( OutputPage $output ) {
		parent::displayBeforeForm( $output );
		$output->addModules( 'wikibase.special.languageLabelDescriptionAliases' );
	}

	/**
	 * @inheritDoc
	 */
	protected function getEntityType() {
		return Schema::ENTITY_TYPE;
	}

}

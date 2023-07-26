<?php

namespace Wikibase\Schema\MediaWiki\Specials;

use HTMLForm;
use MediaWiki\Html\Html;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\Lib\SettingsArray;
use Wikibase\Lib\Store\EntityTitleLookup;
use Wikibase\Lib\Summary;
use Wikibase\Lib\UserInputException;
use Wikibase\Repo\ChangeOp\ChangeOpException;
use Wikibase\Repo\CopyrightMessageBuilder;
use Wikibase\Repo\EditEntity\MediaWikiEditEntityFactory;
use Wikibase\Repo\Specials\SpecialModifyEntity;
use Wikibase\Repo\Specials\SpecialPageCopyrightView;
use Wikibase\Repo\SummaryFormatter;

/**
 * Special page for setting a Schema's text.
 *
 * @license GPL-2.0-or-later
 */
class SpecialSetSchemaText extends SpecialModifyEntity {

	public function __construct(
		array $tags,
		SpecialPageCopyrightView $copyrightView,
		SummaryFormatter $summaryFormatter,
		EntityTitleLookup $entityTitleLookup,
		MediaWikiEditEntityFactory $editEntityFactory,
	) {
		parent::__construct(
			'SetSchemaText',
			$tags,
			$copyrightView,
			$summaryFormatter,
			$entityTitleLookup,
			$editEntityFactory
		);
	}

	public static function factory(
		MediaWikiEditEntityFactory $editEntityFactory,
		EntityTitleLookup $entityTitleLookup,
		SettingsArray $repoSettings,
		SummaryFormatter $summaryFormatter
	): self {
		$copyrightView = new SpecialPageCopyrightView(
			new CopyrightMessageBuilder(),
			$repoSettings->getSetting( 'dataRightsUrl' ),
			$repoSettings->getSetting( 'dataRightsText' )
		);

		return new self(
			$repoSettings->getSetting( 'specialPageTags' ),
			$copyrightView,
			$summaryFormatter,
			$entityTitleLookup,
			$editEntityFactory
		);
	}

	/**
	 * @see SpecialModifyEntity::modifyEntity()
	 *
	 * @param EntityDocument $entity
	 *
	 * @return Summary|bool
	 */
	protected function modifyEntity( EntityDocument $entity ) {
		try {
			$entity->setSchemaText( $this->getRequest()->getText( 'schemaText' ) );
			$summary = new Summary();
		} catch ( ChangeOpException | UserInputException $e ) {
			$this->showErrorHTML( $e->getMessage() );
			return false;
		}

		return $summary;
	}

	/**
	 * @see SpecialModifyEntity::getForm()
	 *
	 * @param EntityDocument|null $entity
	 *
	 * @return HTMLForm
	 */
	protected function getForm( EntityDocument $entity = null ) {
		if ( $entity === null ) {
			$formDescriptor = [
				'id' => [
					'name' => 'id',
					'type' => 'text',
					'label' => 'Id (todo: i18n)'
				],
			];
		} else {
			$formDescriptor = [
				'id' => [
					'name' => 'id',
					'type' => 'hidden',
					'default' => $entity->getId()->getSerialization(),
				],
				'schemaText' => [
					'name' => 'schemaText',
					'type' => 'textarea',
					'default' => $entity->getSchemaText(),
				],
			];
		}

		return HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() )
			->setHeaderHtml( Html::rawElement( 'p', [], 'TODO: Intro message' ) );
	}

	protected function isModificationRequested() {
		return parent::isModificationRequested() && $this->getRequest()->getBool( 'schemaText' );
	}

}

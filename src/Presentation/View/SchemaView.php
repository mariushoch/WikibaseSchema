<?php

declare( strict_types = 1 );

namespace Wikibase\Schema\Presentation\View;

use InvalidArgumentException;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\PageReferenceValue;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Term\AliasesProvider;
use Wikibase\DataModel\Term\DescriptionsProvider;
use Wikibase\DataModel\Term\LabelsProvider;
use Wikibase\Schema\Domain\Model\Schema;
use Wikibase\Schema\Domain\Model\SchemaId;
use Wikibase\View\EntityTermsView;
use Wikibase\View\EntityView;
use Wikibase\View\LanguageDirectionalityLookup;
use Wikibase\View\Template\TemplateFactory;
use Wikibase\View\ViewContent;

class SchemaView extends EntityView {

	private EntityTermsView $entityTermsView;

	public function __construct(
		TemplateFactory $templateFactory,
		LanguageDirectionalityLookup $languageDirectionalityLookup,
		string $languageCode,
		EntityTermsView $entityTermsView
	) {
		parent::__construct( $templateFactory, $languageDirectionalityLookup, $languageCode );
		$this->entityTermsView = $entityTermsView;
	}

	public function getContent( EntityDocument $entity, $revision ): ViewContent {
		return new ViewContent(
			$this->renderEntityView( $entity ),
			$this->entityTermsView->getPlaceholders( $entity, $revision, $this->languageCode )
		);
	}

	public function getTitleHtml( EntityDocument $entity ): string {
		if ( $entity instanceof LabelsProvider ) {
			return $this->entityTermsView->getTitleHtml(
				$entity->getId()
			);
		}

		return '';
	}

	protected function getMainHtml( EntityDocument $entity ): string {
		if ( !( $entity instanceof Schema ) ) {
			throw new InvalidArgumentException( '$property must contain a Property.' );
		}

		$html = $this->getHtmlForTerms( $entity )
			. $this->getSchemaTextEditButton( $entity->getId() )
			. $this->renderSchemaText( $entity->getSchemaText() );

		return $html;
	}

	protected function getSideHtml( EntityDocument $entity ): string {
		return '';
	}

	private function getSchemaTextEditButton( SchemaId $schemaId ): string {
		// FIXME: This is almost certainly not the right place for generating these links. Also are we even editable?
		$linker = MediaWikiServices::getInstance()->getLinkRenderer();

		return $linker->makeLink(
			new PageReferenceValue( NS_SPECIAL, 'SetSchemaText/' . $schemaId->getSerialization(), PageReferenceValue::LOCAL ),
			wfMessage( 'edit' )
		);
	}

	private function renderSchemaText( string $schemaText ): string {
		$attribs = [
			'id' => 'entityschema-schema-text',
			'class' => 'entityschema-schema-text',
			'dir' => 'ltr',
		];

		// TODO: add syntax highlighting here

		return Html::element(
			'pre',
			$attribs,
			$schemaText
		);
	}

	private function getHtmlForTerms( EntityDocument $entity ): string {
		$id = $entity->getId();

		if ( $entity instanceof LabelsProvider && $entity instanceof DescriptionsProvider ) {
			return $this->entityTermsView->getHtml(
				$this->languageCode,
				$entity->getLabels(),
				$entity->getDescriptions(),
				$entity instanceof AliasesProvider ? $entity->getAliasGroups() : null,
				$id
			);
		}

		return '';
	}
}

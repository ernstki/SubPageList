<?php

namespace SubPageList;

use InvalidArgumentException;
use Title;

/**
 * Turns a flat list of Title objects into a sub page hierarchy of Page objects.
 *
 * @since 1.0
 *
 * @file
 * @ingroup SubPageList
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PageHierarchyCreator {

	/**
	 * Top level pages.
	 *
	 * @var Page[]
	 */
	protected $pages;

	/**
	 * All pages, indexed by title text.
	 *
	 * @var Page[]
	 */
	protected $allPages;

	/**
	 * @var TitleFactory
	 */
	protected $titleFactory;

	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param Title[] $titles
	 *
	 * @return Page[]
	 */
	public function createHierarchy( array $titles ) {
		$this->assertAreTitles( $titles );

		$this->pages = array();
		$this->allPages = array();

		foreach ( $titles as $title ) {
			$this->addTitle( $title );
		}

		return $this->pages;
	}

	protected function addTitle( Title $title ) {
		$page = new Page( $title, array() );
		$titleText = $this->getTextForTitle( $title );

		$parentTitle = $this->getParentTitle( $titleText );

		if ( $parentTitle === '' ) {
			$this->addTopLevelPage( $titleText, $page );
		}
		else {
			$this->createParents( $titleText );
			$this->addSubPage( $parentTitle, $titleText, $page );
		}
	}

	protected function getTextForTitle( Title $title ) {
		return $title->getFullText();
	}

	/**
	 * @param string $titleText
	 * @param Page $page Page is expected to not have any subpages
	 */
	protected function addTopLevelPage( $titleText, Page $page ) {
		if ( !array_key_exists( $titleText, $this->allPages ) ) {
			$this->pages[] = $page;
			$this->allPages[$titleText] = $page;
		}
	}

	/**
	 * @param string $parentTitle
	 * @param string $pageTitle
	 * @param Page $page Page is expected to not have any subpages
	 */
	protected function addSubPage( $parentTitle, $pageTitle, Page $page ) {
		if ( !array_key_exists( $pageTitle, $this->allPages ) ) {
			$this->allPages[$parentTitle]->addSubPage( $page );
			$this->allPages[$pageTitle] = $page;
		}
	}

	protected function addToPageIndex( $titleText, Page $page ) {
		$this->allPages[$titleText] = $page;
	}

	protected function createParents( $pageTitle ) {
		$titleParts = $this->getTitleParts( $pageTitle );
		array_pop( $titleParts );

		if ( empty( $titleParts ) ) {
			return;
		}

		$topLevelPage =  array_shift( $titleParts );

		$this->addTopLevelPage( $topLevelPage, $this->newPageFromText( $topLevelPage ) );

		$previousParts = array( $topLevelPage );

		foreach ( $titleParts as $titlePart ) {
			$parentTitle = $this->titleTextFromParts( $previousParts );

			$previousParts[] = $titlePart;

			$pageTitle = $this->titleTextFromParts( $previousParts );

			$this->addSubPage( $parentTitle, $pageTitle, $this->newPageFromText( $pageTitle ) );
		}
	}

	protected function newPageFromText( $titleText ) {
		return new Page( $this->titleFactory->newFromText( $titleText ) );
	}

	protected function getTitleParts( $titleText ) {
		return explode( '/', $titleText );
	}

	protected function titleTextFromParts( array $titleParts ) {
		return implode( '/', $titleParts );
	}

	protected function getParentTitle( $titleText ) {
		$titleParts = $this->getTitleParts($titleText );
		array_pop( $titleParts );
		return $this->titleTextFromParts( $titleParts );
	}

	protected function assertAreTitles( array $titles ) {
		foreach ( $titles as $title ) {
			if ( !( $title instanceof Title ) ) {
				throw new InvalidArgumentException( 'All elements must be of instance Title' );
			}
		}
	}

}
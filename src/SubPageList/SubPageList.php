<?php 

namespace SubPageList;

use ParamProcessor\ProcessingResult;
use Parser;
use ParserHooks\HookHandler;
use SubPageList\UI\SubPageListRenderer;
use Title;

/**
 * Handler for the subpagelist parser hook.
 *
 * @since 1.0
 * @file
 * @ingroup SubPageList
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SubPageList implements HookHandler {

	protected $subPageFinder;
	protected $pageHierarchyCreator;
	protected $subPageListRenderer;
	protected $titleFactory;

	public function __construct( SubPageFinder $finder, PageHierarchyCreator $hierarchyCreator,
		SubPageListRenderer $renderer, TitleFactory $titleFactory ) {

		$this->subPageFinder = $finder;
		$this->pageHierarchyCreator = $hierarchyCreator;
		$this->subPageListRenderer = $renderer;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @see HookHandler::handle
	 *
	 * @since 1.0
	 *
	 * @param Parser $parser
	 * @param ProcessingResult $result
	 *
	 * @return string
	 */
	public function handle( Parser $parser, ProcessingResult $result ) {
		if ( $result->hasFatal() ) {
			// TODO:
			return 'FATAL';
		}

		$parameters = $result->getParameters();

		$titleText = $parameters['page']->getValue();
		$title = $this->titleFactory->newFromText( $titleText );

		if ( $title !== null ) {
			return $this->renderSubPages( $title, $parameters );
		}

		return "\"$titleText\" has no sub pages."; // TODO
	}

	protected function renderSubPages( Title $title, array $parameters ) {
		$subPageTitles = $this->subPageFinder->getSubPagesFor( $title );
		$subPageTitles[] = $title;

		$pageHierarchy = $this->pageHierarchyCreator->createHierarchy( $subPageTitles );

		if ( count( $pageHierarchy ) !== 1 ) {
			throw new \LogicException( 'Expected exactly one top level page' );
		}

		$topLevelPage = reset( $pageHierarchy );

		$result = $this->subPageListRenderer->render( $topLevelPage );
		return $result;
	}

}
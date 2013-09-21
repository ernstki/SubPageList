<?php

namespace Tests\Unit\SubPageList;

use SubPageList\Extension;
use SubPageList\Settings;

/**
 * Tests for the SubPageList\Extension class.
 *
 * @file
 * @since 1.0
 *
 * @ingroup SubPageListTest
 *
 * @group SubPageList
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ExtensionTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider constructorProvider
	 *
	 * @param Settings $settings
	 */
	public function testConstructor( Settings $settings ) {
		$extension = new Extension( $settings );

		$this->assertEquals( $settings, $extension->getSettings() );
	}

	public function constructorProvider() {
		$argLists = array(
			array( Settings::newFromGlobals( $GLOBALS ) )
		);

		return $argLists;
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param Extension $extension
	 */
	public function testGetSlaveConnectionProvider( Extension $extension ) {
		$this->assertInstanceOf( 'SubPageList\DBConnectionProvider', $extension->getSlaveConnectionProvider() );
	}

	public function instanceProvider() {
		$argLists = array();

		$argLists[] = array( new Extension( Settings::newFromGlobals( $GLOBALS ) ) );

		return $argLists;
	}

	/**
	 * @dataProvider instanceProvider
	 *
	 * @param Extension $extension
	 */
	public function testGetCacheInvalidator( Extension $extension ) {
		$this->assertInstanceOf( 'SubPageList\CacheInvalidator', $extension->getCacheInvalidator() );
	}

}

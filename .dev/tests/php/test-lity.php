<?php

class Test_Simple_Taxonomy_Ordering extends WP_UnitTestCase {

	function setUp(): void {

		parent::setUp();

		require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/yikes-custom-taxonomy-order.php';

	}

	function tearDown(): void {

		parent::tearDown();

	}

	/**
	 * Test that true is true.
	 *
	 * @since 1.0.0
	 */
	function testTrue() {

		$this->assertTrue( true );

	}

}

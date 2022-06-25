<?php

class Test_Simple_Taxonomy_Ordering extends WP_UnitTestCase {

	public $instance;

	function setUp(): void {

		parent::setUp();

		require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/yikes-custom-taxonomy-order.php';

	}

	function tearDown(): void {

		parent::tearDown();

		delete_option( 'yikes_simple_taxonomy_ordering_options' );

	}

	/**
	 * Test the constants are set.
	 *
	 * @since 2.3.4
	 */
	function testConstants() {

		$this->assertEquals(
			[
				'YIKES_STO_VERSION'         => true,
				'YIKES_STO_VERSION_CHECK'   => '2.3.4',
				'YIKES_STO_PATH'            => true,
				'YIKES_STO_URL'             => true,
				'YIKES_STO_NAME'            => true,
				'YIKES_STO_OPTION_NAME'     => true,
				'YIKES_STO_SELECT2_VERSION' => true,
			],
			[
				'YIKES_STO_VERSION'         => defined( 'YIKES_STO_VERSION' ),
				'YIKES_STO_VERSION_CHECK'   => YIKES_STO_VERSION,
				'YIKES_STO_PATH'            => defined( 'YIKES_STO_PATH' ),
				'YIKES_STO_URL'             => defined( 'YIKES_STO_URL' ),
				'YIKES_STO_NAME'            => defined( 'YIKES_STO_NAME' ),
				'YIKES_STO_OPTION_NAME'     => defined( 'YIKES_STO_OPTION_NAME' ),
				'YIKES_STO_SELECT2_VERSION' => defined( 'YIKES_STO_SELECT2_VERSION' ),
			]
		);

	}

	/**
	 * Test the Yikes_Custom_Taxonomy_Order class exists.
	 *
	 * @since 2.3.4
	 */
	function testClassExists() {

		$this->assertTrue( class_exists( 'Yikes_Custom_Taxonomy_Order' ) );

	}

	/**
	 * Test the YIKES_Simple_Taxonomy_Options class exists.
	 *
	 * @since 2.3.4
	 */
	function testIncludeFiles() {

		$this->assertTrue( class_exists( 'YIKES_Simple_Taxonomy_Options' ) );

	}

	/**
	 * Test that category taxonomy ordering works.
	 *
	 * This test will:
	 *  1. Add three custom categories to the post categories.
	 *  2. Check that get_terms() returns the categories in the default order. 1, 2, 3.
	 *  3. Enable custom sorting for post categories.
	 *  4. Update the custom categories to reverse order. 3, 2, 1.
	 *  5. Check that get_terms() now returns the categories in our custom order. 3, 2, 1.
	 *
	 * @since 2.3.4
	 */
	function testCustomTaxonomyOrderWorks() {

		// Insert post categories
		$post_categories = [
			'custom_one' => [
				'name' => 'Custom Category 1',
			],
			'custom_two' => [
				'name' => 'Custom Category 2',
			],
			'custom_three' => [
				'name' => 'Custom Category 3',
			],
		];

		// Insert custom post categories.
		foreach ( $post_categories as $category_slug => $category_data ) {

			wp_insert_term(
				$category_data['name'],
				'category',
				array(
					'slug' => $category_slug
				)
			);

		}

		$categories = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
				'exclude'    => array( 1 ), // Exclude uncategorized.
			)
		);

		$this->assertEquals(
			array(
				'Custom Category 1',
				'Custom Category 2',
				'Custom Category 3',
			),
			array(
				$categories[0]->name,
				$categories[1]->name,
				$categories[2]->name,
			),
			'The original order of the post categories is not correct.'
		);

		// Enable sorting on categories.
		update_option( 'yikes_simple_taxonomy_ordering_options', array( 'enabled_taxonomies' => array( 'category' ) ) );

		$index = 1;

		foreach ( array_reverse( $post_categories ) as $category_slug => $category_data ) {

			$term = get_term_by( 'slug', $category_slug, 'category' );

			if ( ! is_a( $term, 'WP_Term' ) ) {

				continue;

			}

			update_term_meta( $term->term_id, 'tax_position', $index );

			$index++;

		}

		$categories = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
				'exclude'    => array( 1 ), // Exclude uncategorized.
			)
		);

		$this->assertEquals(
			array(
				'Custom Category 3',
				'Custom Category 2',
				'Custom Category 1',
			),
			array(
				$categories[0]->name,
				$categories[1]->name,
				$categories[2]->name,
			),
			'The custom order of the post categories is not correct.'
		);

	}

	/**
	 * Test to see if a given taxonomy does not have sorting enabled.
	 */
	function testIsTaxonomySortingNotEnabled() {

		$this->assertFalse( ( new Yikes_Custom_Taxonomy_Order() )->is_taxonomy_ordering_enabled( 'non_existing_taxonomy_slug' ) );

	}

	/**
	 * Test to see if a given taxonomy has sorting enabled.
	 */
	function testIsTaxonomySortingEnabled() {

		update_option( 'yikes_simple_taxonomy_ordering_options', array( 'enabled_taxonomies' => array( 'category' ) ) );

		$this->assertTrue( ( new Yikes_Custom_Taxonomy_Order() )->is_taxonomy_ordering_enabled( 'category' ) );

	}

}

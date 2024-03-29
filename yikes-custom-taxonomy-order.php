<?php
/**
 * Plugin Name: Simple Taxonomy Ordering
 * Description: Custom drag & drop taxonomy ordering.
 * Author: Evan Herman
 * Version: 2.3.4
 * Author URI: https://www.evan-herman.com
 * Text Domain: simple-taxonomy-ordering
 * Domain Path: /languages
 * Tested up to: 6.0
 *
 * @package YIKES_Simple_Taxonomy_Ordering
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

define( 'YIKES_STO_VERSION', '2.3.4' );
define( 'YIKES_STO_PATH', plugin_dir_path( __FILE__ ) );
define( 'YIKES_STO_URL', plugin_dir_url( __FILE__ ) );
define( 'YIKES_STO_NAME', plugin_basename( __FILE__ ) );
define( 'YIKES_STO_OPTION_NAME', 'yikes_simple_taxonomy_ordering_options' );
define( 'YIKES_STO_SELECT2_VERSION', '4.1.0-rc.0' );

// Only load class if it hasn't already been loaded.
if ( ! class_exists( 'Yikes_Custom_Taxonomy_Order' ) ) {

	/**
	 * Class Yikes_Custom_Taxonomy_Order.
	 */
	class Yikes_Custom_Taxonomy_Order {

		/**
		 * Main Constructor.
		 */
		public function __construct() {

			// Setup.
			include YIKES_STO_PATH . 'lib/options.php';

			// Hooks.
			add_action( 'current_screen', array( $this, 'admin_order_terms' ) );
			add_action( 'init', array( $this, 'front_end_order_terms' ) );
			add_action( 'wp_ajax_yikes_sto_update_taxonomy_order', array( $this, 'update_taxonomy_order' ) );
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		}

		/**
		 * Order the terms on the admin side.
		 *
		 * @param Object WP_Screen $screen Current screen object.
		 */
		public function admin_order_terms( WP_Screen $screen ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Form data is not being used.
			if ( empty( $_GET['orderby'] ) && 'edit-tags' === $screen->base && $this->is_taxonomy_ordering_enabled( $screen->taxonomy ) ) {
				$this->enqueue();
				$this->default_term_order( $screen->taxonomy );
				$this->yikes_sto_custom_help_tab();

				add_filter( 'terms_clauses', array( $this, 'set_tax_order' ), 10, 3 );
			}
		}

		/**
		 * Add a help tab to the taxonomy screen.
		 */
		public function yikes_sto_custom_help_tab() {
			$screen   = get_current_screen();
			$taxonomy = function_exists( 'get_current_screen' ) ? get_current_screen()->taxonomy : '';
			$options  = get_option( YIKES_STO_OPTION_NAME, array() );

			// Only show the help tab on enabled taxonomies.
			if ( empty( $taxonomy ) || empty( $options ) || ! isset( $options['enabled_taxonomies'] ) || ! in_array( $taxonomy, $options['enabled_taxonomies'], true ) ) {
				return;
			}

			$taxonomy = get_taxonomy( $taxonomy );

			if ( ( ! isset( $taxonomy->labels ) || ! isset( $taxonomy->labels->singular_name ) ) && ! isset( $taxonomy->labels ) ) {
				return;
			}

			$taxonomy_label = ( isset( $taxonomy->labels ) && isset( $taxonomy->labels->singular_name ) ) ? $taxonomy->labels->singular_name : $taxonomy->label;

			$screen->add_help_tab(
				array(
					'id'      => 'yikes_sto_help_tab',
					'title'   => sprintf(
						/* translators: %s is the taxonomy singular name. eg: Category */
						__( '%s Ordering', 'simple-taxonomy-ordering' ),
						esc_attr( $taxonomy_label )
					),
					'content' => '<p>' . __( 'To reposition a taxonomy in the list, simply click on a taxonomy and drag & drop it into the desired position. Each time you reposition a taxonomy, the data will update in the database and on the front end of your site.', 'simple-taxonomy-ordering' ) . '</p><p style="margin-left:0;"><em>' . __( 'Example', 'simple-taxonomy-ordering' ) . ':</em></p><img style="width:75%;max-width:825px;" src="' . YIKES_STO_URL . 'lib/img/sort-category-help-example.gif" alt="' . __( 'Simple Taxonomy Ordering Demo', 'simple-taxonomy-ordering' ) . '">',
				)
			);
		}

		/**
		 * Order the taxonomies on the front end.
		 */
		public function front_end_order_terms() {
			if ( ! is_admin() && apply_filters( 'yikes_simple_taxonomy_ordering_front_end_order_terms', true ) ) {
				add_filter( 'terms_clauses', array( $this, 'set_tax_order' ), 10, 3 );
			}
		}

		/**
		 * Enqueue assets.
		 */
		public function enqueue() {
			$min = SCRIPT_DEBUG ? '' : '.min';
			$tax = function_exists( 'get_current_screen' ) ? get_current_screen()->taxonomy : '';
			wp_enqueue_style( 'yikes-tax-drag-drop-styles', YIKES_STO_URL . "lib/css/yikes-tax-drag-drop{$min}.css", array(), YIKES_STO_VERSION, 'all' );
			wp_enqueue_script( 'yikes-tax-drag-drop', YIKES_STO_URL . "lib/js/yikes-tax-drag-drop{$min}.js", array( 'jquery-ui-core', 'jquery-ui-sortable' ), YIKES_STO_VERSION, true );
			wp_localize_script(
				'yikes-tax-drag-drop',
				'simple_taxonomy_ordering_data',
				array(
					'preloader_url'    => esc_url( admin_url( 'images/wpspin_light.gif' ) ),
					'term_order_nonce' => wp_create_nonce( 'term_order_nonce' ),
					'paged'            => isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 0,
					'per_page_id'      => "edit_{$tax}_per_page",
				)
			);
		}

		/**
		 * Default the taxonomy's terms' order if it's not set.
		 *
		 * @param string $tax_slug The taxonomy's slug.
		 */
		public function default_term_order( $tax_slug ) {
			$terms = get_terms( $tax_slug, array( 'hide_empty' => false ) );
			$order = $this->get_max_taxonomy_order( $tax_slug );
			foreach ( $terms as $term ) {
				if ( ! get_term_meta( $term->term_id, 'tax_position', true ) ) {
					update_term_meta( $term->term_id, 'tax_position', $order );
					$order++;
				}
			}
		}

		/**
		 * Get the maximum tax_position for this taxonomy. This will be applied to terms that don't have a tax position.
		 *
		 * @param string $tax_slug The taxonomy slug.
		 */
		private function get_max_taxonomy_order( $tax_slug ) {
			global $wpdb;
			$max_term_order = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT MAX( CAST( tm.meta_value AS UNSIGNED ) )
					FROM $wpdb->terms t
					JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id AND tt.taxonomy = '%s'
					JOIN $wpdb->termmeta tm ON tm.term_id = t.term_id WHERE tm.meta_key = 'tax_position'",
					$tax_slug
				)
			);
			$max_term_order = is_array( $max_term_order ) ? current( $max_term_order ) : 0;
			return (int) 0 === $max_term_order || empty( $max_term_order ) ? 1 : (int) $max_term_order + 1;
		}

		/**
		 * Re-Order the taxonomies based on the tax_position value.
		 *
		 * @param array $pieces     Array of SQL query clauses.
		 * @param array $taxonomies Array of taxonomy names.
		 */
		public function set_tax_order( $pieces, $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( $this->is_taxonomy_ordering_enabled( $taxonomy ) ) {
					global $wpdb;

					$join_statement = " LEFT JOIN $wpdb->termmeta AS term_meta ON t.term_id = term_meta.term_id AND term_meta.meta_key = 'tax_position'";

					if ( ! $this->does_substring_exist( $pieces['join'], $join_statement ) ) {
						$pieces['join'] .= $join_statement;
					}
					$pieces['orderby'] = 'ORDER BY CAST( term_meta.meta_value AS UNSIGNED )';
				}
			}
			return $pieces;
		}

		/**
		 * Check if a substring exists inside a string.
		 *
		 * @param string $string    The main string (haystack) we're searching in.
		 * @param string $substring The substring we're searching for.
		 *
		 * @return bool True if substring exists, else false.
		 */
		protected function does_substring_exist( $string, $substring ) {
			return strstr( $string, $substring ) !== false;
		}

		/**
		 * AJAX Handler to update terms' tax position.
		 */
		public function update_taxonomy_order() {
			if ( ! check_ajax_referer( 'term_order_nonce', 'term_order_nonce', false ) ) {
				wp_send_json_error();
			}

			$taxonomy_ordering_data = filter_input( INPUT_POST, 'taxonomy_ordering_data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$base_index             = filter_input( INPUT_POST, 'base_index', FILTER_SANITIZE_NUMBER_INT );

			foreach ( $taxonomy_ordering_data as $order_data ) {

				// Due to the way WordPress shows parent categories on multiple pages, we need to check if the parent category's position should be updated.
				// If the category's current position is less than the base index (i.e. the category shouldn't be on this page), then don't update it.
				if ( $base_index > 0 ) {
					$current_position = get_term_meta( $order_data['term_id'], 'tax_position', true );
					if ( (int) $current_position < (int) $base_index ) {
						continue;
					}
				}

				update_term_meta( $order_data['term_id'], 'tax_position', ( (int) $order_data['order'] + (int) $base_index ) );

			}

			do_action( 'yikes_sto_taxonomy_order_updated', $taxonomy_ordering_data, $base_index );

			wp_send_json_success();
		}

		/**
		 * Is Taxonomy Ordering Enabled.
		 *
		 * @param string $tax_slug the taxnomies slug.
		 */
		public function is_taxonomy_ordering_enabled( $tax_slug ) {
			$option_default     = array( 'enabled_taxonomies' => array() );
			$option             = get_option( YIKES_STO_OPTION_NAME, $option_default );
			$enabled_taxonomies = array_flip( $option['enabled_taxonomies'] );

			return isset( $enabled_taxonomies[ $tax_slug ] );
		}

		/**
		 * Internationalization.
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain(
				'simple-taxonomy-ordering',
				false,
				YIKES_STO_PATH . 'languages/'
			);
		}
	}
}

new Yikes_Custom_Taxonomy_Order();

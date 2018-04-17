<?php
/*
Plugin Name: YIKES Simple Taxonomy Ordering
Plugin URI: http://www.yikesinc.com
Description: Custom drag & drop taxonomy ordering.
Author: YIKES, Inc.
Version: 1.2.7
Author URI: http://www.yikesinc.com
Text Domain: simple-taxonomy-ordering
Domain Path: /languages
*/

/*  Copyright 2017  YIKES, Inc  (email : info@yikesinc.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Only load class if it hasn't already been loaded
if ( ! class_exists( 'Yikes_Custom_Taxonomy_Order' ) ) {

	// Yep Yep!
	class Yikes_Custom_Taxonomy_Order {

		/*
		*	Main Constructor
		*/
		function __construct() {
			// admin init
			add_action( 'admin_head', array( $this, 'yikes_custom_tax_order_admin_init' ) );
			// front end init
			add_action( 'init', array( $this, 'yikes_custom_tax_order_front_end_init' ) );
			// handle the AJAX request
			add_action( 'wp_ajax_update_taxonomy_order', array( $this, 'yikes_handle_ajax_request' ) );
			// add custom plugin links
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'yikes_sto_plugin_action_links' ) );
			// Include our options class
			include plugin_dir_path(__FILE__) . 'lib/options.php';
			add_action( 'load-edit-tags.php', array( $this, 'yikes_sto_custom_help_tab' ) );

			// i18n
			add_action( 'plugins_loaded', array( $this, 'yikes_sto_load_plugin_textdomain' ) );
		}

		/*
		*	Init and load the files as needed
		*	@since 0.1
		*/
		public function yikes_custom_tax_order_admin_init() {
			/* Admin Side Re-Order of Hierarchical Taxonomies */
			if( is_admin() ) {
				// get global screen data
				$screen = get_current_screen();
				// confirm $screen and $screen->base is set
				if( isset( $screen ) && isset( $screen->base ) ) {
					// confirm we're on the edit-tags page
					if( $screen->base == 'edit-tags' ) {
						// ensuere that our terms have a `tax_position` value set, so they display properly
						$this->yikes_ensure_terms_have_tax_position_value( $screen );
						// retreive a list of enabled taxonomies
						$taxonomies = self::yikes_get_registered_taxonomies();
						// confirm that the tax_position arg is set and no orderby param has been set
						if( ! isset( $_GET['orderby'] ) && $this->yikes_is_taxonomy_position_enabled( $screen->taxonomy ) ) {
							// enqueue our scripts/styles
							$this->yikes_sto_enqueue_scripts_and_styles();
							// ensure post types have tax_position set
							add_filter( 'admin_init', array( $this, 'yikes_ensure_tax_position_set' ) );
							// re-order the posts
							add_filter( 'terms_clauses', array( $this, 'yikes_alter_tax_order' ), 10, 3 );
						}
					}
				}
			}
		}

		/**
		*	Custom Help Tab
		*	@since 0.1
		*/
		public function yikes_sto_custom_help_tab() {
			$screen = get_current_screen();
			// ensuere that our terms have a `tax_position` value set, so they display properly
			$this->yikes_ensure_terms_have_tax_position_value( $screen );
			// retreive a list of enabled taxonomies
			$taxonomies = self::yikes_get_registered_taxonomies();
			// confirm that the tax_position arg is set and no orderby param has been set
			if( $this->yikes_is_taxonomy_position_enabled( $screen->taxonomy ) ) {
				// Add my_help_tab if current screen is My Admin Page
				$screen->add_help_tab( array(
					'id'	=> 'yikes_sto_help_tab',
					'title'	=> __( 'Taxonomy Ordering', 'simple-taxonomy-ordering' ),
					'content'	=> '<p>' . __( 'To reposition a taxonomy in the list, simply click on a taxonomy and drag & drop it into the desired position. Each time you reposition a taxonomy, the data will update in the database and on the front end of your site.', 'simple-taxonomy-ordering' ) . '</p>' . '<p style="margin-left:0;"><em>' . __( 'Example', 'simple-taxonomy-ordering' ) . ':</em></p><img style="width:75%;max-width:825px;" src="' . plugin_dir_URL(__FILE__) . 'lib/img/sort-category-help-example.gif" alt="' . __( 'Simple Taxonomy Ordering Demo', 'simple-taxonomy-ordering' ) . '">',
				) );
			}
		}

		/*
		*	Properly order the taxonomies on the front end
		*	@since 0.1
		*/
		public function yikes_custom_tax_order_front_end_init() {
			/* Front End Re-Order of Hierarchical Taxonomies */
			if( ! is_admin() ) {
				add_filter( 'terms_clauses', array( $this, 'yikes_alter_tax_order' ), 10, 3 );
			}
		}

		/*
		*	Enqueue any scripts/styles we need
		*	@since 0.1
		*/
		public function yikes_sto_enqueue_scripts_and_styles() {

			// styles
			wp_enqueue_style( 'yikes-tax-drag-drop-styles', plugin_dir_url( __FILE__ ) . 'lib/css/yikes-tax-drag-drop.css' );

			// enqueue jquery ui drag and drop
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			// enqueue our custom script
			wp_enqueue_script( 'yikes-tax-drag-drop', plugin_dir_url(__FILE__) . 'lib/js/yikes-tax-drag-drop.js', array( 'jquery-ui-core', 'jquery-ui-sortable' ), true );
			wp_localize_script( 'yikes-tax-drag-drop', 'simple_taxonomy_ordering_data', array(
				'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
				'preloader_url' => esc_url( admin_url( 'images/wpspin_light.gif' ) ),
			) );

		}

		/**
		*	Register the textdomain for proper i18n / l10n
		*	@since 1.2
		*/
		public function yikes_sto_load_plugin_textdomain() {
			load_plugin_textdomain(
				'simple-taxonomy-ordering',
				false,
				plugin_dir_path(__FILE__) . 'languages/'
			);
		}

		/*
		*	Make sure each taxonomy has some tax_position set in term meta
		*	if not, assign a value to 'tax_position' in wp_termmeta
		*	@since 0.1
		*/
		public function yikes_ensure_terms_have_tax_position_value( $screen ) {
			if( isset( $screen ) && isset( $screen->taxonomy ) ) {
				$terms = get_terms( $screen->taxonomy, array( 'hide_empty' => false ) );
				$x = 1;
				foreach( $terms as $term ) {
					if( ! get_term_meta( $term->term_id, 'tax_position', true ) ) {
						update_term_meta( $term->term_id, 'tax_position', $x );
						$x++;
					}
				}
			}
		}

		/*
		*	Re-Order the taxonomies based on the tax_position value
		*	@since 0.1
		*/
		public function yikes_alter_tax_order( $pieces, $taxonomies, $args ) {
			foreach( $taxonomies as $taxonomy ) {
				// confirm the tax is set to hierarchical -- else do not allow sorting
				if( $this->yikes_is_taxonomy_position_enabled( $taxonomy ) ) {
					global $wpdb;

					$join_statement = " LEFT JOIN $wpdb->termmeta AS term_meta ON t.term_id = term_meta.term_id AND term_meta.meta_key = 'tax_position'";

					if ( ! $this->yikes_does_substring_exist( $pieces['join'], $join_statement ) ) {
						$pieces['join'] .= $join_statement;
					}
					$pieces['orderby'] = "ORDER BY CAST( term_meta.meta_value AS UNSIGNED )";
				}
			}
			return $pieces;
		}

		/**
		* Check if a substring exists inside a string
		*
		* @since 1.2.3
		*
		* @param string | $string	 | The main string (haystack) we're searching in
		* @param string | $substring | The substring we're searching for
		* @return bool  | T || F 	 | True if substring exists, else false
		*/
		protected function yikes_does_substring_exist( $string, $substring ) {

			// Check if the $substring exists already in the $string
			return ( strstr( $string, $substring ) === false ) ? false : true;
		}

		/*
		*	Handle The AJAX Request
		*	@since 0.1
		*/
		public function yikes_handle_ajax_request() {
			$array_data = $_POST['updated_array'];
			foreach( $array_data as $taxonomy_data ) {
				update_term_meta( $taxonomy_data[0], 'tax_position', (int) ( $taxonomy_data[1] + 1 ) );
			}
			wp_die();
			exit;
		}

		/**
		*	Add custom plugin links on the 'plugins.php' page
		*	@since 0.1
		*/
		public function yikes_sto_plugin_action_links( $links ) {
		   $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=yikes-simple-taxonomy-ordering') ) .'" title="' . __( 'Simple Taxonomy Ordering Settings', 'simple-taxonomy-ordering' ) . '">' . __( 'Settings', 'simple-taxonomy-ordering' ) . '</a>';
		   $links[] = '<a href="https://yikesplugins.com" target="_blank">' . __( 'More Plugins by YIKES', 'simple-taxonomy-ordering' ) . '</a>';
		   return $links;
		}

		/**
		*	Helper function to confirm 'tax_position' is set to true (allowing sorting of taxonomies)
		*	eg: For an example on how to enable tax_position/sorting for taxonomies, please see:
		*	@since 0.1
		*	@return true/false
		*/
		public function yikes_is_taxonomy_position_enabled( $taxonomy_name ) {
			// Confirm a taxonomy name was passed in
			if( ! $taxonomy_name ) {
				return false;
			}
			$tax_object = get_taxonomy( $taxonomy_name );
			if( $tax_object && is_object( $tax_object ) ) {
				// get saved taxonomies
				$yikes_simple_tax_order_options = get_option( 'yikes_simple_taxonomy_ordering_options', array() );
				$enabled_taxonomies = isset( $yikes_simple_tax_order_options['enabled_taxonomies'] ) ? $yikes_simple_tax_order_options['enabled_taxonomies'] : array();
				// if 'tax_position' => true || is set on the settings page
				if( isset( $tax_object->tax_position ) && $tax_object->tax_position || in_array( $taxonomy_name, $enabled_taxonomies ) ) {
					return true;
				} else {
					return false;
				}
			}
			return false;
		}

		/**
		*	Helper function to return an array of enabled drag and drop taxonomies
		*	@since 0.1
		*	@returns array of enabled taxonomes, or empty if none enabled
		*/
		public static function yikes_get_registered_taxonomies() {
			// get ALL taxonomies on site
			$registered_taxonomies = get_taxonomies();
			// Array of taxonomies we want to exclude from being displayed in our options
			$ignored_taxonomies = apply_filters( 'yikes_simple_taxonomy_ordering_ignored_taxonomies', array(
				'nav_menu',
				'link_category',
				'post_format'
			) );
			// WooCommerce taxonomies
			$ignored_taxonomies = array_merge( $ignored_taxonomies, apply_filters( 'yikes_simple_taxonomy_ordering_ignored_woocommerce_taxonomies', array(
				'product_shipping_class',
				'product_cat', // excluded because Woo has built in drag and drop support out of the box
				'product_type',
			) ) );
			// Easy Digital Downloads taxonomies
			$ignored_taxonomies = array_merge( $ignored_taxonomies, apply_filters( 'yikes_simple_taxonomy_ordering_ignored_edd_taxonomies', array(
				'edd_log_type',
			) ) );
			// Strip Woocommerce product attributes
			foreach( $registered_taxonomies as $registered_tax ) {
				// strip all woocommerce product attributes
				if ( strpos( $registered_tax, 'pa_' ) !== false) {
					$location = array_search( $registered_tax, $registered_taxonomies );
					unset( $registered_taxonomies[$location] );
				}
			}
			// Strip Duplicate Taxonomies
			foreach( $ignored_taxonomies as $ignored_tax ) {
				if( in_array( $ignored_tax, $registered_taxonomies ) ) {
					$location = array_search( $ignored_tax, $registered_taxonomies );
					if( $location ) {
						unset( $registered_taxonomies[$location] );
					}
				}
			}
			// return the taxonomies
			return $registered_taxonomies;
		}

	}

}

// init
new Yikes_Custom_Taxonomy_Order;

?>

<?php
/**
 * YIKES Simple Taxonomy Ordering Options Class.
 *
 * @package YIKES_Simple_Taxonomy_Ordering
 */
class YIKES_Simple_Taxonomy_Options {

	/**
	 * Holds the values to be used in the fields callbacks.
	 *
	 * @var array $options.
	 */
	private $options;

	/**
	 * Start up.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'define_options_page' ) );
		add_action( 'admin_init', array( $this, 'init_options' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_footer_text', array( $this, 'footer_callout' ) );
	}

	/**
	 * Determine whether this is our settings page.
	 *
	 * @return bool True if we're on our settings page.
	 */
	private function is_settings_page() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : '';
		return ! empty( $screen ) && ! empty( $screen->base ) && $screen->base === 'settings_page_yikes-simple-taxonomy-ordering';
	}

	/**
	 * Add additiona scripts and styles as needed.
	 */
	public function enqueue_assets() {
		if ( $this->is_settings_page() ) {
			$min = yikes_sto_maybe_minified();
			wp_enqueue_style( 'select2.min.css', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), YIKES_STO_VERSION, 'all' );
			wp_enqueue_script( 'select2.min.js', plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js', array( 'jquery' ), YIKES_STO_VERSION, true );
			wp_enqueue_script( 'init-select2', plugin_dir_url( __FILE__ ) . "js/init-select2{$min}.js", array( 'select2.min.js' ), YIKES_STO_VERSION, true );
		}
	}

	/**
	 * Filter the footer text and add custom text asking for review.
	 */
	public function footer_callout() {

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : '';
		if ( $this->is_settings_page() ) {
			?>
				<style>.yikes-sto-review-star{ color: goldenrod; font-size: 15px; line-height: 1.3; width: 16px; }.yikes-review-link:hover { text-decoration: none !important; }</style>
				<em>
					<?php
						/* translators: placeholders are links */
						printf( esc_html__( 'Simple Taxonomy Ordering was created by %1$s. If you are enjoying it, please leave us a %2$s review!', 'simple-taxonomy-ordering' ), '<a href="//www.yikesplugins.com" target="_blank" class="yikes-review-link">YIKES, Inc.</a>', '<a href="https://wordpress.org/support/plugin/simple-taxonomy-ordering/reviews/?rate=5#new-post" target="_blank" class="yikes-review-link"><span class="dashicons dashicons-star-filled yikes-sto-review-star"></span><span class="dashicons dashicons-star-filled yikes-sto-review-star"></span><span class="dashicons dashicons-star-filled yikes-sto-review-star"></span><span class="dashicons dashicons-star-filled yikes-sto-review-star"></span><span class="dashicons dashicons-star-filled yikes-sto-review-star"></span></a>' );
					?>
				</em>
			<?php
		}
	}

	/**
	 * Add options page.
	 */
	public function define_options_page() {
		// This page will be under "Settings."
		add_submenu_page(
			'options-general.php',
			__( 'Simple Tax. Ordering', 'simple-taxonomy-ordering' ),
			__( 'Simple Tax. Ordering', 'simple-taxonomy-ordering' ),
			apply_filters( 'yikes_simple_taxonomy_ordering_capabilities', 'manage_options' ),
			'yikes-simple-taxonomy-ordering',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback.
	 */
	public function create_admin_page() {
		// Set class property.
		$this->options = get_option( YIKES_STO_OPTION_NAME, array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'YIKES Simple Taxonomy Ordering', 'simple-taxonomy-ordering' ); ?></h1>
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields.
				settings_fields( 'yikes_sto_option_group' );
				do_settings_sections( 'yikes-simple-taxonomy-ordering' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings.
	 */
	public function init_options() {
		register_setting( 'yikes_sto_option_group', YIKES_STO_OPTION_NAME );

		add_settings_section(
			'yikes_sto_setting_section',
			'',
			array( $this, 'options_header_text' ),
			'yikes-simple-taxonomy-ordering'
		);

		add_settings_field(
			'enabled_taxonomies',
			__( 'Enabled Taxonomies', 'simple-taxonomy-ordering' ),
			array( $this, 'enabled_taxonomies_callback' ),
			'yikes-simple-taxonomy-ordering',
			'yikes_sto_setting_section'
		);
	}

	/**
	 * Print the Options Page Description.
	 */
	public function options_header_text() {
		esc_html_e( 'Enable or disable taxonomies from being orderable by using the dropdown.', 'simple-taxonomy-ordering' );
	}

	/**
	 * Get the settings option array and print one of its values.
	 */
	public function enabled_taxonomies_callback() {
		$taxonomies = $this->get_taxonomies();
		$enabled    = isset( $this->options['enabled_taxonomies'] ) ? array_flip( $this->options['enabled_taxonomies'] ) : array();
		if ( $taxonomies ) {
			?>
			<select id="yikes-sto-select2" style="display: none;" multiple="multiple" name="<?php echo esc_attr( YIKES_STO_OPTION_NAME ); ?>[enabled_taxonomies][]">
			<?php
			foreach ( $taxonomies as $taxonomy ) {
				$tax_object       = get_taxonomy( $taxonomy );
				$tax_name         = $tax_object && isset( $tax_object->labels ) ? $tax_object->labels->name : $taxonomy;
				$post_type        = $tax_object && isset( $tax_object->object_type ) && isset( $tax_object->object_type[0] ) ? $tax_object->object_type[0] : 'post';
				$post_type_object = get_post_type_object( $post_type );
				$post_type_label  = $post_type_object->labels->name;
				$selected         = isset( $this->options['enabled_taxonomies'] ) && isset( $enabled[ $taxonomy ] ) ? 'selected="selected"' : '';
				?>
					<option value="<?php echo esc_attr( $taxonomy ); ?>" <?php echo esc_attr( $selected ); ?>>
						<?php echo esc_html( $tax_name ) . ' <small>(' . esc_html( $post_type_label ) . ')</small>'; ?>
					</option>
				<?php
			}
			?>
			</select>
			<p class="description"><?php esc_html_e( 'Select which taxonomies you would like to enable drag & drop sorting on.', 'simple-taxonomy-ordering' ); ?></p>
			<?php
		} else {
			esc_html_e( 'No Taxonomies Found.', 'simple-taxonomy-ordering' );
		}
	}


	/**
	 * Fetch all the taxonomies that should be available for ordering.
	 *
	 * By default, we exclude some WordPress, WooCommerce, and EDD taxonomy terms. To add or remove a taxonomy that we're excluding, use the filter `yikes_simple_taxonomy_ordering_excluded_taxonomies`.
	 *
	 * @return array An array of taxonomies.
	 */
	private function get_taxonomies() {
		$taxonomies = get_taxonomies();

		// Array of taxonomies we want to exclude from being displayed in our options.
		$excluded_taxonomies = array(
			'nav_menu',
			'link_category',
			'post_format',
			'product_shipping_class',
			'product_cat',
			'product_type',
			'edd_log_type',
		);

		/**
		 * Filter yikes_simple_taxonomy_ordering_ignored_taxonomies.
		 *
		 * Add or remove taxonomies that should not be available for ordering.
		 *
		 * @param array $excluded_taxonomies The array of ignored taxonomy slugs.
		 * @param array $taxonomies          The array of included taxonomy slugs.
		 *
		 * @return array $excluded_taxonomies The array of taxonomies that should be excluded from ordering.
		 */
		$excluded_taxonomies = apply_filters( 'yikes_simple_taxonomy_ordering_excluded_taxonomies', $excluded_taxonomies, $taxonomies );

		// Remove excluded taxonomies.
		$taxonomies = array_diff( $taxonomies, $excluded_taxonomies );

		/**
		 * Filter yikes_simple_taxonomy_ordering_included_taxonomies.
		 *
		 * Add or remove taxonomies that are available for ordering.
		 *
		 * @param array $taxonomies          The array of included taxonomy slugs.
		 * @param array $excluded_taxonomies The array of ignored taxonomy slugs.
		 *
		 * @return array $excluded_taxonomies The array of taxonomies that should be excluded from ordering.
		 */
		$taxonomies = apply_filters( 'yikes_simple_taxonomy_ordering_included_taxonomies', $taxonomies, $excluded_taxonomies );

		// Return the taxonomies.
		return $taxonomies;
	}
}

$yikes_sto_settings = new YIKES_Simple_Taxonomy_Options();

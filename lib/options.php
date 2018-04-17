<?php
/*
*	Main YIKES Simple Taxonomy Ordering Options Class
*	@since 0.1
*/
class YIKES_Simple_Taxonomy_Options
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'yikes_sto_options_page' ) );
        add_action( 'admin_init', array( $this, 'yikes_sto_options_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'yikes_sto_options_scripts_and_styles' ) );
    }

	/*
	*	Add additiona scripts and styles as needed
	*	@since 0.1
	*/
	public function yikes_sto_options_scripts_and_styles() {
		$screen = get_current_screen();
		if( isset( $screen ) && isset( $screen->base ) ) {
			// ensure scripts/styles are only loaded on our options page
			if( 'settings_page_yikes-simple-taxonomy-ordering' == $screen->base ) {
				wp_enqueue_style( 'select2.min.css', plugin_dir_url(__FILE__) . 'css/select2.min.css' );
				wp_enqueue_script( 'select2.min.js', plugin_dir_url(__FILE__) . 'js/select2.full.min.js', array( 'jquery' ), 'all', true );
				// Filter the footer text with review text/stars
				add_action( 'admin_footer_text', array( $this, 'yikes_sto_filter_footer_function' ) );
			}
		}
	}
	
	/**
	*	Filter the footer text and add custom text asking for review
	*	@since 0.1
	*/
	public function yikes_sto_filter_footer_function() 
	{	
		?>
			<style>.yikes-sto-review-star{ color: goldenrod; font-size: 15px; line-height: 1.3; width: 16px; }.yikes-review-link:hover { text-decoration: none !important; }</style>
			<em><?php printf( __( 'Simple Taxonomy Ordering was created by %s. If you are enjoying it, please leave us a %s review</a>!', 'simple-taxonomy-ordering' ), '<a href="yikesinc.com" target="_blank" class="yikes-review-link">YIKES</a>', '<a href="#" title="test" class="yikes-review-link"><span class="dashicons dashicons-star-filled yikes-sto-review-star"></span><span class="dashicons dashicons-star-filled yikes-sto-review-star"></span><span class="dashicons dashicons-star-filled yikes-sto-review-star"></span><span class="dashicons dashicons-star-filled yikes-sto-review-star"></span><span class="dashicons dashicons-star-filled yikes-sto-review-star"></span>' ); ?></em>
		<?php
	}
	
    /**
     * Add options page
     */
    public function yikes_sto_options_page()
    {
        // This page will be under "Settings"
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
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'yikes_simple_taxonomy_ordering_options', array() );
        ?>
        <div class="wrap">
            <h1><?php _e( 'YIKES Simple Taxonomy Ordering', 'simple-taxonomy-ordering' ); ?></h1>          
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'yikes_sto_option_group' );   
                do_settings_sections( 'yikes-simple-taxonomy-ordering' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function yikes_sto_options_init()
    {        
        register_setting(
            'yikes_sto_option_group', // Option group
            'yikes_simple_taxonomy_ordering_options' // Option name
        );

        add_settings_section(
            'yikes_sto_setting_section', // ID
            '', // Title
            array( $this, 'yikes_sto_options_description' ), // Callback
            'yikes-simple-taxonomy-ordering' // Page
        );  

        add_settings_field(
            'enabled_taxonomies', // ID
            __( 'Enabled Taxonomies', 'simple-taxonomy-ordering' ), // Title 
            array( $this, 'enabled_taxaonomies_callback' ), // Callback
            'yikes-simple-taxonomy-ordering', // Page
            'yikes_sto_setting_section' // Section           
        );      
		
    }
	
	 /** 
     * Print the Options Page Description
     */
    public function yikes_sto_options_description()
    {
        _e( 'Adjust the settings for YIKES Simple Taxonomy Ordering below.', 'simple-taxonomy-ordering' );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function enabled_taxaonomies_callback()
    {
		// initialize select2
		?><script>jQuery( document ).ready( function() { jQuery(".js-example-basic-multiple").select2(); }); </script><?php
		// get registered taxonomies
		$taxonomies = Yikes_Custom_Taxonomy_Order::yikes_get_registered_taxonomies();		
		// if taxonomies, create drop down
		if( $taxonomies ) {
			?><select class="js-example-basic-multiple" multiple="multiple" style="width:75%;min-width:300px;" name="yikes_simple_taxonomy_ordering_options[enabled_taxonomies][]"><?php
			foreach( $taxonomies as $taxonomy ) {
				$tax_object       = get_taxonomy( $taxonomy ); // get the taxonomy object
				$tax_name         = $tax_object && isset( $tax_object->labels ) ? $tax_object->labels->name : $taxonomy; // Setup the taxonomy name
				$post_type        = $tax_object && isset( $tax_object->object_type ) && isset( $tax_object->object_type[0] ) ? $tax_object->object_type[0] : 'post'; // setup post type
				$post_type_object = get_post_type_object( $post_type ); // get post type object
				$post_type_label  = $post_type_object->labels->name; // get the post type name
				$selected         = ( isset( $this->options['enabled_taxonomies'] ) && in_array( $taxonomy, $this->options['enabled_taxonomies'] ) ) ? 'selected' : ''; // setup the selected option
				?>
					<option value="<?php echo $taxonomy; ?>" <?php echo $selected; ?>>
						<?php echo $tax_name . ' <small>(' . $post_type_label . ')</small>'; ?>
					</option>
				<?php
			}
			?></select>
			<p class="description"><?php _e( 'Select which taxonomies you would like to enable drag & drop sorting on.', 'simple-taxonomy-ordering' ); ?></p>
			<?php
		} else {
			_e( 'No Taxonomies Found.' ,'simple-taxonomy-ordering' );
		}
    }
	
}

if( is_admin() )
    $yikes_sto_settings = new YIKES_Simple_Taxonomy_Options();
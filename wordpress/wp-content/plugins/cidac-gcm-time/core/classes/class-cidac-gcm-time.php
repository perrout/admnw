<?php
/**
* JMS Home Post Type
*/
class cidacGCMTime
{
	private $domain = 'cidac-gcm-time';
	private $post_type = 'cidac_gcm';
	private $shortcode_tag = 'cidac-gcm-time';

	/**
     * Class constructor.
     */
	public function __construct() {
		$this->cidac_load_plugin_textdomain();

		// Add Actons
		add_action( 'init', array( $this, 'cidac_register_post_type' ), 0 );
		add_action( 'add_meta_boxes', array( $this, 'cidac_register_meta_box_gcm' ) );
		
		// Add assets
		add_action( 'wp_enqueue_scripts', array( $this, 'cidac_enqueue_assets') );
		add_action( 'admin_enqueue_scripts', array( $this, 'cidac_admin_enqueue_assets') );

		// Add Shortcode
		add_shortcode( $this->shortcode_tag, array( $this, 'cidac_register_shortcode' ) );

	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function cidac_load_plugin_textdomain() {
		load_plugin_textdomain( $this->domain, false, CIDAC_GCM_TIME_PLUGIN_FILE . '/languages/' );
	}

	/**
	 * Register assets files
	 */
	public function cidac_admin_enqueue_assets() {
		// Make paths variables so we don't write em twice ;)
		$css_path = '/assets/css/editor-style.css';
		$css_ver = date("ymd-Gis", filemtime( get_template_directory() . $css_path ));

		// Register optional editor only styles
		wp_register_style(
			'cidac-gcm-time-admin',
			CIDAC_GCM_TIME_PLUGIN_URL . $css_path,
			array(),
			$css_ver
		);

		// Enqueue optional editor only styles
		wp_enqueue_style( 'cidac-gcm-time-admin' );

	}

	public function cidac_enqueue_assets() {
		// Make paths variables so we don't write em twice ;)
		$js_path = '/assets/js/main.js';
		$css_path = '/assets/css/style.css';
		$js_ver  = date("ymd-Gis", filemtime( get_template_directory() . $js_path ));
		$css_ver = date("ymd-Gis", filemtime( get_template_directory() . $css_path ));
		
		// General scripts.
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			// Bootstrap.
			wp_enqueue_script( 
				'cidac-gcm-time-bootstrap', 
				CIDAC_GCM_TIME_PLUGIN_DIR . '/assets/js/libs/bootstrap.min.js', 
				array(), 
				null, 
				true 
			);

			// Main jQuery.
			wp_enqueue_script( 
				'cidac-gcm-time-main', 
				CIDAC_GCM_TIME_PLUGIN_DIR . $js_path, 
				array('jquery'), 
				$js_ver, 
				true 
			);
		} else {
			// Grunt main file with Bootstrap, FitVids and others libs.
			wp_enqueue_script( 
				'cidac-gcm-main-min', 
				CIDAC_GCM_TIME_PLUGIN_DIR . '/assets/js/main.min.js', 
				array('jquery'), 
				$js_ver, 
				true 
			);
		}	

		// Register optional editor only styles
		wp_register_style(
			'cidac-gcm-time',
			CIDAC_GCM_TIME_PLUGIN_URL . $css_path,
			array(),
			$css_ver
		);

		// Enqueue optional editor only styles
		wp_enqueue_style( 'cidac-gcm-time'	);

	}
	/**
	 * Set Post Type
	 */
	public function cidac_register_post_type() {
		$labels = array(
			'name'                  => _x( 'GCMs', 'Post Type General Name', $this->domain ),
			'singular_name'         => _x( 'GCM', 'Post Type Singular Name', $this->domain ),
			'menu_name'				=> __( 'GCM', $this->domain ),
			'name_admin_bar'		=> __( 'GCM', $this->domain ),
			'add_new'				=> __( 'Add New Home', $this->domain ),
			'add_new_item'			=> __( 'Add New Home', $this->domain ),
			'edit_item'				=> __( 'Edit Home', $this->domain ),
			'all_items'				=> __( 'All', $this->domain )
		);

		$args = array(
			'label'					=> __( 'GCM', $this->domain ),
			'description'			=> __( 'GCM Time', $this->domain ),
			'labels'              	=> $labels,
			'supports'				=> array( 'title' ),
			'menu_icon'				=> 'dashicons-admin-home',
			'menu_position'         => 2,
			'public'                => true,
			'show_ui'               => true,
			'show_in_rest' 			=> true,
			'publicly_queryable'    => false,
			'can_export'            => true,
			'query_var' 			=> true,
			'has_archive' 			=> false,
			'capability_type'       => 'post',
		);
		register_post_type( $this->post_type, $args );
	}

	/**
	 * Set Metabox
	 */
	public function cidac_register_meta_box_gcm() {
		add_meta_box( 
			'cidac-gcm-meta-box-id', 
			'GCM', 
			array( $this, 'cidac_render_meta_box_gcm' ), 
			$this->post_type, 
			'normal', 
			'high' 
		);
	}

	/**
	 * Render Metabox
	*/
	public function cidac_render_meta_box_gcm() {
		$results = get_post_meta(get_the_ID(), 'cidac_gcm_matricula');
		if ( count( $results) > 0 ) {
			echo '<ul id="cidac-gcm">';
			foreach ( $results as $r ) {
				echo '<li>' . $r . '</li>';
			}
			echo '</ul>';
		}
	}


	/**
	 * Shortcode to embed form on frontend
	 *
 	 * @return string
	 */
	public function cidac_register_shortcode( $atts ) {
		// Stop running function if form wasn't submitted
		if ( isset($_POST['cidac_gcm_post_id']) && isset($_POST['cidac_gcm_matricula']) ) {
			// Check that the nonce was set and valid
			if( !wp_verify_nonce($_POST['_wpnonce'], 'cidac_gcm_nonce') ) {
				echo 'Did not save because your form seemed to be invalid. Sorry';
				return;
			}
		
			add_post_meta( $_POST['cidac_gcm_post_id'], 'cidac_gcm_matricula', $_POST['cidac_gcm_matricula'] );

			echo 'Dados salvos';
			return;

		}else {
			echo ( is_user_logged_in() ) ? $this->cidac_render_shortcode() : 'Acesso restrito.';
		}
	}

	/**
	 * Shortcode to render form on frontend
	 *
 	 * @return string
	 */
	public function cidac_render_shortcode() {
		$args = array( 'post_type' => $this->post_type, 'posts_per_page' => 1 );
		$loop = new WP_Query( $args );
		ob_start();
		while ( $loop->have_posts() ) : $loop->the_post();
			the_title();
			?>
			<form id="cidac_gcm" name="cidac_gcm" method="post">
				<?php wp_nonce_field( 'cidac_gcm_nonce' ); ?>
				<input type="hidden" id="cidacGcmPostId" name="cidac_gcm_post_id" value="<?php echo get_the_ID() ?>">
				<div class="form-group">
					<label for="cidacGcmMatricula">GCM Matricula</label>
					<input type="text" class="form-control" id="cidacGcmMatricula" name="cidac_gcm_matricula" placeholder="Matricula">
				</div>
				<button type="submit" class="btn btn-default">Submit</button>
			</form>
		<?php
		endwhile;
		$output_string = ob_get_contents();
		ob_end_clean();
		return $output_string;
	}
}



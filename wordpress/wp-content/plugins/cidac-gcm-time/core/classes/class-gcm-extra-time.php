<?php

require_once CIDAC_GCM_TIME_PLUGIN_DIR . '/core/classes/class-gcm-extra-time-export.php';

/**
* JMS Home Post Type
*/
class cidacGcmExtraTime
{
	private $domain = 'cidac-gcm-extra-time';
	private $post_type = 'cidac_gcm';
	private $shortcode_tag = 'cidac-gcm-extra-time';

	/**
     * Class constructor.
     */
	public function __construct() {
		$this->cidac_load_plugin_textdomain();
		$this->cidac_activation_function();
		$this->alerts = null;
		// Add Actons
		add_action( 'init', array( $this, 'cidac_register_post_type' ), 0 );
		add_action( 'add_meta_boxes', array( $this, 'cidac_register_meta_box_gcm' ) );
		add_action( 'admin_menu', array( $this, 'cidac_gcm_export_add_menu_dashboard') );

		add_action( 'after_setup_theme', array( $this, 'cidac_register_custom_forms' ) );

		// Block access to dashboard admin
		add_action( 'admin_init', array(&$this, 'cidac_block_wp_admin') );

		// Filters
		add_filter( 'show_admin_bar' , array(&$this, 'cidac_disable_admin_bar') );
		
		// Add assets
		add_action( 'wp_enqueue_scripts', array( $this, 'cidac_enqueue_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'cidac_admin_enqueue_assets' ) );

		// Custom Users Fields
		// add the field to user's own profile editing screen
		add_action(	'edit_user_profile', array( $this, 'cidac_usermeta_form_fields' ) );
		
		// add the field to user profile editing screen
		add_action(	'show_user_profile', array( $this, 'cidac_usermeta_form_fields' ) );
		
		// add the save action to user's own profile editing screen update
		add_action(	'personal_options_update', array( $this, 'cidac_usermeta_form_fields_update' ) );
		
		// add the save action to user profile editing screen update
		add_action(	'edit_user_profile_update', array( $this, 'cidac_usermeta_form_fields_update' ) );

		// Add Shortcode
		add_shortcode( $this->shortcode_tag, array( $this, 'cidac_register_shortcode' ) );

		// Add GCM Role
		add_role(
			'subscriber_gcm',
			__( 'GCM' ),
			array(
				'read' => true
			)
		);
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function cidac_load_plugin_textdomain() {
		load_plugin_textdomain( $this->domain, false, CIDAC_GCM_TIME_PLUGIN_FILE . '/languages/' );
	}
	
	public function cidac_activation_function() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'cidac_gcm';		
		$charset_collate = $wpdb->get_charset_collate();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {
			$sql = "CREATE TABLE $table_name (
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`register` varchar(255),
			`name` varchar(255),
			`email` varchar(255),
			`phone` varchar(255),
			`cpf` varchar(255),
			UNIQUE KEY id (id)
			) $charset_collate;";
			
			$result = $wpdb->query($sql); 

			// $sql = "CREATE TABLE $table_name (
            //     ID mediumint(9) NOT NULL AUTO_INCREMENT,
            //     `product-model` text NOT NULL,
            //     `product-name` text NOT NULL,
            //     `product-description` int(9) NOT NULL,
            //     PRIMARY KEY  (ID)
			// )    $charset_collate;";
			// require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			// dbDelta( $sql );
		}
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
			'add_new'				=> __( 'Adicionar Nova Data', $this->domain ),
			'add_new_item'			=> __( 'Adicionar Nova Data', $this->domain ),
			'edit_item'				=> __( 'Editar', $this->domain ),
			'all_items'				=> __( 'Hora Extra', $this->domain )
		);

		$args = array(
			'label'					=> __( 'GCM', $this->domain ),
			'description'			=> __( 'GCM Hora Extra', $this->domain ),
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
     * Menu item will allow us to load the page to display the table
     */
    public function cidac_gcm_export_add_menu_dashboard()
    {			
		add_submenu_page(
			'edit.php?post_type=cidac_gcm',
			__( 'Exportar GCM' ),
			__( 'Exportar GCM' ),
			'manage_options',
			'cidac-gcm-extra-time-page',
			array($this, 'cidac_gcm_export_users_callback' )
		);
	}
	
    /**
     * Display the list table page
     *
     * @return Void
     */
    public function cidac_gcm_export_users_callback()
    {        
        $jmsContentLockListTable = new CIDAC_GCM_List_Table();
        $jmsContentLockListTable->prepare_items();
        ob_start();
	?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
				<h2><?php _e( "Exportar Leads" ); ?></h2>
                <?php $jmsContentLockListTable->display(); ?>
                <!-- <script type="text/javascript">
                    jQuery(document).ready( function($)
                    {
                        $('.tablenav.top .clear, .tablenav.bottom .clear').before('<form action="#" method="POST"><input type="hidden" id="jms-content-lock-export" name="jms-content-lock-export" value="1" /><input class="button button-primary" style="margin-left:-8px;" type="submit" value="Exportar como CSV" /></form>');
                    });
                </script> -->
            </div>
	<?php
        $output_string = ob_get_contents();
        ob_end_clean();
        echo $output_string;
    }


	/**
	 * Block access to dashboard for gcm users
	 */
	public function cidac_block_wp_admin() {
		if ( is_admin() && current_user_can( 'subscriber_gcm' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}
	
	/**
	 * Disable admin bar for gcm users
	 */
	public function cidac_disable_admin_bar($content) {
		return ( current_user_can( 'subscriber_gcm' ) ) ? false : $content;
	}

	/**
	 * The field on the editing screens.
	 *
	 * @param $user WP_User user object
	 */
	public function cidac_usermeta_form_fields($user) {
		?>
		<h3><?php _e('GCM') ?></h3>
		<table class="form-table">
			<tr>
				<th>
					<label for="user_cpf"><?php _e('CPF') ?></label>
				</th>
				<td>
					<input type="text"
						class="regular-text ltr"
						id="user_cpf"
						name="user_cpf"
						value="<?= esc_attr(get_user_meta($user->ID, 'user_cpf', true)); ?>"
						title="<?php _e('Utilizar o formato 000.000.000-00.') ?>"
						placeholder="<?php _e('000.000.000-00') ?>"
						pattern="[0-9]{3}.[0-9]{3}.[0-9]{3}-[0-9]{2}"
						required>
					<p class="description">
						<?php _e('Por favor informe o CPF.') ?>
					</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="user_phone"><?php _e('Telefone') ?></label>
				</th>
				<td>
					<input type="text"
						class="regular-text ltr"
						id="user_phone"
						name="user_phone"
						value="<?= esc_attr(get_user_meta($user->ID, 'user_phone', true)); ?>"
						title="<?php _e('Utilizar o formato (00) 00000-0000.') ?>"
						placeholder="<?php _e('(00) 00000-0000') ?>"
						required>
					<p class="description">
						<?php _e('Por favor informe o Telefone.') ?>						
					</p>
				</td>
			</tr>
		</table>
		<?php
	}
	
	/**
	 * The save action.
	 *
	 * @param $user_id int the ID of the current user.
	 *
	 * @return bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function cidac_usermeta_form_fields_update($user_id) {
		// check that the current user have the capability to edit the $user_id
		if (!current_user_can('edit_user', $user_id)) {
			return false;
		}

		if (isset($_POST['user_cpf'])) {
			update_user_meta( $user_id, 'user_cpf', $_POST['user_cpf'] );
		}

		if (isset($_POST['user_phone'])) {
			update_user_meta( $user_id, 'user_phone', $_POST['user_phone'] );
		}

		return;
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
			'cidac-gcm-extra-time-admin',
			CIDAC_GCM_TIME_PLUGIN_URL . $css_path,
			array(),
			$css_ver
		);

		// Enqueue optional editor only styles
		wp_enqueue_style( 'cidac-gcm-extra-time-admin' );

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
				'cidac-gcm-extra-time-bootstrap', 
				CIDAC_GCM_TIME_PLUGIN_DIR . '/assets/js/libs/bootstrap.min.js', 
				array(), 
				null, 
				true 
			);

			// Main jQuery.
			wp_enqueue_script( 
				'cidac-gcm-extra-time-main', 
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
			'cidac-gcm-extra-time',
			CIDAC_GCM_TIME_PLUGIN_URL . $css_path,
			array(),
			$css_ver
		);

		// Enqueue optional editor only styles
		wp_enqueue_style( 'cidac-gcm-extra-time'	);

	}

	public function cidac_register_custom_forms() {
		if ( isset($_POST['cidac_gcm_login_form']) ) {
			if ( ! isset( $_POST['cidac_gcm_login_nonce'] ) || ! wp_verify_nonce( $_POST['cidac_gcm_login_nonce'], 'cidac_gcm_login' ) ) {	
				$this->alerts = 'Desculpe, nonce não verificado.';		
			} else {
				// $redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				$username =  $_POST[ 'username' ];
				$password =  $_POST[ 'password' ];
				$remember_me = isset($_POST[ 'rememberme' ]) ? true : false;
			
				$credentials = array(
					'user_login'    => $username,
					'user_password' => $password,
					'remember'      => $remember_me
				);
			
				$user = wp_signon( $credentials, false );
					
				if ( is_wp_error($user) ) {
					$this->alerts = $user->get_error_message();
				} else {
					clean_user_cache($user->ID);
					wp_clear_auth_cookie();
					wp_set_current_user($user->ID);
					wp_set_auth_cookie($user->ID, $remember_me, false);
				}
			}
		}

		if ( isset($_POST['cidac_gcm_register_form']) ) {
			if ( ! isset( $_POST['cidac_gcm_register_nonce'] ) || ! wp_verify_nonce( $_POST['cidac_gcm_register_nonce'], 'cidac_gcm_register' ) ) {	
				$this->alerts = 'Desculpe, nonce não verificado.';		
			} else {	
				$first_name = sanitize_text_field( $_POST['first_name'] );
				$last_name = sanitize_text_field( $_POST['last_name'] );
				$display_name = $first_name . ' ' . $last_name;
				$user_cpf = sanitize_text_field( $_POST['user_cpf'] );
				$user_phone = sanitize_text_field( $_POST['user_phone'] );
				$info = array();
				$info['first_name'] = $first_name;
				$info['last_name'] = $last_name;
				$info['display_name'] = $display_name;
				$info['user_nicename'] = $info['user_login'] = sanitize_user( $_POST['username'] );
				$info['user_email'] = sanitize_email($_POST['user_email']);
				$info['nickname'] = sanitize_user( $_POST['username'] );;				
				$info['user_pass'] = sanitize_text_field( $_POST['password'] );;
				$info['role'] = 'subscriber_gcm';
				// Register the user
				$user = wp_insert_user( $info );
				if ( ! is_wp_error($user) ){ 
					if ($user_cpf) update_user_meta( $user, 'user_cpf', $user_cpf );
					if ($user_phone) update_user_meta( $user, 'user_phone', $user_phone );
					clean_user_cache($user);
					wp_clear_auth_cookie();
					wp_set_current_user($user);
					wp_set_auth_cookie($user, true, false);
				}else {
					$error  = $user->get_error_codes() ;    
					if(in_array('empty_user_login', $error))
						$this->alerts = __($user->get_error_message('empty_user_login'));
					elseif(in_array('existing_user_login',$error))
						$this->alerts = __('Matrícula já cadastrada.');
					elseif(in_array('existing_user_email',$error))
						$this->alerts = __('E-mail já cadastrado.');
				}
			}
		}
	}

	/**
	 * Set Metabox
	 */
	public function cidac_register_meta_box_gcm() {
		add_meta_box( 
			'cidac-gcm-meta-box-id', 
			'GCM Hora Extra', 
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
		if ( ! is_user_logged_in() )  { 
			echo $this->cidac_render_forms();
		} else {
			echo $this->cidac_render_shortcode();
		} 
		// if ( !is_admin() ) {
		// 	return ( is_user_logged_in() ) ? $this->cidac_render_shortcode() : $this->cidac_render_forms();
		// }
		// Stop running function if form wasn't submitted
		// if ( isset($_POST['cidac_gcm_post_id']) && isset($_POST['cidac_gcm_matricula']) ) {
		// 	// Check that the nonce was set and valid
		// 	if( !wp_verify_nonce($_POST['_wpnonce'], 'cidac_gcm_nonce') ) {
		// 		echo 'Did not save because your form seemed to be invalid. Sorry';
		// 		return;
		// 	}

		// 	add_post_meta( $_POST['cidac_gcm_post_id'], 'cidac_gcm_matricula', $_POST['cidac_gcm_matricula'] );

		// 	echo 'Dados salvos';
		// 	return;

		// }else {


		// 	echo ( is_user_logged_in() ) ? $this->cidac_render_shortcode() : $this->cidac_render_forms($error);
		// }


		
	}

	/**
	 * Shortcode to render form on frontend
	 *
 	 * @return string
	 */
	public function cidac_render_shortcode() {
		ob_start();
		$args = array( 'post_type' => $this->post_type, 'posts_per_page' => 1 );
		$loop = new WP_Query( $args );
		while ( $loop->have_posts() ) : $loop->the_post();
			the_title();
			?>
			<form id="cidac_gcm" name="cidac_gcm" method="post" action="<?php echo $_SERVER['HTTP_REFERER'] ?>">
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
		$output = ob_get_clean();
		return $output;
	}


	/**
	 * Shortcode to render form on frontend
	 *
 	 * @return string
	 */
	public function cidac_render_forms() {
		ob_start();
		?>		
		<div id="cidac_gcm_forms">
			<?php if (isset($this->alerts)) : ?>
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-warning alert-dismissible" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<?php _e($this->alerts) ?>
					</div>
				</div>
			</div>
			<?php endif ?>
			<div class="row">
				<div class="col-md-5">
					<form method="post" name="cidac_gcm_register_form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" >
						<?php wp_nonce_field( 'cidac_gcm_register', 'cidac_gcm_register_nonce' ); ?>
						<fieldset>
							<div id="legend">
							<legend class=""><?php _e('Não tenho cadastro') ?></legend>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="firstName"><?php _e('Nome') ?></label>
										<input type="text" class="form-control" id="firstName" name="first_name" placeholder="<?php _e('Nome') ?>" required />
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="lastName"><?php _e('Sobrenome') ?></label>
										<input type="text" class="form-control" id="lastName" name="last_name" placeholder="<?php _e('Sobrenome') ?>" required >
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="userCPF"><?php _e('CPF') ?></label>
										<input type="text" class="form-control" id="userCPF" name="user_cpf" placeholder="<?php _e('CPF') ?>" required />
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="userPhone"><?php _e('Telefone') ?></label>
										<input type="tel" class="form-control" id="userPhone" name="user_phone" placeholder="<?php _e('Telefone') ?>" required />
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="userEmail"><?php _e('E-mail') ?></label>
								<input type="email" class="form-control" id="userEmail" name="user_email" placeholder="<?php _e('E-mail') ?>" required />
							</div>
							<div class="form-group">
								<label for="userLogin"><?php _e('Matrícula') ?></label>
								<input type="text" class="form-control" id="userLogin" name="username" placeholder="<?php _e('Matrícula') ?>" required />
							</div>
							<div class="form-group">
								<label for="userPass"><?php _e('Senha') ?></label>
								<input type="password" class="form-control" id="userPass" name="password" placeholder="<?php _e('Senha') ?>" required />
							</div>
							<button type="submit" name="cidac_gcm_register_form" class="btn btn-default"><?php _e('Cadastrar') ?></button>
						</fieldset>
					</form>
				</div>
				<div class="col-md-5 col-md-offset-1">
					<form method="post" name="cidac_gcm_login_form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" >
						<?php wp_nonce_field( 'cidac_gcm_login', 'cidac_gcm_login_nonce' ); ?>
						<fieldset>
							<div id="legend">
							<legend class=""><?php _e('Já tenho cadastro') ?></legend>
							</div>
							<div class="form-group">
								<label for="userLogin"><?php _e('Matrícula') ?></label>
								<input type="text" class="form-control" id="userLogin" name="username" placeholder="<?php _e('Matrícula') ?>" required />
							</div>
							<div class="form-group">
								<label for="userPass"><?php _e('Senha') ?></label>
								<input type="password" class="form-control" id="userPass" name="password" placeholder="<?php _e('Senha') ?>" required />
							</div>
							<div class="checkbox">
								<label>
									<input name="rememberme" type="checkbox"> <?php _e('Lembrar-me') ?>
								</label>
							</div>
							<button type="submit" name="cidac_gcm_login_form" class="btn btn-default"><?php _e('Entrar') ?></button>
						</fieldset>
					</form>
				</div>
			<//div>
		</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}

}
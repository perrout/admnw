<?php

class JMS_Content_Lock {
	
	protected $atts = array();
	
	public function __construct() {
		// Shortcodes
		// add_shortcode('logged_in', array(&$this, 'shortcode_logged_in')); // Logged In
		// add_shortcode('came_from', array(&$this, 'shortcode_came_from')); // Referer
		// add_filter('the_excerpt', array(&$this, 'jms_content_lock_box'));
		add_filter('the_content', array(&$this, 'jms_content_lock_content'));
		// add_filter('query_vars',  array(&$this, 'jms_add_query_vars_filter'));
		// Enable the user with no privileges to run ajax_login() in AJAX
		add_action('wp_ajax_nopriv_ajaxlogin',  array(&$this, 'jms_content_lock_ajax_login'));
		// Enable the user with no privileges to run ajax_register() in AJAX
		add_action('wp_ajax_nopriv_ajaxregister',  array(&$this, 'jms_content_lock_ajax_register'));
		// add_action( 'admin_init', array(&$this, 'jms_content_lock_block_wp_admin'));
		add_role(
			'subscriber_lead',
			__( 'Lead' ),
			array(
				'read' => true
			)
		);
		
	}

	public function jms_content_lock_block_wp_admin() {
		if ( is_admin() && current_user_can( 'subscriber_lead' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	// public function jms_content_lock_box( $excerpt ) {
	// 	if(!is_user_logged_in()) {
	// 		if( is_single() && ! empty( $GLOBALS['post'] ) ) {
	// 			if ( $GLOBALS['post']->ID == get_the_ID() ) {								
	// 				$excerpt .= '
	// 				<div id="jms-content-lock">
	// 					<div class="jms-content-lock-container">
	// 						<h4>Faça seu cadastro para continuar lendo</h4>
	// 						<div class="row">
	// 							<div class="col-md-6">
	// 								<fb:login-button scope="public_profile,email" size="large" button-type="continue_with" auto-logout-link="false" use-continue-as="true" show-faces="false" onlogin="checkLoginState();"></fb:login-button>
	// 							</div>
	// 							<div class="col-md-6">
	// 								<form id="jmsContentLockLogin" class="form-inline" action="login" method="post">
	// 									<label for="email" class="sr-only">Email</label>
	// 									<input id="jmsContentLockEmail" type="text" name="email" class="form-control form-signin-email" placeholder="Email" required autofocus>
	// 									<button class="btn btn-primary" type="submit">Entrar</button>	
	// 									<p id="jmsContentLockStatus" class="small"></p>										
	// 									' . wp_nonce_field( 'ajax-login-nonce', 'jmsLoginSecurity' ) . '
	// 								</form>
	// 							</div>
	// 						</div>
	// 					</div>
	// 				</div>';
	// 				$excerpt .= $this->jms_content_lock_register_modal();
	// 			}
	// 		}
	// 	}
	// 	return $excerpt;
	// }

	public function jms_content_lock_content( $content ) {
		if(!is_user_logged_in()) {
			if( is_single() && ! empty( $GLOBALS['post'] ) ) {
				if ( $GLOBALS['post']->ID == get_the_ID() ) {								
					$content = '
					<div id="jms-content-lock">
						<div class="jms-content-lock-container">
							<h4>Faça seu cadastro para continuar lendo</h4>
							<div class="row">
								<div class="col-md-6">
									<fb:login-button scope="public_profile,email" size="large" button-type="continue_with" auto-logout-link="false" use-continue-as="true" show-faces="false" onlogin="checkLoginState();"></fb:login-button>
								</div>
								<div class="col-md-6">
									<form id="jmsContentLockLogin" class="form-inline" action="login" method="post">
										<label for="email" class="sr-only">Email</label>
										<input id="jmsContentLockEmail" type="text" name="email" class="form-control form-signin-email" placeholder="Email" required autofocus>
										<button class="btn btn-primary" type="submit">Entrar</button>	
										<p id="jmsContentLockStatus" class="small"></p>										
										' . wp_nonce_field( 'ajax-login-nonce', 'jmsLoginSecurity' ) . '
									</form>
								</div>
							</div>
						</div>
					</div>';
					$content .= $this->jms_content_lock_register_modal();
				}
			}
		}
		return $content;
	}

	public function jms_content_lock_register_modal() {
		$html = '
		<!-- Modal -->
		<div class="modal fade" id="jmsContentLockRegisterModal" tabindex="-1" role="dialog" aria-labelledby="jmsContentLockRegisterModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="jmsContentLockRegisterModalLabel">Informe o seu WhatsApp para continuar lendo</h4>
					</div>
					<div class="modal-body modal-content-lock">
						<form id="jmsContentLockRegister" class="form-signin" action="register" method="post">
							<p id="jmsContentLockStatus" class="small"></p>
							' . wp_nonce_field( 'ajax-register-nonce', 'jmsRegisterSecurity' ) . '
							<input id="jmsContentLockEmail" type="hidden" name="email" required>
							<label for="phone" class="sr-only">Telefone</label>
							<input id="jmsContentLockPhone" type="tel" name="phone" class="form-control" placeholder="(99) 99999-9999" required>
							<button class="btn btn-lg btn-primary btn-block" type="submit">Cadastrar</button>	         
						</form>
					</div>
				</div>
			</div>
		</div>		
		';
		return $html;
	}
	
	public function jms_content_lock_dologin($email) { 
		if (!isset($email)) return false;
		$user = get_user_by('email', $email );
		$show_modal = false;
		$login = false;
		$message = '';
		if ($user) {
			$user_meta = get_userdata($user->ID);
			$user_roles = $user_meta->roles;
			if ( in_array( 'subscriber_lead', $user_roles, true ) ) {
				if ( ! empty( $user_meta->user_login_count ) ) {
					update_user_meta( $user->ID, 'user_login_count', ( (int) $user_meta->user_login_count + 1 ) );
					if ($user_meta->user_login_count > 1 && empty( $user_meta->user_phone )) {
						$show_modal = true;
						$message =  __('Informe o seu WhatsApp para continuar lendo.');
					}else {				
						$login = true;
						$message =  __('Login realizado com sucesso, redirecionando...');
					}
				} else {
					$login = true;
					$message =  __('Login realizado com sucesso, redirecionando...');
				}
				if ($login) {
					update_user_meta( $user->ID, 'user_login_count', 1 );
					clean_user_cache($user->ID);
					wp_clear_auth_cookie();
					wp_set_current_user($user->ID);
					wp_set_auth_cookie($user->ID, true, false);
					update_user_caches($user);
				}
				echo json_encode(array('login' => $login, 'show_modal' => $show_modal, 'message' => $message));

			}else {
				echo json_encode(array('login' => $login, 'message' => __('Login não permitido.')));
			}
		} else {
			echo json_encode(array('login' => $login, 'message' => __('Cadastre-se para continuar lendo.')));
		}

	}

	public function jms_content_lock_ajax_login() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'ajax-login-nonce' ) ) {	
			echo json_encode(array('login' => false, 'message' => 'Desculpe, nonce não verificado.'));
		} else {
			$email = sanitize_email($_POST['email']);
			if (email_exists($email)) {
				$this->jms_content_lock_dologin($email);
			} else {
				$info = array();
				$info['user_email'] = $info['user_nicename'] = $info['nickname'] = $info['display_name'] = $info['user_login'] = $email ;
				$info['user_pass'] = NULL;
				$info['role'] = 'subscriber_lead';
				// Register the user
				$user = wp_insert_user( $info );
				if ( ! is_wp_error($user) ){ 
					$this->jms_content_lock_dologin($email);
				}else {
					$error  = $user->get_error_codes() ;    
					if(in_array('empty_user_login', $error))
						echo json_encode(array('login' => false, 'message'=> __($user->get_error_message('empty_user_login'))));
					elseif(in_array('existing_user_login',$error))
						echo json_encode(array('login' => false, 'message'=> __('This username is already registered.')));
					elseif(in_array('existing_user_email',$error))
						echo json_encode(array('login' => false, 'message'=> __('This email address is already registered.')));
				}
			}
		}
		wp_die();
	}

	public function jms_content_lock_ajax_register() { 
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'ajax-register-nonce' ) ) {	
			echo json_encode(array('login' => false, 'message' => 'Desculpe, nonce não verificado.'));
		} else {
			// Nonce is checked, get the POST data and sign user on
			$email = sanitize_email($_POST['email']);
			$phone = sanitize_text_field($_POST['phone']);
			// echo json_encode(array('login' => false, 'message'=>__('Email não localizado!')));
			$user = get_user_by('email', $email );
			if ($user) {
				if ($phone) update_user_meta( $user->ID, 'user_phone', $phone );
				$this->jms_content_lock_dologin($email);
			}else {				
				echo json_encode(array('login' => false, 'message'=>__('Email não localizado!')));
			}
		}
		wp_die();
	}

	public function jms_content_lock_unique_username( $username ) {

		$username = sanitize_title( $username );
	
		static $i;
		if ( null === $i ) {
			$i = 1;
		} else {
			$i ++;
		}
		if ( ! username_exists( $username ) ) {
			return $username;
		}
		$new_username = sprintf( '%s-%s', $username, $i );
		if ( ! username_exists( $new_username ) ) {
			return $new_username;
		} else {
			return call_user_func( __FUNCTION__, $username );
		}
	}
}

$content_lock = new JMS_Content_Lock();

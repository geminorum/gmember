<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberSignUp extends gPluginModuleCore
{

	protected $priority_init    = 5;
	protected $signup_form_page = FALSE;

	public function setup_actions()
	{
		add_action( 'user_register', array( $this, 'user_register' ) );

		if ( ! is_multisite() )
			return;

		// add_filter( 'sanitize_user', array( $this, 'sanitize_username' ), 12, 3 );
		// add_filter( 'wpmu_validate_user_signup', array( $this, 'wpmu_validate_user_signup_email' ), 12, 1 );

		parent::setup_actions();
	}

	public function init()
	{
		global $gMemberNetwork;

		if ( $this->url = $gMemberNetwork->settings->get( 'signup_url', '' ) ) {

			$this->check_wp_signup();

			add_filter( 'wp_signup_location', array( $this, 'wp_signup_location' ), 15 ); // for WP
			add_filter( 'bp_get_signup_page', array( $this, 'wp_signup_location' ), 15 ); // for BuddyPress
			add_filter( 'bp_get_activation_page', array( $this, 'wp_signup_location' ), 15 ); // for BuddyPress, probably no need!

			add_action( 'login_form_register', array( $this, 'login_form_register' ), 5 ); // for direct login page
		}

		if ( $this->after = $gMemberNetwork->settings->get( 'signup_after', '' ) )
			add_filter( 'registration_redirect', array( $this, 'registration_redirect' ), 15, 1 );

		// FIXME: WORKING BUT DISABLED UNTIL COMPELETE REWRITE
		// add_shortcode( 'signup-form', array( $this, 'signup_form_shortcode' ) );
	}

	// helper
	public function check_wp_signup()
	{
		if ( is_admin() )
			return;

		$action = ! empty( $_GET['action'] ) ? $_GET['action'] : '';

		// not at the WP core signup page and action is not register
		if ( ! empty( $_SERVER['SCRIPT_NAME'] )
			&& FALSE === strpos( $_SERVER['SCRIPT_NAME'], 'wp-signup.php' )
			&& ( 'register' != $action ) )
				return;

		wp_redirect( $this->url );
		exit();
	}

	public function wp_signup_location( $url )
	{
		if ( $this->url )
			return esc_url( trailingslashit( $this->url ) );

		return $url;
	}

	public function login_form_register()
	{
		wp_redirect( $this->url );
		exit();
	}

	public function registration_redirect( $after )
	{
		if ( $this->after )
			return esc_url( trailingslashit( $this->after ) );

		return $after;
	}

	public function user_register( $user_id )
	{
		global $gMemberNetwork;

		if ( $gMemberNetwork->settings->get( 'signup_ip', TRUE ) ) {

			if ( $ip = gPluginHTTP::normalizeIP( $_SERVER['REMOTE_ADDR'] ) )
				update_user_meta( $user_id, $this->constants['meta_register_ip'], $ip );
		}

		if ( $colorscheme = $gMemberNetwork->settings->get( 'default_colorscheme', FALSE ) )
			wp_update_user( array( 'ID' => $user_id, 'admin_color' => $colorscheme ) );

		// NO NEED: we're not going to let the meta stored in the first place!
		// update_user_meta( $user_id, 'show_welcome_panel', 0 ); // http://wpengineer.com/2470/hide-welcome-panel-for-wordpress-multisite/
	}

	// we use this to modify the activation url sent by the activation email
	public function site_url( $url, $path, $scheme, $blog_id )
	{
		if ( ! $this->signup_form_page || empty( $path )
			|| FALSE === strpos( $path, 'wp-activate.php?key=' ) )
				return $url;

		return add_query_arg( 'key',
			str_replace( 'wp-activate.php?key=', '', $path ),
			$this->signup_form_page
		);
	}

	// FIXME: MUST DEP
	public function wpmu_signup_user_notification_email( $email, $user, $user_email, $key, $meta )
	{
		if ( ! $this->signup_form_page )
			return $email;

		return sprintf ( __( "To activate your user, please click the following link:\n\n%s\n\nAfter you activate, you will receive *another email* with your login." ),
			add_query_arg( 'key', $key, $this->signup_form_page )
		);
	}

	public function signup_form_shortcode( $atts, $content = NULL, $tag = '' )
	{
		global $current_site;

		if ( ! is_multisite() )
			return $content;

		if ( is_page() ) {
			$this->signup_form_page = get_page_link();
			// add_filter( 'wpmu_signup_user_notification_email', array( $this, 'wpmu_signup_user_notification_email' ), 10, 5 );
			add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
		}

		$args = shortcode_atts( array(
			'class'              => 'member-signup',
			'title'              => sprintf( __( 'Get your own %s account in seconds' ), $current_site->site_name ),
			'title_wrap'         => 'h3',
			'field_wrap'         => 'div',
			'field_wrap_class'   => 'member-signup-field',
			'logged_in_redirect' => false,
			'logged_in_text'     => __( 'You are logged in already. No need to register again!' ),
			'disabled_redirect'  => false,
			'disabled_text'      => __( 'Registration has been disabled.' ),
		), $atts, $tag );

		$labels = apply_filters( 'gmember_signup_form_labels', array(
			'user_name'  => __( 'Username:' ),
			'user_email' => __( 'Email&nbsp;Address:' ),
			'user_pass'  => __( 'Password:' ),
			'submit'     => __( 'Submit' ),
		), $args );

		$descriptions = apply_filters( 'gmember_signup_form_descriptions', array(
			'user_name'  => __( '(Must be at least 4 characters, letters and numbers only.)' ),
			'user_email' => __( 'We send your registration email to this address. (Double-check your email address before continuing.)' ),
		), $args );

		if ( isset( $_GET['key'] ) || isset( $_POST['key'] ) ) {
			$key = ! empty( $_GET['key'] ) ? $_GET['key'] : $_POST['key'];
			$current = wpmu_activate_signup( $key );
			if ( is_wp_error( $current ) ) {
				if ( 'already_active' == $current->get_error_code() || 'blog_taken' == $current->get_error_code() ) {
					$signup = $current->get_error_data();

					$activate = gPluginHTML::tag( $args['title_wrap'], array(
						'class' => 'member-signup-title member-signup-activate-title',
					), __( 'Your account is now active!' ) );

					$activate .= gPluginHTML::tag( 'p', array(
						'class' => 'lead-in member-signup-activate-text',
					), ( '' == $signup->domain.$signup->path ?
							sprintf( __( 'Your account has been activated. You may now <a href="%1$s">log in</a> to the site using your chosen username of &#8220;%2$s&#8221;. Please check your email inbox at %3$s for your password and login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can <a href="%4$s">reset your password</a>.'), network_site_url( 'wp-login.php', 'login' ), $signup->user_login, $signup->user_email, wp_lostpassword_url() ) :
							sprintf( __( 'Your site at <a href="%1$s">%2$s</a> is active. You may now log in to your site using your chosen username of &#8220;%3$s&#8221;. Please check your email inbox at %4$s for your password and login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can <a href="%5$s">reset your password</a>.'), 'http://' . $signup->domain, $signup->domain, $signup->user_login, $signup->user_email, wp_lostpassword_url() )
					) );

					defined( 'DONOTCACHEPAGE' ) or define( 'DONOTCACHEPAGE', true );
					return gPluginHTML::tag( 'div', array(
						'class' => $args['class'].' member-signup-activate',
					), $activate );

				} else {
					$activate = gPluginHTML::tag( $args['title_wrap'], array(
						'class' => 'member-signup-title member-signup-activate-title member-signup-activate-error-title',
					), __( 'An error occurred during the activation' ) );

					$activate .= gPluginHTML::tag( 'p', array(
						'class' => 'member-signup-activate-error-text',
					), $current->get_error_message() );

					defined( 'DONOTCACHEPAGE' ) or define( 'DONOTCACHEPAGE', true );
					return gPluginHTML::tag( 'div', array(
						'class' => $args['class'].' member-signup-activate-error',
					), $activate );

				}

			} else {

				$user = get_userdata( (int) $current['user_id'] );

				$activate = gPluginHTML::tag( $args['title_wrap'], array(
					'class' => 'member-signup-title member-signup-activate-title',
				), __( 'Your account is now active!' ) );

				$welcome = '<p>'.gPluginHTML::tag( 'span', array(
					'class' => 'h3',
				), $labels['user_name'] );
				$welcome .= gPluginHTML::tag( 'span', array(), $user->user_login ).'</p>';

				$welcome .= '<p>'.gPluginHTML::tag( 'span', array(
					'class' => 'h3',
				), $labels['user_pass'] );
				$welcome .= gPluginHTML::tag( 'span', array(), $current['password'] ).'</p>';

				$activate .= gPluginHTML::tag( 'div', array(
					'id' => 'signup-welcome',
					'class' => 'member-signup-activate-welcome',
				), $welcome );

				$url = get_blogaddress_by_id( (int) $current['blog_id'] );

				$activate .= gPluginHTML::tag( 'p', array(
					'class' => 'view member-signup-activate-text',
				), ( $url != network_home_url( '', 'http' ) ?
					sprintf( __('Your account is now activated. <a href="%1$s">View your site</a> or <a href="%2$s">Log in</a>'), $url, $url . 'wp-login.php' ) :
					sprintf( __('Your account is now activated. <a href="%1$s">Log in</a> or go back to the <a href="%2$s">homepage</a>.' ), network_site_url( 'wp-login.php', 'login' ), network_home_url() )
					) );

				defined( 'DONOTCACHEPAGE' ) or define( 'DONOTCACHEPAGE', true );
				return gPluginHTML::tag( 'div', array(
					'class' => $args['class'].' member-signup-activate',
				), $activate );
			}
		}

		if ( is_user_logged_in() ) {
			if ( $args['logged_in_redirect'] ) {
				wp_redirect( $args['logged_in_redirect'] ); die();
			} else {
				$logged_in = gPluginHTML::tag( 'p', array(
					'class' => 'member-signup-logged-in',
				), $args['logged_in_text'] );
				return gPluginHTML::tag( 'div', array(
					'class' => $args['class'],
				), $logged_in );
			}
		}

		$active_signup = apply_filters( 'wpmu_active_signup', get_site_option( 'registration', 'all' ) ); // return "all", "none", "blog" or "user"
		if ( $active_signup == 'none' ) {
			if ( $args['disabled_redirect'] ) {
				wp_redirect( $args['disabled_redirect'] ); die();
			} else {
				$disabled = gPluginHTML::tag( 'p', array(
					'class' => 'member-signup-disabled',
				), $args['logged_disabled'] );
				return gPluginHTML::tag( 'div', array(
					'class' => $args['class'],
				), $disabled );
			}
		}

		$current = array(
			'stage'      => isset( $_POST['stage'] ) ?  $_POST['stage'] : 'default',
			'user_name'  => isset( $_POST['user_name'] ) ? $_POST['user_name'] : '',
			'user_email' => isset( $_POST['user_email'] ) ? $_POST['user_email'] : '',
			'errors'     => new WP_Error(),
		);

		switch ( $current['stage'] ) {
			case 'validate-user-signup' : {
				// removed beacause: the filter not checked wp nounce if its not on wp-signup.php and wp_die is not acceptable!
				remove_filter( 'wpmu_validate_user_signup', 'signup_nonce_check' );
				if ( wp_create_nonce( 'member_signup_form_'.$_POST['member_signup_form_id']) != $_POST['_member_signup_form'] ) {
					$error = gPluginHTML::tag( 'p', array(
						'class' => 'member-signup-error',
					), __( 'Please try again.' ) );
					defined( 'DONOTCACHEPAGE' ) or define( 'DONOTCACHEPAGE', true );
					return gPluginHTML::tag( 'div', array(
						'class' => $args['class'],
					), $error );
				}

				$current = array_merge( $current, wpmu_validate_user_signup( $current['user_name'], $current['user_email'] ) );
				//$result = array('user_name' => $user_name, 'orig_username' => $orig_username, 'user_email' => $user_email, 'errors' => $errors);
				if ( ! $current['errors']->get_error_code() ) {

					wpmu_signup_user( $current['user_name'], $current['user_email'], apply_filters( 'add_signup_meta', array() ) );

					$confirm = gPluginHTML::tag( $args['title_wrap'], array(
						'class' => 'member-signup-title member-signup-title-confirm',
					), sprintf( __( '%s is your new username' ), $current['user_name'] ) );

					$confirm .= gPluginHTML::tag( 'p', array(), __( 'But, before you can start using your new username, <strong>you must activate it</strong>.' ) );
					$confirm .= gPluginHTML::tag( 'p', array(), sprintf( __( 'Check your inbox at <strong>%s</strong> and click the link given.' ), $current['user_email'] ) );
					$confirm .= gPluginHTML::tag( 'p', array(), __( 'If you do not activate your username within two days, you will have to sign up again.' ) );

					ob_start();
					do_action( 'signup_finished' );
					$confirm .= ob_get_clean();

					defined( 'DONOTCACHEPAGE' ) or define( 'DONOTCACHEPAGE', true );
					return gPluginHTML::tag( 'div', array(
						'class' => $args['class'].' member-signup-confirm',
					), $confirm );
				}
			}
		}

		$current = apply_filters( 'signup_user_init', $current ); // allow definition of default variables

		ob_start();
		do_action( 'preprocess_signup_form' );
		$pre = ob_get_clean();

		$header = gPluginHTML::tag( $args['title_wrap'], array(
			'class' => 'member-signup-title',
		), $args['title'] );

		$fields_hidden = gPluginHTML::tag( 'input', array(
			'type'  => 'hidden',
			'name'  => 'stage',
			'value' => 'validate-user-signup',
		), false );

		remove_action( 'signup_hidden_fields', 'signup_nonce_fields' );
		$id_nonce = mt_rand();
		$fields_hidden .= gPluginHTML::tag( 'input', array(
			'type'  => 'hidden',
			'name'  => 'member_signup_form_id',
			'value' => $id_nonce,
		), false );
		$fields_hidden .= wp_nonce_field( 'member_signup_form_'.$id_nonce, '_member_signup_form', false, false );



		ob_start();
		do_action( 'signup_hidden_fields' );
		$fields_hidden .= ob_get_clean();

		$field_user_name = gPluginHTML::tag( 'label', array(
			'for' => 'user_name',
		), $labels['user_name'] );

		if ( $username_error = $current['errors']->get_error_message( 'user_name' ) )
			$field_user_name .= gPluginHTML::tag( 'p', array(
				'class' => 'error',
			), $username_error );

		$field_user_name .= gPluginHTML::tag( 'input', array(
			'name'      => 'user_name',
			'id'        => 'user_name',
			'class'     => 'textInput',
			'type'      => 'text',
			'value'     => esc_attr( $current['user_name'] ),
			'maxlength' => '60',
		), false );

		if ( $descriptions['user_name'] )
			$field_user_name .= gPluginHTML::tag( 'p', array(
				'class' => 'description formHint',
			), $descriptions['user_name'] );

		$field_user_name = gPluginHTML::tag( $args['field_wrap'], array(
			'class' => $args['field_wrap_class'].' ctrlHolder',
		), $field_user_name );

		$field_user_email = gPluginHTML::tag( 'label', array(
			'for' => 'user_email',
		), $labels['user_email'] );

		if ( $useremail_error = $current['errors']->get_error_message( 'user_email' ) )
			$field_user_email .= gPluginHTML::tag( 'p', array(
				'class' => 'error',
			), $useremail_error );

		$field_user_email .= gPluginHTML::tag( 'input', array(
			'name'      => 'user_email',
			'id'        => 'user_email',
			'class'     => 'textInput',
			'type'      => 'text',
			'value'     => esc_attr( $current['user_email'] ),
			'maxlength' => '200',
		), false );

		if ( $descriptions['user_email'] )
			$field_user_email .= gPluginHTML::tag( 'p', array(
				'class' => 'description formHint',
			), $descriptions['user_email'] );

		$field_user_email = gPluginHTML::tag( $args['field_wrap'], array(
			'class' => $args['field_wrap_class'].' ctrlHolder',
		), $field_user_email );

		$user_email = gPluginHTML::tag( 'fieldset', array(), $field_user_name.$field_user_email );

		ob_start();
		do_action( 'signup_extra_fields', $current['errors'], $args );
		$fields_extra = ob_get_clean();

		$submit = gPluginHTML::tag( 'input', array(
			'name' => 'submit',
			'class' => 'primaryAction',
			'type' => 'submit',
			'value' => esc_attr( $labels['submit'] ),
		), false );

		$submit = gPluginHTML::tag( 'p', array(
			'class' => 'submit buttonHolder',
		), $submit );

		$form = gPluginHTML::tag( 'form', array(
			'id' => 'member_signup_form',
			'method' => 'post',
			'class' => 'uniForm',
			'action' => '', // TODO : get current url
		), $fields_hidden.$user_email.$fields_extra.$submit );

		defined( 'DONOTCACHEPAGE' ) or define( 'DONOTCACHEPAGE', true );
		return gPluginHTML::tag( 'div', array(
			'class' => $args['class'],
		), $pre.$header.$form );
	}









	// originally from : http://wordpress.org/plugins/network-username-restrictions-override/
	// email addresses can contain characters not allowed in the strict set, such as '+'.
	public function sanitize_username( $username, $raw_username, $strict )
	{
		if ( is_email( $raw_username ) ) {
			$gMemberNetwork =& gMemberNetwork::getInstance();
			if ( $gMemberNetwork->get_option( 'signup_with_email', false ) )
				return $raw_username;
		}
		return $username;
	}

	// Originally from : http://wordpress.org/plugins/network-username-restrictions-override/
	public function wpmu_validate_user_signup_email( $result )
	{
		$gMemberNetwork =& gMemberNetwork::getInstance();
		if ( ! $gMemberNetwork->get_option( 'signup_with_email', false ) )
			return $result;

		if ( ! is_wp_error( $result['errors'] ) )
			return $result;

		// WHAT TO DO? : check if user_name is_email too?

		$new_errors = new WP_Error();

		foreach ( $result['errors']->get_error_codes() as $code ) {
			$messages = $result['errors']->get_error_messages( $code );
			if ( $code == 'user_name' ) {
				foreach ( $messages as $message ) {
					if ( $message == __( 'Only lowercase letters (a-z) and numbers are allowed.' )
						|| $message == __( 'Sorry, usernames may not contain the character &#8220;_&#8221;!' ) ) {
							if ( is_email( $result['user_name'] ) )
								continue;
					}
					$new_errors->add( $code, $message );
				}
			} else {
				foreach ( $messages as $message )
					$new_errors->add( $code, $message );
			}
		}

		$result['errors'] = $new_errors;
		return $result;
	}
}

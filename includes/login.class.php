<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

// http://en.bainternet.info/2012/wordpress-easy-login-url-with-no-htaccess

class gMemberLogin extends gPluginModuleCore
{

	var $_main_site_id = 0;

	public function setup_actions()
	{
		parent::setup_actions();

		$this->_main_site_id = gPluginWPHelper::get_current_site_blog_id();

		remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
		add_filter( 'authenticate', array( $this, 'authenticate' ), 20, 3 );

		add_action( 'login_form', array( $this, 'login_form' ) );
		add_filter( 'wp_login_errors', array( $this, 'wp_login_errors' ), 20, 2 );

		// WORKING : add settings
		// allow only one session per user
		// add_action( 'wp_login', 'wp_destroy_other_sessions' );
	}

	public function init()
	{
		// working, but disabled due to problem with redirects on wp networks
		// $this->redirect_to_main_site();
		// add_filter( 'login_url', array( $this, 'login_url' ), 10, 2 );

		add_action( 'wp_login', array( $this, 'wp_login' ), 10, 2 );
		add_filter( 'login_message', array( $this, 'login_message' ) );
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 11, 3 );
		add_filter( 'logout_redirect', array( $this, 'logout_redirect' ), 11, 3 );

		add_filter( 'allow_password_reset', array( $this, 'allow_password_reset' ), 10, 2 );
	}

	public function admin_init()
	{
		add_action( 'personal_options', array( $this, 'personal_options' ), 8, 1 );
		add_action( 'personal_options_update', array( $this, 'edit_user_profile_update' ), 10, 1 );
		add_action( 'edit_user_profile_update', array( $this, 'edit_user_profile_update' ), 10, 1 );
	}

	// originally from : http://wordpress.org/extend/plugins/wp-email-login/ v4.6.4
	public function authenticate( $user, $username, $password )
	{
		if ( is_a( $user, 'WP_User' ) )
			return $user;

		if ( ! empty( $username ) ) {
			$username = str_replace( '&', '&amp;', stripslashes( $username ) );
			$user = get_user_by( 'email', $username );
			if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status )
				$username = $user->user_login;
		}

		return wp_authenticate_username_password( NULL, $username, $password );
	}

	// originally from : http://wordpress.org/extend/plugins/wp-email-login/ v4.6.4
	public function login_form()
	{
?><script type="text/javascript">
/* <![CDATA[ */
	if ( document.getElementById('loginform') )
		document.getElementById('loginform').childNodes[1].childNodes[1].childNodes[0].nodeValue = '<?php echo esc_js( __( 'Username or Email', GMEMBER_TEXTDOMAIN ) ); ?>';

	if ( document.getElementById('login_error') )
		document.getElementById('login_error').innerHTML = document.getElementById('login_error').innerHTML.replace( '<?php echo esc_js( __( 'username' ) ); ?>', '<?php echo esc_js( __( 'Username or Email' , GMEMBER_TEXTDOMAIN ) ); ?>' );
/* ]]> */
</script><?php
	}

	// https://gist.github.com/norcross/ba1dd4e89223b10c1f2d#comment-1241696
	public function wp_login_errors( $errors, $redirect_to )
	{
		if ( isset( $errors->errors['invalid_username'] ) )
			@$errors->errors['invalid_username'][0] = sprintf( __( '<strong>ERROR</strong>: Invalid Username or Email. <a href="%s" title="Password Lost and Found">Lost your password</a>?', GMEMBER_TEXTDOMAIN ), wp_lostpassword_url() );

		if ( isset( $errors->errors['empty_username'] ) )
			@$errors->errors['empty_username'][0] = __( '<strong>ERROR</strong>: The username/Email field is empty.', GMEMBER_TEXTDOMAIN );

		return $errors;
	}

	public function personal_options( $profileuser )
	{
		$edit_users = current_user_can( 'edit_users' );
		$date_format = _x( 'M j, Y @ G:i', 'Registered/Last Login date format', GMEMBER_TEXTDOMAIN );

		$register_date = strtotime( $profileuser->user_registered );
		$register_on = date_i18n( $date_format, $register_date ).
			' <small><small><span class="description">('.
			sprintf( __( '%s ago', GMEMBER_TEXTDOMAIN ), apply_filters( 'string_format_i18n', human_time_diff( $register_date ) ) ).
			')</span></small></small>';

		if ( isset( $profileuser->{$this->constants['meta_lastlogin']} ) && '' != $profileuser->{$this->constants['meta_lastlogin']} ) {
			$lastlogin_date = strtotime( $profileuser->{$this->constants['meta_lastlogin']} );
			$lastlogin = date_i18n( $date_format, $lastlogin_date ).
				' <small><small><span class="description">('.
				sprintf( __( '%s ago', GMEMBER_TEXTDOMAIN ), apply_filters( 'string_format_i18n', human_time_diff( $lastlogin_date ) ) ).
				')</span></small></small>';
		} else {
			$lastlogin = __( 'No Data Available', GMEMBER_TEXTDOMAIN );
		}

		$nicename = $profileuser->user_login == $profileuser->user_nicename
			? $this->sanitize_slug( $profileuser->display_name )
			: $profileuser->user_nicename;

		?><tr><th><br /></th><td><br /></td></tr><?php
		if ( $edit_users && isset( $profileuser->{$this->constants['meta_register_ip']} ) ) {
			?><tr class="register_ip">
				<th><?php _e( 'Registration IP', GMEMBER_TEXTDOMAIN ); ?></th>
				<td><code><?php echo $profileuser->{$this->constants['meta_register_ip']}; ?></code></td>
			</tr><?php }
		?><tr class="register_date">
			<th><?php _e( 'Registration on', GMEMBER_TEXTDOMAIN ); ?></th>
			<td><?php echo $register_on; ?></td>
		</tr>
		<tr class="last_login">
			<th><?php _e( 'Last Login', GMEMBER_TEXTDOMAIN ); ?></th>
			<td><?php echo $lastlogin; ?></td>
		</tr><?php

		if ( ! IS_PROFILE_PAGE && current_user_can( 'edit_users' ) ) {
			?><tr><th><label for="gmember_disable_user"><?php
				_e( ' Disable User Account', GMEMBER_TEXTDOMAIN );
				?></label></th><td>
				<input type="checkbox" name="gmember_disable_user" id="gmember_disable_user" value="1" <?php checked( 1, get_the_author_meta( $this->constants['meta_disable_user'], $profileuser->ID ) ); ?> />
				<span class="description"><?php _e( 'If checked, the user cannot login with this account.' , GMEMBER_TEXTDOMAIN ); ?></span>
			</td></tr><?php

			?><tr><th><label for="gmember_password_reset"><?php
				_e( ' Disable Password Reset', GMEMBER_TEXTDOMAIN );
				?></label></th><td>
				<input type="checkbox" name="gmember_password_reset" id="gmember_password_reset" value="1" <?php checked( 1, get_the_author_meta( $this->constants['meta_disable_password_reset'], $profileuser->ID ) ); ?> />
				<span class="description"><?php _e( 'If checked, the user cannot reset his password via wp-login.php' , GMEMBER_TEXTDOMAIN ); ?></span>
			</td></tr><?php
		}

		?><tr><th><br /></th><td><br /></td></tr>
		<tr>
			<th><label for="gmember-slug"><?php _e( 'Slug', GMEMBER_TEXTDOMAIN ); ?></label></th>
			<td><input type="text" name="gmember_slug" id="gmember_slug" value="<?php echo esc_attr( $nicename ); ?>" class="regular-text" dir="ltr" <?php if ( ! $edit_users ) echo 'readonly="readonly"'; ?>/>
			<p class="description"><?php _e( 'This will be used in the URL of the user\'s page', GMEMBER_TEXTDOMAIN ); ?></p></td>
		</tr><?php
	}

	public function edit_user_profile_update( $user_id )
	{
		if ( isset( $_POST['gmember_slug'] ) && $_POST['gmember_slug'] ) {
			$sanitized = self::sanitize_slug( $_POST['gmember_slug'] );
			if ( ! username_exists( $sanitized ) )
				wp_update_user( array(
					'ID'            => $user_id,
					'user_nicename' => $sanitized,
				) );
		}

		if ( current_user_can( 'edit_users' ) ) {
			if ( isset( $_POST['gmember_disable_user'] ) ) {
				update_user_meta( $user_id, $this->constants['meta_disable_user'], '1' );
			} else {
				delete_user_meta( $user_id, $this->constants['meta_disable_user'] );
			}
			if ( isset( $_POST['gmember_password_reset'] ) ) {
				update_user_meta( $user_id, $this->constants['meta_disable_password_reset'], '1' );
			} else {
				delete_user_meta( $user_id, $this->constants['meta_disable_password_reset'] );
			}
		}
	}

	// FIXME: UNFINISHED
	// use this on sanitize
	public static function update_nicename()
	{
		$user_nicename_check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1" , $user_nicename, $user_login));

		if ( $user_nicename_check ) {
			$suffix = 2;
			while ($user_nicename_check) {
				$alt_user_nicename = $user_nicename . "-$suffix";
				$user_nicename_check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1" , $alt_user_nicename, $user_login));
				$suffix++;
			}
			$user_nicename = $alt_user_nicename;
		}
	}

	public function sanitize_slug( $string )
	{
		//TODO : more options like transliterate
		return sanitize_title( $string );
	}

	// filter whether to allow a password to be reset.
	public function allow_password_reset( $allow, $user_id )
	{
		$allowed = get_user_meta( $user_id, $this->constants['meta_disable_password_reset'], TRUE );

		if ( $allowed == '1' )
			return FALSE;

		return $allow;
	}

	public function wp_login( $user_login, $user )
	{
		global $gMemberNetwork;

		if ( $gMemberNetwork->settings->get( 'store_lastlogin', TRUE ) )
			update_user_meta( $user->ID, $this->constants['meta_lastlogin'], current_time( 'mysql', TRUE ) );

		// After login check to see if user account is disabled
		$disabled = get_user_meta( $user->ID, $this->constants['meta_disable_user'], TRUE );
		if ( $disabled == '1' ) {
			wp_clear_auth_cookie();
			wp_redirect( add_query_arg( array( 'disabled' => '1' ), $this->get_main_site_login() ) );
			exit;
		}
	}

	public function login_message( $message )
	{
		// Show the error message if it seems to be a disabled user
		if ( isset( $_GET['disabled'] ) && $_GET['disabled'] == 1 )
			$message = '<div id="login_error">'.apply_filters( 'gmember_disable_users_notice',
				__( 'Account disabled', GMEMBER_TEXTDOMAIN ) ).'</div>';

		return $message;
	}

	public function redirect_to_main_site()
	{
		if ( ! is_user_logged_in() ) {
			if ( FALSE !== strpos( $_SERVER['SCRIPT_NAME'], 'wp-login.php' ) ) {
				if (  $this->_main_site_id != get_current_blog_id() ) {

					$login_url = $this->get_main_site_login();
					$redirect = empty( $_REQUEST['redirect_to'] ) ? FALSE : $_REQUEST['redirect_to'];

					if ( $redirect )
						$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );

					wp_redirect( $login_url );
					exit();
				}
			}
		}
	}

	// ALSO SEE : http://wp.tutsplus.com/tutorials/creative-coding/redirect-users-to-custom-pages-by-role/
	// ALSO SEE : http://www.paulund.co.uk/redirect-login-wordpress
	public function login_redirect( $redirect_to, $requested_redirect_to, $user )
	{
		if ( ! empty( $requested_redirect_to ) )
			return $requested_redirect_to;

		if ( ! is_wp_error( $user ) && $user->has_cap( 'edit_posts' ) )
			return get_admin_url();

		return get_option( 'home' );
	}

	public function logout_redirect( $redirect_to, $requested_redirect_to, $user )
	{
		if ( ! empty( $requested_redirect_to ) )
			return $requested_redirect_to;

		return get_option( 'home' );
	}

	public function login_url( $login_url, $redirect )
	{
		if ( is_user_logged_in() || $this->_main_site_id == get_current_blog_id() )
			return $login_url;

		if ( ! empty( $redirect ) )
			return add_query_arg( 'redirect_to', urlencode( $redirect ), $this->get_main_site_login() );

		return $this->get_main_site_login();
	}

	// Originally from : https://github.com/boonebgorges/login-on-main-site
	public function get_main_site_login()
	{
		return get_blog_option( $this->_main_site_id, 'siteurl' ).'/wp-login.php';
	}
}

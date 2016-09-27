<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberLogin extends gPluginModuleCore
{

	protected $main_site_id = 0;

	public function setup_actions()
	{
		parent::setup_actions();

		$this->main_site_id = gPluginWPHelper::getCurrentSiteBlogID();

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
		add_action( 'user_edit_form_tag', array( $this, 'user_edit_form_tag' ), 99 );
		add_action( 'personal_options_update', array( $this, 'edit_user_profile_update' ), 10, 1 );
		add_action( 'edit_user_profile_update', array( $this, 'edit_user_profile_update' ), 10, 1 );
	}

	public function user_edit_form_tag()
	{
		global $gMemberNetwork, $profileuser;

		$store_lastlogin = $gMemberNetwork->settings->get( 'store_lastlogin', TRUE );
		$date_format     = _x( 'M j, Y @ G:i', 'Registered/Last Login date format', GMEMBER_TEXTDOMAIN );

		echo '><h2>'.__( 'Account Information', GMEMBER_TEXTDOMAIN ).'</h2>';
		echo '<table class="form-table">';

		if ( isset( $profileuser->{$this->constants['meta_register_ip']} )
			&& $profileuser->{$this->constants['meta_register_ip']} )
				$register_ip = $gMemberNetwork->getIPLookup( $profileuser->{$this->constants['meta_register_ip']} );
		else
			$register_ip = __( 'No Data Available', GMEMBER_TEXTDOMAIN );

		echo '<tr class="register_ip"><th>'.__( 'Registration IP', GMEMBER_TEXTDOMAIN )
			.'</th><td><code>'.$register_ip.'</code></td></tr>';

		$register_date = strtotime( $profileuser->user_registered );
		$register_on = date_i18n( $date_format, $register_date ).
			' <small><small><span class="description">('.
			sprintf( __( '%s ago', GMEMBER_TEXTDOMAIN ), apply_filters( 'string_format_i18n', human_time_diff( $register_date ) ) ).
			')</span></small></small>';

		echo '<tr class="register_date"><th>'
				.__( 'Registration on', GMEMBER_TEXTDOMAIN )
			.'</th><td>'
				.$register_on
			.'</td></tr>';

		if ( $store_lastlogin || current_user_can( 'edit_users' ) ) {

			if ( isset( $profileuser->{$this->constants['meta_lastlogin']} ) && '' != $profileuser->{$this->constants['meta_lastlogin']} ) {
				$lastlogin_date = strtotime( $profileuser->{$this->constants['meta_lastlogin']} );
				$lastlogin = date_i18n( $date_format, $lastlogin_date ).
					' <small><small><span class="description">('.
					sprintf( __( '%s ago', GMEMBER_TEXTDOMAIN ), apply_filters( 'string_format_i18n', human_time_diff( $lastlogin_date ) ) ).
					')</span></small></small>';
			} else {
				$lastlogin = '<code>'.__( 'No Data Available', GMEMBER_TEXTDOMAIN ).'</code>';
			}

			echo '<tr class="last_login'.( $store_lastlogin ? '' : ' error' ).'"><th>'
					.__( 'Last Login', GMEMBER_TEXTDOMAIN )
				.'</th><td>'
					.$lastlogin
					.( $store_lastlogin ? '' : ' &mdash; <strong>'.__( 'Last Logins are Disabled', GMEMBER_TEXTDOMAIN ).'</strong>' )
				.'</td></tr>';
		}

		if ( ! IS_PROFILE_PAGE && current_user_can( 'edit_users' ) ) {

			echo '</table><h2>'.__( 'Administrative Options', GMEMBER_TEXTDOMAIN ).'</h2>';
			echo '<table class="form-table">';

			$nicename = $profileuser->user_login == $profileuser->user_nicename
				? $this->sanitize_slug( $profileuser->display_name )
				: $profileuser->user_nicename;

			echo '<tr><th><label for="gmember-slug">'.__( 'Slug', GMEMBER_TEXTDOMAIN )
				.'</label></th><td><input type="text" name="gmember_slug" id="gmember_slug" value="'
				.esc_attr( $nicename ).'" class="regular-text" dir="ltr"'
				.( current_user_can( 'edit_users' ) ? '' : ' readonly="readonly" disabled="disabled"' )
				.' /><p class="description">'.
					__( 'This will be used in the URL of the user\'s page', GMEMBER_TEXTDOMAIN )
				.'</p></td></tr>';

			echo '<tr><th>'.__( 'Account Login', GMEMBER_TEXTDOMAIN )
				.'</th><td><label for="gmember_disable_user">'
				.'<input type="checkbox" name="gmember_disable_user" id="gmember_disable_user" value="1"';
					checked( 1, get_the_author_meta( $this->constants['meta_disable_user'], $profileuser->ID ) );
			echo ' /> '.__( 'Disable user login with this account', GMEMBER_TEXTDOMAIN )
				.'</label></td></tr>';

			echo '<tr><th>'.__( 'Password Reset', GMEMBER_TEXTDOMAIN )
				.'</th><td><label for="gmember_password_reset">'
				.'<input type="checkbox" name="gmember_password_reset" id="gmember_password_reset" value="1"';
					checked( 1, get_the_author_meta( $this->constants['meta_disable_password_reset'], $profileuser->ID ) );
			echo ' /> '.__( 'Disable this account password reset via wp-login.php', GMEMBER_TEXTDOMAIN )
				.'</label></td></tr>';
		}

		echo '</table'; // it's correct, checkout the hook!
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
		$user_nicename_check = $wpdb->get_var( $wpdb->prepare( "
			SELECT ID FROM $wpdb->users
			WHERE user_nicename = %s
			AND user_login != %s
			LIMIT 1
		" , $user_nicename, $user_login ) );

		if ( $user_nicename_check ) {

			$suffix = 2;

			while ( $user_nicename_check ) {

				$alt_user_nicename = $user_nicename."-$suffix";

				$user_nicename_check = $wpdb->get_var( $wpdb->prepare( "
					SELECT ID FROM $wpdb->users
					WHERE user_nicename = %s
					AND user_login != %s
					LIMIT 1
				" , $alt_user_nicename, $user_login ) );

				$suffix++;
			}

			$user_nicename = $alt_user_nicename;
		}
	}

	public function sanitize_slug( $string )
	{
		// TODO: more options like transliterate
		return sanitize_title( $string );
	}

	// filter whether to allow a password to be reset.
	public function allow_password_reset( $allow, $user_id )
	{
		if ( '1' == get_user_meta( $user_id, $this->constants['meta_disable_password_reset'], TRUE ) )
			return FALSE;

		return $allow;
	}

	public function wp_login( $user_login, $user )
	{
		global $gMemberNetwork;

		if ( $gMemberNetwork->settings->get( 'store_lastlogin', TRUE ) )
			update_user_meta( $user->ID, $this->constants['meta_lastlogin'], current_time( 'mysql', TRUE ) );

		// after login check to see if user account is disabled
		if ( '1' == get_user_meta( $user->ID, $this->constants['meta_disable_user'], TRUE ) ) {
			wp_clear_auth_cookie();
			wp_redirect( add_query_arg( array( 'disabled' => '1' ), $this->get_main_site_login() ) );
			exit;
		}
	}

	public function login_message( $message )
	{
		// show the error message if it seems to be a disabled user
		if ( isset( $_GET['disabled'] ) && $_GET['disabled'] == 1 )
			$message = '<div id="login_error">'.apply_filters( 'gmember_disable_users_notice',
				__( 'Account disabled', GMEMBER_TEXTDOMAIN ) ).'</div>';

		return $message;
	}

	public function redirect_to_main_site()
	{
		if ( ! is_user_logged_in() ) {
			if ( FALSE !== strpos( $_SERVER['SCRIPT_NAME'], 'wp-login.php' ) ) {
				if (  $this->main_site_id != get_current_blog_id() ) {

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

	public function login_redirect( $redirect_to, $requested_redirect_to, $user )
	{
		if ( defined( 'DOING_AJAX' ) )
			return $redirect_to;

		if ( is_wp_error( $user ) )
			return $redirect_to;

		if ( empty( $requested_redirect_to ) )
			return $user->has_cap( 'edit_posts' ) ? get_admin_url() : get_home_url();

		return $redirect_to;
	}

	public function logout_redirect( $redirect_to, $requested_redirect_to, $user )
	{
		if ( ! empty( $requested_redirect_to ) )
			return $requested_redirect_to;

		return get_option( 'home' );
	}

	public function login_url( $login_url, $redirect )
	{
		if ( is_user_logged_in() || $this->main_site_id == get_current_blog_id() )
			return $login_url;

		if ( ! empty( $redirect ) )
			return add_query_arg( 'redirect_to', urlencode( $redirect ), $this->get_main_site_login() );

		return $this->get_main_site_login();
	}

	// Originally from : https://github.com/boonebgorges/login-on-main-site
	public function get_main_site_login()
	{
		return get_blog_option( $this->main_site_id, 'siteurl' ).'/wp-login.php';
	}
}

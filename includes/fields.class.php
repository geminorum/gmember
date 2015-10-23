<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberFields extends gPluginModuleCore
{

	public function init()
	{
		if ( is_admin() ) {
			add_filter( 'show_user_profile', array( $this, 'edit_user_profile' ), 10, 1  );
			add_action( 'edit_user_profile', array( $this, 'edit_user_profile' ), 10, 1  );
			add_action( 'personal_options_update', array( $this, 'edit_user_profile_update' ), 10, 1 );
			add_action( 'edit_user_profile_update', array( $this, 'edit_user_profile_update' ), 10, 1 );
		}
	}

	public function edit_user_profile( $profileuser )
	{
		// gPluginUtils::dump( $profileuser );
		// gPluginUtils::dump( $profileuser->{$this->_extra_meta} );

		$fields = $this->getFilters( 'gmember_extra_meta' );
		if ( isset( $profileuser->{$this->_extra_meta} ) )
			$extra = gMemberHelper::generateMetaArray( $fields, $profileuser->{$this->_extra_meta} );
		else
			$extra = gMemberHelper::generateMetaArray( $fields );

		// require_once( self::getLayout( 'profile.extra' ) );
		echo gMemberHelper::displayMetaArray( $extra, $profileuser->ID, 'profile-extra-admin' );

		// TODO: add nounce
	}

	public function edit_user_profile_update( $user_id )
	{
		add_action ( 'user_profile_update_errors', array( $this, 'user_profile_update_errors' ), 10, 3 );
	}







	var $_option_group = 'gmember_profile_options';
	var $_extra_meta = 'gmember_extra';
	var $_signup_meta = false;


	function init_OLD()
	{
		if ( is_multisite() ) {
			$gMemberNetwork = gMemberNetwork::getInstance();
			if ( $gMemberNetwork->get_option( 'signup_extra', false ) ) {
				add_action( 'signup_extra_fields', array( $this, 'signup_extra_fields' ), 10 , 2 );
				add_action( 'wpmu_validate_user_signup', array( $this, 'wpmu_validate_user_signup' ), 10 );
				add_action( 'add_signup_meta', array( $this, 'add_signup_meta' ), 10 );
				add_action( 'wpmu_activate_user', array( $this, 'wpmu_activate_user' ), 10, 3 );
			}
		}
	}

	function admin_init_OLD()
	{
		// add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
		// add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	function admin_print_styles()
	{
		if ( strpos( $_SERVER['REQUEST_URI'], 'profile.php' ) || strpos( $_SERVER['REQUEST_URI'], 'user-edit.php' ) )
			echo '<link rel="stylesheet" href="'.GMEMBER_URL.'assets/css/profile'.( is_rtl() ? '-rtl' : '' ).'.css" type="text/css" />';
	}


	function user_profile_update_errors( &$errors, $update, &$user )
	{
		//TODO: check nounce

		$fields = $this->getFilters( 'gmember_extra_meta' );
		if ( ! count( $fields ) ) // maybe filtered
			return;

		$saved = get_user_meta( $user->ID, $this->_extra_meta, true );
		$extra = gMemberHelper::buildMetaArray( $fields, $user->ID, $saved );
		$extra = gMemberHelper::sanitizeMetaArray( $errors, $extra, $fields, $user->ID, $saved  );

		if ( count( $extra ) ) {
			update_user_meta( $user->ID, $this->_extra_meta, $extra );
		} else {
			delete_user_meta( $user->ID, $this->_extra_meta );
		}

	}

	function add_signup_meta( $meta )
	{
		// $fields = $this->getFilters( 'gmember_extra_meta' );
		// if ( ! count( $fields ) ) // maybe filtered
		// 	return;
		//
		// $errors = new WP_Error();
		// $extra = gMemberHelper::buildMetaArray( $fields, false, array(), 'member' );
		// $extra = gMemberHelper::sanitizeMetaArray( $errors, $extra, $fields, false );

		if ( ! $this->_signup_meta )
			return $meta;

		if ( ! is_array( $meta ) )
			$meta = array();

		if ( isset( $this->_signup_meta['extra'] ) && count( $this->_signup_meta['extra'] ) )
			$meta = array_merge( $meta, array( $this->_extra_meta => $this->_signup_meta['extra'] ) );

		if ( isset( $this->_signup_meta['contacts'] ) && count( $this->_signup_meta['contacts'] ) )
			$meta = array_merge( $meta, array( 'contacts' => $this->_signup_meta['contacts'] ) );

		return $meta;
	}

	function wpmu_activate_user( $user_id, $password, $meta )
	{
		if ( is_array( $meta ) ) {
			if ( array_key_exists( $this->_extra_meta, $meta ) && count( $meta[$this->_extra_meta] ) )
				update_user_meta( $user_id, $this->_extra_meta, $meta[$this->_extra_meta] );
			if ( array_key_exists( 'contacts', $meta ) && count( $meta['contacts'] ) )
				foreach ( _wp_get_user_contactmethods() as $method => $name )
					update_user_meta( $user_id, $method, $meta['contacts'][$method] );
		}
	}

	function wpmu_validate_user_signup( $result )
	{
		$fields = $this->getFilters( 'gmember_extra_meta' );
		if ( ! count( $fields ) ) // maybe filtered
			return $result;

		$this->_signup_meta = array();
		$extra = gMemberHelper::buildMetaArray( $fields, false, array(), 'member' );
		$this->_signup_meta['extra'] = gMemberHelper::sanitizeMetaArray( $result['errors'], $extra, $fields, false );

		foreach ( _wp_get_user_contactmethods() as $method => $name )
			if ( isset( $_POST['contacts-'.$method] ) )
				$this->_signup_meta['contacts'][$method] = sanitize_text_field( $_POST['contacts-'.$method] );

		return $result;
	}

	function signup_extra_fields( $errors, $args = null )
	{

		echo gMemberHelper::displayContactMethods( 'contacts' );

		$fields = $this->getFilters( 'gmember_extra_meta' );
		if ( ! count( $fields ) ) // maybe filtered
			return;

		$extra = gMemberHelper::generateMetaArray( $fields, ( is_array( $this->_signup_meta ) ? $this->_signup_meta : array() ), $errors );

		echo gMemberHelper::displayMetaArray( $extra, false, 'profile-extra-signup', 'member', 'gmember_extra_data_signup' );
	}

	// Refer to /wp-admin/includes/template.php to see how the default pointer is done.
	// https://github.com/billerickson/Password-Pointer/blob/master/plugin.php
	// by https://github.com/billerickson/
	function admin_enqueue_scripts( $hook_suffix )
	{
		$enqueue = false;

		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		// TODO : add button on profile to reset the password nag notification by admins

		if ( ! in_array( 'gmember_password_nag', $dismissed ) ) {
			$enqueue = true;
			add_action( 'admin_print_footer_scripts', array( $this, 'pointer_admin_print_footer_scripts' ) );
		}

		if ( $enqueue ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
		}
	}


	function pointer_admin_print_footer_scripts()
	{
		$pointer_content = '<h3>'.__( 'Change your password!', GMEMBER_TEXTDOMAIN ).'</h3>';
		$pointer_content .= '<p>'.__( 'Change your randomly generated password to one that you will remember.', GMEMBER_TEXTDOMAIN ).'</p>';

		?>
		<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			$('#wp-admin-bar-my-account').pointer({
				content: '<?php echo $pointer_content; ?>',
				position: 'top',
				pointerWidth: 200,
				close: function() {
					$.post( ajaxurl, {
						pointer: 'gmember_password_nag',
						action: 'dismiss-wp-pointer'
					});
				}
			}).pointer('open');
		});
		//]]>
		</script>
		<?php
	}

}

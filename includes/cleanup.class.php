<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberCleanUp extends gPluginModuleCore
{

	public function setup_actions()
	{
		add_filter( 'update_user_metadata', array( $this, 'update_user_metadata' ), 12, 5 );
		add_filter( 'get_user_metadata', array( $this, 'get_user_metadata' ), 12, 4 );
		add_filter( 'insert_user_meta', array( $this, 'insert_user_meta' ), 12, 3 );
		add_filter( 'get_user_option_rich_editing', array( $this, 'get_user_option_option' ), 8, 3 );
		add_filter( 'get_user_option_comment_shortcuts', array( $this, 'get_user_option_option' ), 8, 3 );
	}

	public function update_user_metadata( $null, $object_id, $meta_key, $meta_value, $prev_value )
	{
		// prevent BP last activity back-comp, SEE: http://wp.me/pLVLj-gc
		if ( function_exists( 'buddypress' ) && 'last_activity' === $meta_key )
			return TRUE;

		if ( array_key_exists( $meta_key, wp_get_user_contact_methods( $object_id ) ) ) {
			if ( ! $meta_value ) {

				if ( get_metadata( 'user', $object_id, $meta_key ) )
					delete_metadata( 'user', $object_id, $meta_key );

				return TRUE;
			}
		}

		return $null;
	}

	public function get_user_metadata( $null, $object_id, $meta_key, $single )
	{
		if ( 'show_welcome_panel' == $meta_key )
			return 0;

		return $null;
	}

	// TODO: add bulk actions to remove existing empty default user metas
	public function insert_user_meta( $meta, $user, $update )
	{
		if ( ! $update && isset( $meta['nickname'] ) && $user->user_login == $meta['nickname'] ) {
			// TODO: get default from plugin options
			if ( isset( $meta['last_name'] ) && $meta['last_name'] )
				$meta['nickname'] = $meta['last_name'];
		}

		foreach ( $this->get_default_user_meta() as $key => $value ) {
			if ( isset( $meta[$key] ) && $value == $meta[$key] ) {
				unset( $meta[$key] );
				if ( $update )
					delete_user_meta( $user->ID, $key );
			}
		}

		return $meta;
	}

	private function get_default_user_meta()
	{
		return array(
			'nickname'             => '',
			'first_name'           => '',
			'last_name'            => '',
			'description'          => '',
			'rich_editing'         => 'true',
			'comment_shortcuts'    => 'false',
			'admin_color'          => 'fresh',
			'use_ssl'              => 0,
			'show_admin_bar_front' => 'true',
			'locale'               => '',
		);
	}

	public function get_user_option_option( $result, $option, $user )
	{
		if ( FALSE === $result ) {
			$defaults = $this->get_default_user_meta();
			if ( isset( $defaults[$option] ) )
				return $defaults[$option];
		}

		return $result;
	}

	// FIXME: UNFINISHED
	public function network_settings_html( $settings_uri, $sub )
	{
		global $gMemberNetwork;

		echo '<form method="post" action="">';

			// $gMemberNetwork->spam->user_form( $gMemberNetwork->spam->user_list() );

			submit_button( __( 'Submit Changes', GMEMBER_TEXTDOMAIN ) );
		echo '</form>';
	}
}

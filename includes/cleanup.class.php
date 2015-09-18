<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberCleanUp extends gPluginModuleCore
{

	public function setup_actions()
	{
		add_filter( 'get_user_metadata', array( &$this, 'get_user_metadata' ), 12, 4 );
		add_filter( 'update_user_metadata', array( &$this, 'update_user_metadata' ), 12, 5 );
		add_filter( 'insert_user_meta', array( &$this, 'insert_user_meta' ), 12, 2 ); // since WP4.4
	}

	public function get_user_metadata( $null, $object_id, $meta_key, $single )
	{
		if ( 'show_welcome_panel' == $meta_key )
			return 0;

		return $null;
	}

	public function update_user_metadata( $null, $object_id, $meta_key, $meta_value, $prev_value )
	{
		$skip = array(
			'show_welcome_panel',
			'comment_shortcuts',
			'rich_editing',
			'admin_color',
			'use_ssl',
			'show_admin_bar_front',
		);

		if ( in_array( $meta_key, $skip ) )
			return TRUE;

		// FIXME: what if deleting?!
		// if ( 'description' == $meta_key && empty( $meta_value ) )
		// 	return TRUE;

		return $null;
	}

	public function insert_user_meta( $meta, $user )
	{
		if ( isset( $meta['nickname'] ) && $meta['nickname'] == $user->user_login ) {
			// TODO: get default from gmember options
			if ( isset( $meta['last_name'] ) && $meta['last_name'] )
				$meta['nickname'] = $meta['last_name'];
		}

		// if ( isset( $meta['comment_shortcuts'] ) && ! $meta['comment_shortcuts'] )
		// 	unset( $meta['comment_shortcuts'] );

		if ( isset( $meta['admin_color'] ) && 'fresh' == $meta['admin_color'] )
			unset( $meta['admin_color'] );

		if ( isset( $meta['show_admin_bar_front'] ) && $meta['show_admin_bar_front'] )
			unset( $meta['show_admin_bar_front'] );

		foreach ( $meta as $meta_key => $meta_val )
			if ( empty( $meta_val ) )
				unset( $meta[$meta_key] );

		// gPluginWPHelper::log( 'INSERT USER META', array(
		// 	'user'     => $user, //$user->user_login,
		// 	'meta'     => $meta,
		// ) );

		return $meta;
	}

	// FIXME: UNFINISHED
	public function network_settings_html( $settings_uri, $sub )
	{
		global $gMemberNetwork;

		echo '<form method="post" action="">';

			// $gMemberNetwork->spam->user_form( $gMemberNetwork->spam->user_list() );

			submit_button( __( 'Submit Changes',  GMEMBER_TEXTDOMAIN ) );
		echo '</form>';
	}
}

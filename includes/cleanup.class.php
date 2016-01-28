<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberCleanUp extends gPluginModuleCore
{

	public function setup_actions()
	{
		add_filter( 'get_user_metadata', array( $this, 'get_user_metadata' ), 12, 4 );
		add_filter( 'insert_user_meta', array( $this, 'insert_user_meta' ), 12, 3 );
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

		$defaults = array(
            'nickname'             => '',
            'first_name'           => '',
            'last_name'            => '',
            'description'          => '',
            'rich_editing'         => 'true',
            'comment_shortcuts'    => 'false',
            'admin_color'          => 'fresh',
            'use_ssl'              => 0,
            'show_admin_bar_front' => 'true',
		);

		foreach ( $defaults as $key => $value ) {
			if ( isset( $meta[$key] ) && $value == $meta[$key] ) {
				unset( $meta[$key] );
				if ( $update )
					delete_user_meta( $user->ID, $key );
			}
		}

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

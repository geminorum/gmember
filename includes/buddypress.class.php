<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberBuddyPress extends gPluginModuleCore
{

	public function setup_actions()
	{
		add_filter( 'bp_displayed_user_fullname', array( $this, 'bp_displayed_user_fullname' ), 9 );
		add_filter( 'bp_get_member_name', array( $this, 'bp_get_member_name' ), 9 );
	}

	public function bp_displayed_user_fullname( $default )
	{
		if ( ! buddypress()->displayed_user->userdata )
			return $default;

		buddypress()->displayed_user->userdata->id = buddypress()->displayed_user->userdata->ID;

		return $this->display_name( buddypress()->displayed_user->userdata, $default );
	}

	public function bp_get_member_name( $fullname )
	{
		global $members_template;

		if ( isset( $members_template->member ) )
			return $this->display_name( $members_template->member, $fullname );

		return $fullname;
	}

	// Based on : BP Display Name v1.0 by ScenicJobs.com
	// http://scenicjobs.com/wordpress-plugins
	public function display_name( $userdata, $default )
	{
		global $gMemberNetwork;

		$display_name = NULL;

		switch( $gMemberNetwork->settings->get( 'bp_display_name', 'nickname' ) ) {

			case 'default' :

				$display_name = $default;

			break;
			case 'first_last_name' :

				$display_name = sprintf( ' %s %s',
					get_user_meta( $userdata->id, 'first_name', TRUE ),
					get_user_meta( $userdata->id, 'last_name' , TRUE ) );

			break;
			case 'username' :

				$display_name = $user->user_login;

			break;
			case 'first_name' :

				$display_name = get_user_meta( $userdata->id, 'first_name', TRUE );

			break;
			case 'last_name' :

				$display_name = get_user_meta( $userdata->id, 'last_name', TRUE );

			break;
			default :
			case 'nickname' :

				$display_name = get_user_meta( $userdata->id, 'nickname', TRUE );
		}

		return $display_name ? $display_name : $default;
	}
}

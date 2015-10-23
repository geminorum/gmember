<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberOnline extends gPluginModuleCore
{

	public function plugins_loaded()
	{
		global $gMemberNetwork;

		if ( ! $gMemberNetwork->settings->get( 'store_online', FALSE ) )
			return;

		add_action( 'wp_logout', array( $this, 'wp_logout' ) );
		add_action( 'wp_login', array( $this, 'wp_login' ), 10, 2 );
		add_filter( 'heartbeat_received', array( $this, 'heartbeat_received' ), 10, 3 );
		add_filter( 'heartbeat_nopriv_received', array( $this, 'heartbeat_received' ), 10, 3 );
	}

	public function wp_login( $username, $user )
	{
		// FIXME: check gMember Options
		update_user_meta( $user->ID, 'is_online', TRUE );
		update_user_meta( $user->ID, 'last_active', time() );
	}

	public function wp_logout()
	{
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'is_online', FALSE );
	}

	public function who_is( $args = array() )
	{
		$args = wp_parse_args( $args, array(
			'meta_key'     => 'last_active',
			'meta_value'   => time() - 24 * 60 * 60, // users active in last 24 hours
			'meta_compare' => '>',
			'count_total'  => FALSE,
		) );

		$users = get_users( $args );

		// $online = array();
		$html = '';

		foreach ( $users as $user ) {
			if ( ! get_user_meta( $user->ID, 'is_online', TRUE ) )
				continue;

			if ( function_exists( 'bp_core_get_userlink' ) )
				$html .= '<li><a href="'.bp_core_get_user_domain( $user->ID ).'" title="'.bp_core_get_user_displayname( $user->ID ).'">'.get_avatar( $user->user_email, 40, '', 'avatar' ).'</a></li>';
			else
				$html .= '<li><a title="'.esc_attr( $user->display_name ).'" >'.get_avatar( $user->user_email, 40, '', 'avatar' ).'</a></li>';

			// $online[$user->ID] = $user->user_login;
			// $online[$user->ID] = $user->display_name;
		}

		return $html;
	}

	public function heartbeat_received( $response, $data, $screen_id )
	{
		if ( isset( $data['gmember-online'] ) ) {
			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'last_active', time() );
			update_user_meta( $user_id, 'is_online', TRUE );
			$response['gmember-online'] = $this->who_is();
			$response['heartbeat_interval'] = 'slow';
		}
		return $response;
	}

	public function enqueue()
	{
		wp_enqueue_script( 'gmember-online', GMEMBER_URL.'assets/js/module.online.js', array( 'heartbeat', 'jquery' ), GMEMBER_VERSION, TRUE );
	}

	public function html()
	{
		echo '<ul id="gmember-online">Loading&hellip;<ul>';
	}
}

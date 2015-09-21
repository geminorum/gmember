<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberAdmin extends gPluginModuleCore
{

	public function setup_actions()
	{
		if ( is_network_admin() ) {
			add_action( 'admin_print_styles', array( &$this, 'admin_print_styles' ) );
			add_action( 'wp_network_dashboard_setup', array( &$this, 'wp_network_dashboard_setup' ) );
			add_filter( 'wpmu_users_columns', array( &$this, 'wpmu_users_columns' ) );
			add_filter( 'manage_users_custom_column', array( &$this, 'manage_users_custom_column' ), 10, 3 );
		}
	}

	public function admin_print_styles()
	{
		if ( 'users-network' == get_current_screen()->base )
			gPluginFormHelper::linkStyleSheet( $this->constants['plugin_url'].'assets/css/network.admin.users.css', GMEMBER_VERSION );
	}

	public function wp_network_dashboard_setup()
	{
		wp_add_dashboard_widget( 'gmember-signups',
			__( 'Latest Signups', GMEMBER_TEXTDOMAIN ),
			array( &$this, 'dashboard_signups' )
		);
	}

	public function dashboard_signups()
	{
		$query = new WP_User_Query( array (
			'blog_id' => 0,
			'orderby' => 'registered',
			'order'   => 'DESC',
			'number'  => 12,
			'fields'  => array(
				'ID',
				'display_name',
				'user_email',
				'user_registered',
				// 'user_status',
				'user_login',
			),
		) );

		if ( empty( $query->results ) ) {

			_e( 'No User?!', GMEMBER_TEXTDOMAIN );

		} else {

			echo '<table class="widefat" width="100%;"><thead><tr>';
			echo '<th>'.__( 'Registered', GMEMBER_TEXTDOMAIN ).'</th>';
			echo '<th>'.__( 'Name', GMEMBER_TEXTDOMAIN ).'</th>';
			echo '<th>'.__( 'E-mail', GMEMBER_TEXTDOMAIN ).'</th>';
			echo '</tr></thead>';

			$last = FALSE;
			$alt  = TRUE;

			foreach ( $query->results as $user ) {

				$registered = strtotime( $user->user_registered );

				printf(
					'<tr%1$s><td title="%5$s">%4$s</td><td><a href="user-edit.php?user_id=%6$s">%2$s</a></td><td><a href="mailto:%7$s" title="%7$s" target="_blank">%3$s</a></td></tr>',
					( $alt ? ' class="alternate"' : '' ),
					esc_html( $user->display_name ),
					esc_html( gPluginTextHelper::truncateString( $user->user_email, 21 ) ),
					esc_html( date_i18n( __( 'j/m' ), $registered ) ),
					esc_attr( human_time_diff( $registered ).' &mdash; '.date_i18n( __( 'j/m/Y' ), $registered ) ),
					$user->ID,
					esc_attr( $user->user_email )
				);

				$alt = ! $alt;

				if ( ! $last )
					$last = $registered;
			}

			echo '</table><ul>';
				echo '<li>'.sprintf( __( 'Last Registered: %s ago', GMEMBER_TEXTDOMAIN ), human_time_diff( $last ) ).'</li>';
				echo '<li>'.sprintf( __( 'Total Users: %s', GMEMBER_TEXTDOMAIN ), number_format_i18n( $query->get_total() ) ).'</li>';
			echo '</ul>';
		}
	}

	// defaults: 'cb', 'username', 'name', 'email', 'registered', 'blogs'
	public function wpmu_users_columns( $users_columns )
	{
		unset( $users_columns['registered'] );
		$users_columns['timestamps'] = __( 'Timestamps', GMEMBER_TEXTDOMAIN );
		return $users_columns;
	}

	public function manage_users_custom_column( $empty, $column_name, $user_id )
	{
		if ( 'timestamps' != $column_name )
			return $empty;

		$html        = '';
		$user        = get_user_by( 'id', $user_id );
		$lastlogin   = get_user_meta( $user_id, 'lastlogin', TRUE );
		$register_ip = get_user_meta( $user_id, 'register_ip', TRUE );

		$html .= '<table></tbody>';

		$html .= '<tr><td>'.__( 'Registered', GMEMBER_TEXTDOMAIN ).'</td><td><code title="'
			.mysql2date( 'g:i:s a', $user->user_registered ).'">'
			.mysql2date( 'Y/m/d', $user->user_registered ).'</code></td></tr>';

		$html .= '<tr><td>'.__( 'Register IP', GMEMBER_TEXTDOMAIN ).'</td><td><code>'
			.( $register_ip ? $register_ip : __( 'N/A', GMEMBER_TEXTDOMAIN ) ).'</code></td></tr>';

		$html .= '<tr><td>'.__( 'Last Login', GMEMBER_TEXTDOMAIN ).'</td><td>'
			.( $lastlogin ? '<code title="'.mysql2date( 'g:i:s a', $lastlogin ).'">'
				.mysql2date( 'Y/m/d', $lastlogin ).'</code>'
			: __( 'Never', GMEMBER_TEXTDOMAIN ) ).'</td></tr>';

		if ( function_exists( 'bp_get_last_activity' ) )
			$html .= '<tr><td colspan="2">'.bp_get_last_activity( $user_id ).'</td></tr>';

		$html .= '</tbody></table>';

		echo $html;
	}
}

<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberAdmin extends gPluginModuleCore
{

	public function setup_actions()
	{
		if ( is_network_admin() ) {

			add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
			add_action( 'wp_network_dashboard_setup', array( $this, 'wp_network_dashboard_setup' ) );

			add_filter( 'views_users-network', array( $this, 'views_users_network' ) );
			add_filter( 'users_list_table_query_args', array( $this, 'users_list_table_query_args' ) );

			add_filter( 'wpmu_users_columns', array( $this, 'wpmu_users_columns' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'manage_users_custom_column' ), 10, 3 );

			add_filter( 'manage_users-network_sortable_columns', array( $this, 'manage_users_network_sortable_columns' ) );
		}
	}

	public function admin_print_styles()
	{
		$screen = get_current_screen();

		if ( 'users-network' == $screen->base )
			gPluginHTML::linkStyleSheet( $this->constants['plugin_url'].'assets/css/network.admin.users.css', $this->constants['plugin_ver'] );

		else if ( 'dashboard-network' == $screen->base )
			gPluginHTML::linkStyleSheet( $this->constants['plugin_url'].'assets/css/network.admin.dashboard.css', $this->constants['plugin_ver'] );
	}

	public function wp_network_dashboard_setup()
	{
		wp_add_dashboard_widget( 'gmember-signups',
			_x( 'Latest Signups', 'Signup Admin Widget', GMEMBER_TEXTDOMAIN ),
			array( $this, 'dashboard_signups' )
		);

		wp_add_dashboard_widget( 'gmember-logins',
			_x( 'Latest Logins', 'Logins Admin Widget', GMEMBER_TEXTDOMAIN ),
			array( $this, 'dashboard_logins' )
		);
	}

	public function dashboard_signups()
	{
		$query = new \WP_User_Query( array (
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

			_ex( 'No User?!', 'Signup Admin Widget', GMEMBER_TEXTDOMAIN );

		} else {

			echo '<table class="widefat gmember-dashboard -table-signup"><thead><tr>';
			echo '<th>'._x( 'On', 'Signup Admin Widget', GMEMBER_TEXTDOMAIN ).'</th>';
			echo '<th>'._x( 'Name', 'Signup Admin Widget', GMEMBER_TEXTDOMAIN ).'</th>';
			echo '<th>'._x( 'E-mail', 'Signup Admin Widget', GMEMBER_TEXTDOMAIN ).'</th>';
			echo '</tr></thead>';

			$last = FALSE;
			$alt  = TRUE;

			$template = '<tr%1$s>'
							.'<td class="-month-day" title="%5$s">%4$s</td>'
							.'<td class="-edit-link"><a title="%8$s" href="%6$s" target="_blank">%2$s</a></td>'
							.'<td class="-mail-link"><a title="%7$s" href="%7$s" target="_blank">%3$s</a></td>'
						.'</tr>';

			foreach ( $query->results as $user ) {

				$registered = strtotime( get_date_from_gmt( $user->user_registered ) );

				vprintf( $template, array(
					( $alt ? ' class="alternate"' : '' ),
					esc_html( $user->display_name ),
					esc_html( gPluginTextHelper::truncateString( $user->user_email, 21 ) ),
					esc_html( date_i18n( _x( 'j/m', 'Signup Admin Widget', GMEMBER_TEXTDOMAIN ), $registered ) ),
					esc_attr( human_time_diff( $registered ).' &mdash; '.date_i18n( _x( 'j/m/Y', 'Signup Admin Widget', GMEMBER_TEXTDOMAIN ), $registered ) ),
					get_edit_user_link( $user->ID ),
					'mailto:'.esc_attr( $user->user_email ),
					$user->user_login,
				) );

				$alt = ! $alt;

				if ( ! $last )
					$last = $registered;
			}

			echo '</table><ul class="gmember-dashboard -list-signup">';
				echo '<li>'.sprintf( _x( 'Last Registered: %s ago', 'Signup Admin Widget', GMEMBER_TEXTDOMAIN ), human_time_diff( $last ) ).'</li>';
				echo '<li>'.sprintf( _x( 'Total Users: %s', 'Signup Admin Widget', GMEMBER_TEXTDOMAIN ), number_format_i18n( $query->get_total() ) ).'</li>';
			echo '</ul>';
		}
	}

	public function dashboard_logins()
	{
		$query = new \WP_User_Query( array (
			'blog_id'    => 0,
			'meta_key'   => $this->constants['meta_lastlogin'],
			'orderby'    => 'meta_value',
			'order'      => 'DESC',
			'number'     => 12,
			'meta_query' => array( array(
				'key'     => $this->constants['meta_lastlogin'],
				'compare' => 'EXISTS',
			) ),
			'fields' => array(
				'ID',
				'display_name',
				'user_email',
				'user_login',
			),
		) );

		if ( empty( $query->results ) ) {

			echo '<code>'.__( 'No Data Available', GMEMBER_TEXTDOMAIN ).'</code>';

		} else {

			echo '<table class="widefat gmember-dashboard -table-logins"><thead><tr>';
			echo '<th>'._x( 'Ago', 'Logins Admin Widget', GMEMBER_TEXTDOMAIN ).'</th>';
			echo '<th>'._x( 'Name', 'Logins Admin Widget', GMEMBER_TEXTDOMAIN ).'</th>';
			echo '<th>'._x( 'Timestamp', 'Logins Admin Widget', GMEMBER_TEXTDOMAIN ).'</th>';
			echo '</tr></thead>';

			$last = FALSE;
			$alt  = TRUE;

			$template = '<tr%1$s>'
							.'<td class="-time-ago">%3$s</td>'
							.'<td class="-edit-link"><a title="%5$s" href="%4$s" target="_blank">%2$s</a></td>'
							.'<td class="-time-full">%6$s</td>'
						.'</tr>';

			foreach ( $query->results as $user ) {

				if ( $meta = get_user_meta( $user->ID, $this->constants['meta_lastlogin'], TRUE ) )
					$lastlogin = strtotime( get_date_from_gmt( $meta ) );
				else
					continue;

				vprintf( $template, array(
					( $alt ? ' class="alternate"' : '' ),
					esc_html( $user->display_name ),
					esc_html( human_time_diff( $lastlogin ) ),
					get_edit_user_link( $user->ID ),
					$user->user_login,
					esc_html( date_i18n( _x( 'H:i:s - F j, Y', 'Logins Admin Widget', GMEMBER_TEXTDOMAIN ), $lastlogin ) ),
				) );

				$alt = ! $alt;

				if ( ! $last )
					$last = $lastlogin;
			}

			echo '</table><ul class="gmember-dashboard -list-logins">';
				echo '<li>'.sprintf( _x( 'Last Login: %s ago', 'Logins Admin Widget', GMEMBER_TEXTDOMAIN ), human_time_diff( $last ) ).'</li>';
			echo '</ul>';
		}
	}

	public function users_list_table_query_args( $args )
	{
		if ( isset( $_GET['spam'] ) )
			add_action( 'pre_user_query', array( $this, 'pre_user_query' ) );

		return $args;
	}

	public function pre_user_query( &$user_query )
	{
		global $wpdb;

		$user_query->query_where .= " AND $wpdb->users.spam = '1'";
	}

	public function views_users_network( $views )
	{
		// FIXME: remove current class from other views
		$class = isset( $_GET['spam'] ) ? ' class="current"' : '';

		// FIXME: helper for counting spam users
		// $views['spam'] = "<a href='".network_admin_url('users.php?spam')."'$class>".sprintf( _n( 'Marked as Spam <span class="count">(%s)</span>', 'Marked as Spam <span class="count">(%s)</span>', 12 ), number_format_i18n( 12 ) ) . '</a>';

		$views['spam'] = '<a href="'.network_admin_url( 'users.php?spam' ).'"'.$class.'>'.__( 'Marked as Spam', GMEMBER_TEXTDOMAIN ).'</a>';
		return $views;
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
		$mode        = empty( $_REQUEST['mode'] ) ? 'list' : $_REQUEST['mode'];

		$user        = get_user_by( 'id', $user_id );
		$lastlogin   = get_user_meta( $user_id, $this->constants['meta_lastlogin'], TRUE );
		$register_ip = get_user_meta( $user_id, $this->constants['meta_register_ip'], TRUE );

		$registered  = strtotime( get_date_from_gmt( $user->user_registered ) );
		$lastlogged  = $lastlogin ? strtotime( get_date_from_gmt( $lastlogin ) ) : NULL;

		$html .= '<table></tbody>';

		$html .= '<tr><td>'.__( 'Registered', GMEMBER_TEXTDOMAIN ).'</td><td><code title="'
			.date_i18n( 'g:i:s a', $registered ).'">'
			.date_i18n( 'Y/m/d', $registered ).'</code></td></tr>';

		$html .= '<tr><td>'.__( 'Last Login', GMEMBER_TEXTDOMAIN ).'</td><td>'
			.( $lastlogin ? '<code title="'.date_i18n( 'g:i:s a', $lastlogged ).'">'
				.date_i18n( 'Y/m/d', $lastlogged ).'</code>'
			: __( 'Never', GMEMBER_TEXTDOMAIN ) ).'</td></tr>';

		if ( function_exists( 'bp_get_user_last_activity' ) ) {

			if ( $lastactivity = bp_get_user_last_activity( $user_id ) )
				$lastactive = strtotime( get_date_from_gmt( $lastactivity ) );

			$html .= '<tr><td>'.__( 'Last Activity', GMEMBER_TEXTDOMAIN ).'</td><td>'
				.( $lastactivity
					? '<code title="'.bp_core_time_since( $lastactivity ).'">'
						.date_i18n( 'Y/m/d', $lastactive )
					: '<code>'.__( 'N/A', GMEMBER_TEXTDOMAIN ) )
				.'</code></td></tr>';
		}

		$html .= '<tr><td>'.__( 'Register IP', GMEMBER_TEXTDOMAIN ).'</td><td><code>'
			.( $register_ip ? $register_ip : __( 'N/A', GMEMBER_TEXTDOMAIN ) ).'</code></td></tr>';

		$html .= '</tbody></table>';

		echo $html;
	}

	public function manage_users_network_sortable_columns( $sortable_columns )
	{
		$sortable_columns['timestamps'] = 'id'; // order by id (registerdate)
		return $sortable_columns;
	}
}

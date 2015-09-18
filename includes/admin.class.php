<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberAdmin extends gPluginModuleCore
{

	public function setup_actions()
	{
		add_action( 'wp_network_dashboard_setup', array( &$this, 'wp_network_dashboard_setup' ) );
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
}

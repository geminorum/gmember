<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberNetwork extends gPluginNetworkCore
{

	public function setup_modules()
	{
		// $this->session = new gMemberSession();

		$modules = array(
			'signup'  => 'gMemberSignUp',
			// 'mail'    => 'gMemberMail',
			'login'   => 'gMemberLogin',
			'profile' => 'gMemberProfile',
			// 'groups'  => 'gMemberGroups',
			'widgets' => 'gMemberWidgets',
			// 'online'  => 'gMemberOnline',
			// 'spam'    => 'gMemberSPAM',
			// 'avatar'  => 'gMemberAvatar',
			// 'social'  => 'gMemberSocial',
			// 'fields'  => 'gMemberFields',
			// 'import'  => 'gMemberImport',
			'cleanup' => 'gMemberCleanUp',
		);

		if ( is_admin() ) {
			$modules['admin'] = 'gMemberAdmin';
		}

		foreach ( $modules as $module => $class ) {

			$this->{$module} = gPluginFactory::get( $class, $this->constants, $this->args );

			if ( FALSE === $this->{$module} )
				unset( $this->{$module} );
		}

		add_action( 'bp_include', array( $this, 'bp_include' ) );
	}

	public function bp_include()
	{
		$this->buddypress = gPluginFactory::get( 'gMemberBuddyPress', $this->constants, $this->args );
	}

	public function load_textdomain()
	{
		load_plugin_textdomain( GMEMBER_TEXTDOMAIN, FALSE, 'gmember/languages' );
	}

	protected function setup_constants()
	{
		defined( 'GPLUGIN_SESSION_COOKIE' ) or define( 'GPLUGIN_SESSION_COOKIE', '_gs_session' );
		defined( 'GPLUGIN_SESSION_CRON_ROUTINE' ) or define( 'GPLUGIN_SESSION_CRON_ROUTINE', 'hourly' );
	}

	public function network_settings_save()
	{
		global $gMemberNetwork;

		$sub = isset( $_REQUEST['sub'] ) ? $_REQUEST['sub'] : 'general';

		do_action( 'gmember_network_settings_register', $sub );


		if ( ! empty( $_POST ) ) {

			if ( 'spam' == $sub ) {

				// CHECK REFERER!!!

				$args = array();

				if ( isset( $_POST['gmember_spam_userid'] ) && count( $_POST['gmember_spam_userid'] ) )
					$args['deleted'] = $gMemberNetwork->spam->user_delete( $_POST['gmember_spam_userid'] );

				if ( isset( $_POST['gmember_spam_approved_userid'] ) && count( $_POST['gmember_spam_approved_userid'] ) )
					$args['approved'] = $gMemberNetwork->spam->user_approve( $_POST['gmember_spam_approved_userid'] );

				if ( count( $args ) ) {
					wp_redirect( add_query_arg( $args, wp_get_referer() ) );
					exit();
				}

			} else if ( 'import' == $sub ) {


			} else if ( 'update' == $_POST['action'] ) {

				check_admin_referer( 'gmember_'.$sub.'-options' );

				if ( isset( $_POST['gmember_network'] ) && is_array( $_POST['gmember_network'] ) ) {

					$options = $gMemberNetwork->settings->settings_sanitize( $_POST['gmember_network'] );
					$result = $gMemberNetwork->settings->update_options( $options );

					wp_redirect( add_query_arg( 'message', ( $result ? 'updated' : 'error' ), wp_get_referer() ) );
					exit();
				}
			}
		}

		if ( 'spam' == $sub )
			add_action( 'gmember_network_settings_sub_spam', array( $gMemberNetwork->spam, 'network_settings_html' ), 10, 2 );
		else if ( 'import' == $sub )
			add_action( 'gmember_network_settings_sub_import', array( $gMemberNetwork->import, 'network_settings_html' ), 10, 2 );
		else if ( 'cleanup' == $sub )
			add_action( 'gmember_network_settings_sub_cleanup', array( $gMemberNetwork->cleanup, 'network_settings_html' ), 10, 2 );
		else
			add_action( 'gmember_network_settings_sub_'.$sub, array( $this, 'network_settings_html' ), 10, 2 );
	}
}

class gMemberNetworkSettings   extends gPluginSettingsCore {}
// class gMemberComponentSettings extends gPluginSettingsCore {}
class gMemberModuleSettings    extends gPluginSettingsCore {}
class gMemberLogger            extends gPluginLoggerCore   {}

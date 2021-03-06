<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

/*
Plugin Name: gMember
Plugin URI: http://geminorum.ir/wordpress/gmember
Description: Extra User Management. Depends on <a href="http://geminorum.ir/wordpress/gplugin/">gPlugin</a>
Version: 2.14.2
License: GPLv3+
Author: geminorum
Author URI: http://geminorum.ir/
Network: true
TextDomain: gmember
DomainPath : /languages
GitHub Plugin URI: https://github.com/geminorum/gmember
GitHub Branch: master
Requires WP: 4.5
Requires PHP: 5.3
*/

define( 'GMEMBER_VERSION', '2.14.2' );
define( 'GMEMBER_VERSION_DB', '0.1' );
define( 'GMEMBER_VERSION_GPLUGIN', 38 );
define( 'GMEMBER_DIR', plugin_dir_path( __FILE__ ) );
define( 'GMEMBER_URL', plugin_dir_url( __FILE__ ) );
define( 'GMEMBER_FILE', basename( GMEMBER_DIR ).'/'.basename( __FILE__ ) );

if ( file_exists( WP_PLUGIN_DIR.'/gmember-custom.php' ) )
	require( WP_PLUGIN_DIR.'/gmember-custom.php' );

defined( 'GMEMBER_TEXTDOMAIN' ) or define( 'GMEMBER_TEXTDOMAIN', 'gmember' );

function gmember_init( $gplugin_version = NULL ) {

	if ( function_exists( 'gNetwork' ) )
		return FALSE;

	global $gMemberNetwork;

	if ( ! $gplugin_version || ! version_compare( $gplugin_version, GMEMBER_VERSION_GPLUGIN, '>=' ) )
		return;

	$includes = array(
		'network',
		'filtered',

		'signup',
		'login',
		'profile',
		'widgets',
		'admin',
		'social',
		'cleanup',
		'buddypress',
	);

	foreach ( $includes as $file )
		if ( file_exists( GMEMBER_DIR.'includes/'.$file.'.class.php' ) )
			require_once( GMEMBER_DIR.'includes/'.$file.'.class.php' );

	$args = array(
		'domain'  => 'gmember',
		'title'   => __( 'gMember', GMEMBER_TEXTDOMAIN ),
		'network' => TRUE,

		'logger_args' => array(
			'name'        => __( 'Logs', GMEMBER_TEXTDOMAIN ),
			'post_type'   => 'gmember_log',
			'taxonomy'    => 'gmember_log_type',
			'meta_prefix' => '_gmember_log_',
			'hook_prefix' => 'gmember_log_',
			'types'       => array( 'email' ),
		),
	);

	$constants = array(
		'plugin_dir' => GMEMBER_DIR,
		'plugin_url' => GMEMBER_URL,
		'plugin_ver' => GMEMBER_VERSION,
		'plugin_vdb' => GMEMBER_VERSION_DB,

		'class_filters'          => 'gMemberFiltered',
		'theme_templates_dir'    => 'gmember-templates',
		'class_network_settings' => 'gMemberNetworkSettings',

		'meta_key'        => '_gmember',
		'term_meta_key'   => '_gmember',
		'root_meta_key'   => '_gmember_root',
		'remote_meta_key' => '_gmember_remote',

		'meta_register_ip'            => 'register_ip',
		'meta_lastlogin'              => 'lastlogin',
		'meta_disable_user'           => 'disable_user',
		'meta_disable_password_reset' => 'disable_password_reset',
		'meta_approved_user'          => 'approved_user',
	);

	if ( class_exists( 'gPluginFactory' ) )
		$gMemberNetwork = gPluginFactory::get( 'gMemberNetwork', $constants, $args );
}

require( GMEMBER_DIR.'gplugin/load.php' );
gplugin_init( 'gmember_init' );

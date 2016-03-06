<?php defined( 'ABSPATH' ) or die( 'Restricted access' );
/*
Plugin Name: gMember
Plugin URI: http://geminorum.ir/wordpress/gmember
Description: Extra User Management. Depends on <a href="http://geminorum.ir/wordpress/gplugin/">gPlugin</a>
Version: 0.2.9
License: GNU/GPL 2
Author: geminorum
Author URI: http://geminorum.ir/
Network: true
TextDomain: gmember
DomainPath : /languages
GitHub Plugin URI: https://github.com/geminorum/gmember
GitHub Branch: master
Requires WP: 4.4
Requires PHP: 5.3
*/

/*
	Copyright 2016 geminorum

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define( 'GMEMBER_VERSION', '0.2.9' );
define( 'GMEMBER_VERSION_DB', '0.1' );
define( 'GMEMBER_VERSION_GPLUGIN', 31 );
define( 'GMEMBER_FILE', __FILE__ );
define( 'GMEMBER_DIR', plugin_dir_path( __FILE__ ) );
define( 'GMEMBER_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( WP_PLUGIN_DIR.'/gmember-custom.php' ) )
	require( WP_PLUGIN_DIR.'/gmember-custom.php' );

defined( 'GMEMBER_TEXTDOMAIN' ) or define( 'GMEMBER_TEXTDOMAIN', 'gmember' );

function gmember_init( $gplugin_version = NULL ){

	// TODO: bail if no gPlugin version
	if ( $gplugin_version && ! version_compare( $gplugin_version, GMEMBER_VERSION_GPLUGIN, '>=' ) )
		return;

	global $gMemberNetwork;

	$includes = array(
		'network',
		'filtered',
		// 'session',

		'signup',
		// 'mail',
		'login',
		'profile',
		// 'fields',
		// 'groups',
		'widgets',
		// 'online',
		// 'spam',
		'admin',
		// 'avatar',
		// 'social',
		// 'import',
		'cleanup',
		'buddypress',
	);

	foreach ( $includes as $file )
		if ( file_exists( GMEMBER_DIR.'includes/'.$file.'.class.php' ) )
			require_once( GMEMBER_DIR.'includes/'.$file.'.class.php' );

	$args = array(
		'domain'    => 'gmember',
		'title'     => __( 'gMember', GMEMBER_TEXTDOMAIN ),
		'network'   => TRUE,

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
		//'class_mustache' => 'gMemberMustache',
		'theme_templates_dir'    => 'gmember-templates',
		'class_network_settings' => 'gMemberNetworkSettings',
		//'class_component_settings' => 'gMemberComponentSettings',
		//'class_module_settings' => 'gMemberModuleSettings',

		'product_cpt'      => 'product',
		'product_archives' => 'products',

		'group_tax'  => 'user_group',
		'group_slug' => 'group',

		'shortcode_purchase' => 'gmember_purchase',

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

	if ( function_exists( 'gPluginFactory' ) )
		$gMemberNetwork = gPluginFactory( 'gMemberNetwork', $constants, $args );
}

require( GMEMBER_DIR.'gplugin/load.php' );
gplugin_init( 'gmember_init' );

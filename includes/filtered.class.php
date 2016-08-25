<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberFiltered extends gPluginFilteredCore
{

	protected function gmember_network_options_defaults()
	{
		return array(
			'plugin_version' => constant( 'GMEMBER_VERSION' ),
			'db_version'     => constant( 'GMEMBER_VERSION_DB' ),
		);
	}

	protected function network_settings_args()
	{
		return array(

			'register_hook'     => 'gmember_network_settings_register',
			'settings_sanitize' => FALSE, // disable sanitization
			'site_options'      => TRUE,
			'option_group'      => 'gmember_network',

			'page' => array(
				'gmember_general' => array(
					'default' => array(
						'title' => '',
						'callback' => '__return_FALSE',
						'fields' => array(
							'search_authors' => array(
								'title'   => __( 'Search Authors', GMEMBER_TEXTDOMAIN ),
								'desc'    => __( 'Include by author display name in general post search queries.', GMEMBER_TEXTDOMAIN ),
								'type'    => 'enabled',
								'default' => '0',
							),
							'store_lastlogin' => array(
								'title'   => __( 'Store Last Login', GMEMBER_TEXTDOMAIN ),
								'type'    => 'enabled',
								'default' => '1',
							),
							'store_online' => array(
								'title'   => __( 'Store Online Users', GMEMBER_TEXTDOMAIN ),
								'type'    => 'enabled',
								'default' => '0',
							),
						),
					),
				),
				'gmember_signup' => array(
					'default' => array(
						'title' => '',
						'callback' => '__return_FALSE',
						'fields' => array(
							'signup_ip' => array(
								'title'   => __( 'Signup IP', GMEMBER_TEXTDOMAIN ),
								'type'    => 'enabled',
								'default' => '1',
							),
							'signup_url' => array(
								'title'   => __( 'Network Signup URL', GMEMBER_TEXTDOMAIN ),
								'desc'    => __( 'Full URL to the custom sign-up page. You can use <code>[signup-form]</code> shortcode or any other registration mechanisem.', GMEMBER_TEXTDOMAIN ),
								'type'    => 'text',
								'default' => '',
								'dir'     => 'ltr',
								'filter'  => 'esc_url',
							),
						),
					),
				),
				'gmember_buddypress' => array(
					'default' => array(
						'title' => '',
						'callback' => '__return_FALSE',
						'fields' => array(
							'bp_display_name' => array(
								'title'   => __( 'Display Name', GMEMBER_TEXTDOMAIN ),
								'desc'    => __( 'Member\'s Universal Sidewide Display Name', GMEMBER_TEXTDOMAIN ),
								'type'    => 'select',
								'default' => 'default',
								'values'  => array(
									'default'         => _x( '&mdash; Default &mdash;', 'BP Display Name Option', GMEMBER_TEXTDOMAIN ),
									'first_last_name' => _x( 'First and Last Name', 'BP Display Name Option', GMEMBER_TEXTDOMAIN ),
									'username'        => _x( 'Username', 'BP Display Name Option', GMEMBER_TEXTDOMAIN ),
									'nickname'        => _x( 'Nickname', 'BP Display Name Option', GMEMBER_TEXTDOMAIN ),
									'first_name'      => _x( 'First Name', 'BP Display Name Option', GMEMBER_TEXTDOMAIN ),
									'last_name'       => _x( 'Last Name', 'BP Display Name Option', GMEMBER_TEXTDOMAIN ),
								),
							),
						),
					),
				),
			),
		);
	}

	protected function network_settings_messages()
	{
		return array(
			'error'   => self::error( __( 'There was an error durring updating proccess', GMEMBER_TEXTDOMAIN ) ),
			'updated' => self::updated( __( 'Settings successfully updated.', GMEMBER_TEXTDOMAIN ) ),
		);
	}

	protected function network_settings_titles()
	{
		return array(
			'title' => __( 'gMember Settings', GMEMBER_TEXTDOMAIN ),
			'menu'  => __( 'gMember Settings', GMEMBER_TEXTDOMAIN ),
		);
	}

	protected function network_settings_subs()
	{
		return array(
			'overview'   => __( 'Overview', GMEMBER_TEXTDOMAIN ),
			'general'    => __( 'General', GMEMBER_TEXTDOMAIN ),
			'signup'     => __( 'SignUp', GMEMBER_TEXTDOMAIN ),
			'buddypress' => __( 'BuddyPress', GMEMBER_TEXTDOMAIN ),
			'cleanup'    => __( 'CleanUp', GMEMBER_TEXTDOMAIN ),
			// 'spam'       => __( 'Spam Fight', GMEMBER_TEXTDOMAIN ),
			// 'import'     => __( 'Import Users', GMEMBER_TEXTDOMAIN ),
		);
	}

	protected function group_tax_labels()
	{
		return array(
			'name'                       => _x( 'User Groups', 'User Group Tax Name', GMEMBER_TEXTDOMAIN ),
			'menu_name'                  => _x( 'User Groups', 'User Group Tax Menu Name', GMEMBER_TEXTDOMAIN ),
			'singular_name'              => _x( 'User Group', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'all_items'                  => _x( 'All User Groups', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'parent_item'                => _x( 'Parent User Group', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'parent_item_colon'          => _x( 'Parent User Group:', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'search_items'               => _x( 'Search User Groups', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'edit_item'                  => _x( 'Edit User Group', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'update_item'                => _x( 'Update User Group', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'add_new_item'               => _x( 'Add New User Group', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'new_item_name'              => _x( 'New User Group', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'separate_items_with_commas' => _x( 'Separate user groups with commas', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'add_or_remove_items'        => _x( 'Add or remove user groups', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'choose_from_most_used'      => _x( 'Choose from most used user groups', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'not_found'                  => _x( 'No User Groups found.', 'User Group Tax Labels', GMEMBER_TEXTDOMAIN ),
			'popular_items'              => NULL,
		);
	}
}

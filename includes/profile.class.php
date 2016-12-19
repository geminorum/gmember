<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberProfile extends gPluginModuleCore
{

	private $display_name = array();

	public function setup_actions()
	{
		if ( did_action( 'set_current_user' ) )
			$this->set_current_user();
		else
			add_action( 'set_current_user', array( $this, 'set_current_user' ), 15 );

		parent::setup_actions();
	}

	public function plugins_loaded()
	{
		add_filter( 'the_author', array( $this, 'the_author' ), 12 );
		add_filter( 'get_the_author_display_name', array( $this, 'get_the_author_display_name' ), 12, 2 );
		add_filter( 'get_comment_author', array( $this, 'get_comment_author' ), 12, 3 );
		add_filter( 'p2_get_user_display_name', array( $this, 'p2_get_user_display_name' ), 12 );
		add_filter( 'p2_get_archive_author', array( $this, 'p2_get_archive_author' ), 12 );
	}

	public function init()
	{
		global $gMemberNetwork;

		if ( ! is_admin() ) {
			add_filter( 'edit_profile_url', array( $this, 'edit_profile_url' ), 8, 3 );

			if ( $gMemberNetwork->settings->get( 'search_authors', FALSE ) )
				add_filter( 'posts_search', array( $this, 'posts_search' ) );
		}
	}

	public function admin_init()
	{
		global $gMemberNetwork;

		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
		add_action( 'personal_options', array( $this, 'personal_options_late' ), 99, 1 );
		add_action( 'personal_options_update', array( $this, 'edit_user_profile_update' ), 10, 1 );
		add_action( 'edit_user_profile_update', array( $this, 'edit_user_profile_update' ), 10, 1 );

		if ( $gMemberNetwork->settings->get( 'disable_colorschemes', TRUE ) )
			remove_all_actions( 'admin_color_scheme_picker' );
	}

	public function admin_print_styles()
	{
		if ( in_array( get_current_screen()->base, array( 'profile', 'user-edit' ) ) )
			gPluginHTML::linkStyleSheet( $this->constants['plugin_url'].'assets/css/network.admin.profile.css', $this->constants['plugin_ver'] );
	}

	// fire order changed since WP 4.7.0
	// @SEE: https://make.wordpress.org/core/?p=20592
	public function set_current_user()
	{
		if ( ! is_user_logged_in() )
			return;

		global $current_user, $user_identity;

		$old = $current_user->display_name;
		$user_identity = $current_user->display_name = $this->get_display_name( $current_user->ID, $current_user->display_name );

		if ( $old != $user_identity )
			update_user_caches( $current_user );
	}

	public function the_author( $author = NULL )
	{
		if ( is_null( $author ) )
			return $author;

		global $authordata;

		return is_object( $authordata ) ? $this->get_display_name( $authordata->ID, $authordata->display_name ) : NULL;
	}

	public function get_the_author_display_name( $current, $user_id )
	{
		return $this->get_display_name( $user_id, $current );
	}

	public function get_display_name( $user_id, $current = '' )
	{
		if ( ! isset( $this->display_name[$user_id] ) )
			$this->display_name[$user_id] = get_user_meta( $user_id, 'gmember_display_name', TRUE );

		if ( ! empty( $this->display_name[$user_id][$this->current_blog] ) )
			return $this->display_name[$user_id][$this->current_blog];

		return $current;
	}

	public function get_comment_author( $author, $comment_ID, $comment )
	{
		return empty( $comment->user_id ) ? $author : $this->get_display_name( $comment->user_id, $author );
	}

	public function p2_get_user_display_name( $current )
	{
		return $this->get_display_name( $GLOBALS['current_user']->ID, $current );
	}

	public function p2_get_archive_author( $current )
	{
		return is_author() ? $this->get_display_name( get_queried_object_id(), $current ) : $current;
	}

	public function personal_options_late( $profileuser )
	{
		if ( is_multisite() && ! is_network_admin() && ! is_user_admin() ) {

			echo '</table><h2>'.__( 'Blog Options', GMEMBER_TEXTDOMAIN ).'</h2>';
			echo '<table class="form-table">';

			echo '<tr><th><label for="gmember_display_name">'
				.__( 'Nickname for this site', GMEMBER_TEXTDOMAIN )
				.'</label></th><td><input type="text" name="gmember_display_name" id="gmember_display_name" value="'
				.esc_attr( isset( $profileuser->gmember_display_name[$this->current_blog] ) ? $profileuser->gmember_display_name[$this->current_blog] : '' )
				.'" class="regular-text" /><p class="description">'
					.__( 'This will be displayed as your name in this site only', GMEMBER_TEXTDOMAIN )
				.'</p></td></tr>';

			echo '</table><table class="form-table">';
		}
	}

	public function edit_user_profile_update( $user_id )
	{
		if ( ! is_multisite() )
			return;

		if ( isset( $_POST['gmember_display_name'] ) ) {

			$display_names = get_user_meta( $user_id, 'gmember_display_name', TRUE );

			if ( empty( $display_names ) )
				$display_names = array();

			if ( empty( $_POST['gmember_display_name'] ) )
				unset( $display_names[$this->current_blog] );
			else
				$display_names[$this->current_blog] = trim( $_POST['gmember_display_name'] );

			update_user_meta( $user_id, 'gmember_display_name', $display_names );
		}
	}

	// set all non-admin edit profile links to the main site
	public function edit_profile_url( $url, $user_id, $scheme )
	{
		global $current_site;
		return get_admin_url( (int) $current_site->blog_id, 'profile.php', $scheme );
	}

	// @SOURCE: https://gist.github.com/danielbachhuber/7126249
	// Include posts from authors in the search results where either their display name or user login matches the query string
	// @author danielbachhuber
	public function posts_search( $posts_search )
	{
		if ( ! is_search() || empty( $posts_search ) )
			return $posts_search;

		global $wpdb;

		// get all of the users of the blog and see if the search query matches either the display name or the user login
		add_filter( 'pre_user_query', array( $this, 'pre_user_query' ) );

		$matching_users = get_users( array(
			'count_total'   => FALSE,
			'search'        => sprintf( '*%s*', sanitize_text_field( get_query_var( 's' ) ) ),
			'fields'        => 'ID',
			'search_fields' => array(
				'display_name',
				'user_login',
			),
		) );

		remove_filter( 'pre_user_query', array( $this, 'pre_user_query' ) );

		// don't modify the query if there aren't any matching users
		if ( empty( $matching_users ) )
			return $posts_search;

		// take a slightly different approach than core where we want all of the posts from these authors
		$posts_search = str_replace( ')))', ")) OR ( {$wpdb->posts}.post_author IN (" . implode( ',', array_map( 'absint', $matching_users ) ) . ")))", $posts_search );

		return $posts_search;
	}

	// Modify get_users() to search display_name instead of user_nicename
	public function pre_user_query( &$user_query )
	{
		if ( is_object( $user_query ) )
			$user_query->query_where = str_replace( "user_nicename LIKE", "display_name LIKE", $user_query->query_where );

		return $user_query;
	}
}

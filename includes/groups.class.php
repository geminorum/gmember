<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberGroups extends gPluginModuleCore
{

	public function setup_actions()
	{
		parent::setup_actions();

		add_filter( 'sanitize_user', array( &$this, 'sanitize_user' ) );

		if ( is_admin() ) {
			add_filter( 'parent_file', array( &$this, 'parent_file' ) );

			add_filter( 'manage_edit-'.$this->constants['group_tax'].'_columns', array( &$this, 'manage_columns' ) );
			add_action( 'manage_'.$this->constants['group_tax'].'_custom_column', array( &$this, 'custom_column' ), 10, 3 );

			add_action( 'show_user_profile', array( &$this, 'edit_user_profile' ), 5 );
			add_action( 'edit_user_profile', array( &$this, 'edit_user_profile' ), 5 );
			add_action( 'personal_options_update', array( &$this, 'edit_user_profile_update' ) );
			add_action( 'edit_user_profile_update', array( &$this, 'edit_user_profile_update' ) );
		}
	}

	public function init()
	{
		$this->register_taxonomies();
	}

	public function admin_init()
	{
		$tax = get_taxonomy( $this->constants['group_tax'] );

		if ( ! $tax )
			return;

		add_users_page(
			esc_attr( $tax->labels->menu_name ),
			esc_attr( $tax->labels->menu_name ),
			$tax->cap->manage_terms,
			'edit-tags.php?taxonomy='.$tax->name
		);
	}

	public function parent_file( $parent_file = '' )
	{
		global $pagenow;

		if ( ! empty( $_GET[ 'taxonomy' ] )
			&& $_GET[ 'taxonomy' ] == $this->constants['group_tax']
			&& $pagenow == 'edit-tags.php' )
				$parent_file = 'users.php';

		return $parent_file;
	}

	public function register_taxonomies()
	{
		$group_labels = self::getFilters( 'group_tax_labels' );

		register_taxonomy( $this->constants['group_tax'], 'user', array(
			'label'                 => $group_labels['label'],
			'labels'                => $group_labels,
			'update_count_callback' => array( 'gPluginTaxonomyHelper', 'update_count_callback' ),
			'public'                => TRUE,
			'show_admin_column'     => TRUE,
			'show_in_nav_menus'     => FALSE,
			'show_ui'               => TRUE, // current_user_can( 'manage_categories' ),
			'hierarchical'          => TRUE,
			'query_var'             => TRUE, // $this->constants['group_slug'],
			'rewrite'               => array(
				'slug'         => $this->constants['group_slug'],
				'hierarchical' => TRUE,
				'with_front'   => TRUE,
			),
			'capabilities' => array(
				'manage_terms' => 'list_users',
				'edit_terms'   => 'list_users',
				'delete_terms' => 'list_users',
				'assign_terms' => 'list_users',
			),
		) );
	}

	public function manage_columns( $columns )
	{
		unset( $columns['posts'] );
		$columns['users'] = __( 'Users', GMEMBER_TEXTDOMAIN );
		return $columns;
	}

	public function custom_column( $display, $column, $term_id )
	{
		if ( 'users' === $column ) {
			$term = get_term( $term_id, $this->constants['group_tax'] );
			echo number_format_i18n( $term->count );
		}
	}

	public function edit_user_profile( $user )
	{
		$tax = get_taxonomy( $this->constants['group_tax'] );

		if ( ! current_user_can( $tax->cap->assign_terms ) )
			return;

		$terms = get_terms( $this->constants['group_tax'], array( 'hide_empty' => FALSE ) );

		echo gPluginFormHelper::html( 'h2', __( 'Group', GMEMBER_TEXTDOMAIN ) );

		echo '<table class="form-table">';
			echo '<tr><th scope="row">'.__( 'Select Group', GMEMBER_TEXTDOMAIN ).'</th><td>';

			if ( ! empty( $terms ) ) {

				foreach ( $terms as $term ) {

					$html = gPluginFormHelper::html( 'input', array(
						'type'    => 'radio',
						'name'    => 'groups',
						'id'      => 'groups-'.$term->slug,
						'value'   => $term->slug,
						'checked' => is_object_in_term( $user->ID, $this->constants['group_tax'], $term ),
					) );

					echo '<p>'.gPluginFormHelper::html( 'label', array(
						'for' => 'groups-'.$term->slug,
					), $html.'&nbsp;'.esc_html( $term->name ) ).'</p>';
				 }

			} else {
				_e( 'There are no groups available.', GMEMBER_TEXTDOMAIN );
			}

		echo '</td></tr>';
		echo '</table>';
	}

	public function edit_user_profile_update( $user_id )
	{
		$tax = get_taxonomy( $this->constants['group_tax'] );
		if ( ! current_user_can( 'edit_user', $user_id ) && current_user_can( $tax->cap->assign_terms ) )
			return false;

		if ( ! isset( $_POST['groups'] ) )
			return;

		$term = esc_attr( $_POST['groups'] );
		wp_set_object_terms( $user_id, array( $term ), $this->constants['group_tax'], false);
		clean_object_term_cache( $user_id, $this->constants['group_tax'] );
	}

	// FIXME: DRAFT : need styling / register the shortcode!!
	public function user_groups_shortcode()
	{
		$term_id = get_queried_object_id();
		$term    = get_queried_object();
		$users   = get_objects_in_term( $term_id, $term->taxonomy );

		if ( ! empty( $users ) ) {

			foreach ( $users as $user_id ) {
				echo '<div class="user-entry">';

					// FIXME: use gMemberAvatar::get()
					echo get_avatar( get_the_author_meta( 'email', $user_id ), '96' );

					echo '<h2 class="user-title">'.gPluginFormHelper::html( 'a', array(
						'href' => get_author_posts_url( $user_id ),
						'title' => '',
					), get_the_author_meta( 'display_name', $user_id ) ).'<h2>';

					echo '<div class="description">'.wpautop( get_the_author_meta( 'description', $user_id ) ).'</div>';

				echo '</div>';
			}
		}
	}

	public function sanitize_user( $username )
	{
		if ( $this->constants['group_tax'] === $username )
			$username = '';

		return $username;
	}
}

<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberSPAM extends gPluginModuleCore
{

	public function network_settings_html( $settings_uri, $sub )
	{
		echo '<form method="post" action="">';

			$this->user_form( $this->user_list() );

			submit_button( __( 'Submit Changes',  GMEMBER_TEXTDOMAIN ) );

		echo '</form>';
	}

	public function user_list()
	{
		$users = array();

		// http://codex.wordpress.org/Class_Reference/WP_User_Query
		$query = new WP_User_Query( array (

			'blog_id' => 0,
			'orderby' => 'ID',
			'order'   => 'DESC', // 'ASC',
			'number'  => 100,
			// 'offset'  => 2,

			'meta_query' => array(
				// 'relation' => 'AND',

				0 => array(
					'key'       => $this->constants['meta_approved_user'],
					'compare'   => 'NOT EXISTS',
					// 'compare'   => 'EXISTS',
				),

				// 1 => array(
				// 	'key'       => $this->constants['meta_lastlogin'],
				// 	//'key'       => $this->constants['meta_register_ip'],
				// 	'compare'   => 'NOT EXISTS',
				// 	//'compare'   => 'EXISTS',
				// 	//'compare'   => 'IN',
				// 	//'compare'   => 'LIKE',
				// 	//'value'     => array( '5.' ),
				// ),
			),


			// 'search_columns' => array( 'user_email' ),
			// 'search' => '*@hotmail.com',
			// 'search' => '*@mail.ru',
			// 'search' => '*@outlook.com',
			// 'search' => '*@gmx.com',
			// 'search' => '*.pl',
			// 'search' => '*.gmailmirror.com',

			// 'search' => '*@live.com',
			// 'search' => '*@rocketmail.com',
			// 'search' => '*.bg',
			// 'search' => '*.maybebest.com',

			// 'search' => '*.net',
			// 'search' => '*.org',
			// 'search' => '*.pw',
			// 'search' => '*.co.uk',
			// 'search' => '*@aol.com',
			// 'search' => '*.tw',
			// 'search' => '*@rediffmail.com',
			// 'search' => '*.johnscaffee.com',
			// 'search' => '*@pbnsniper.com',
			// 'search' => 'contact*',
			// 'search' => '*.ru',
			// 'search' => '*@mike-russ-monty.com',
			// 'search' => '*@premiumgce.com',
			// 'search' => '*@purelifecleansereview.com',
			// 'search' => '*@yandex.com',
			// 'search' => '*@triminexreview.com',
			// 'search' => '*supplement.com',
			// 'search' => '*@nokiamail.com',
			// 'search' => '*.cz',
			// 'search' => '*@us.wellcs.com',
			// 'search' => '*@freeolamail.com',
			// 'search' => '*.de',
			// 'search' => '*.us',

			// 'count_total' => true, // get_total()
			// 'fields' => 'all_with_meta',
			// 'fields' => 'all',
			'fields' => array(
				'ID',
				'display_name',
				'user_email',
				'user_registered',
				// 'user_status',
				'user_login',
				// $this->constants['meta_register_ip'],
			),
		) );

		if ( ! empty( $query->results ) )
			return $query->results;

		return FALSE;


		if ( ! empty( $query->results ) ) {
			foreach ( $query->results as $user )
				$users[$user->user_email] = $user->display_name;

			return array(
				array_chunk( $users, $this->_chunc_size, TRUE ),
				$query->get_total(),
			);
		}

		return array( array(), 0, );
	}

	function user_form( $results = array() )
	{
		if ( false === $results ) {
			echo 'Not found!';
			return;
		}

		$alt = true;
		echo '<table class="widefat" width="100%;"><thead><tr>';
			echo '<th class="check-column"><input type="checkbox" id="deleted_box_all" title="DELETE ALL"/></th>';
			echo '<th>&nbsp;</th>';
			echo '<th>ID</th>';
			echo '<th>E-mail</th>';
			echo '<th>&nbsp;</th>';
			echo '<th>Display Name</th>';
			echo '<th class="check-column"><input type="checkbox" id="approved_box_all" title="APROVE ALL"/><th>';
			//echo '<th>&nbsp;</th>';
			echo '<th>Username</th>';
			echo '<th>&nbsp;</th>';
			echo '<th>Registered</th>';
		echo '</tr></thead><tbody>';

		foreach ( $results as $user )
		{
			echo '<tr'.( $alt ? ' class="alternate"' : '').'>';
			if ( $alt ) $alt = false; else $alt = true;
			echo '<td><input type="checkbox" class="deleted_box" name="gmember_spam_userid[]" id="gmember_spam_userid_'.$user->ID.'" value="'.$user->ID.'" /></td>';
			echo '<td>DELETE</td>';
			echo '<td><label for="gmember_spam_userid_'.$user->ID.'">'.$user->ID.'</label></td>';
			echo '<td><label for="gmember_spam_userid_'.$user->ID.'">'.$user->user_email.'</label></td>';
			echo '<td>&nbsp;</td>';
			echo '<td><label for="gmember_spam_approved_userid_'.$user->ID.'">'.$user->display_name.'</label></td>';
			//echo '<td><input type="checkbox" onchange="handleChange(this,'.$user->ID.');" name="gmember_spam_approved_userid[]" id="gmember_spam_approved_userid_'.$user->ID.'" value="'.$user->ID.'" /></td>';
			echo '<td><input type="checkbox" rel="'.$user->ID.'" class="approved_box" name="gmember_spam_approved_userid[]" id="gmember_spam_approved_userid_'.$user->ID.'" value="'.$user->ID.'" /></td>';
			echo '<td>APPROVE</td>';
			echo '<td><label for="gmember_spam_userid_'.$user->ID.'">'.$user->user_login.'</label></td>';
			echo '<td>&nbsp;</td>';
			echo '<td><label for="gmember_spam_userid_'.$user->ID.'">'.$user->user_registered.'</label></td>';
			//echo '<td>&nbsp;</td>';
			//echo '<td><label for="gmember_spam_userid_'.$user->ID.'">'.$user->{$this->constants['meta_register_ip']}.'</label></td>';
			//echo '</tr><tr><td>&nbsp;</td></tr>';
			echo '</tr>';
		}

		echo '</tbody></table>';
		echo "<script>jQuery(document).ready(function($) {

	$('.approved_box').change(function() {

		var delete_box = $('#gmember_spam_userid_'+$(this).attr('rel'));
		//console.log(delete_box);
		if($(this).is(':checked')) {
			delete_box.prop('checked', false);
		} else {
			delete_box.prop('checked', true);
		}
	});

	$('#deleted_box_all').click(function(e){
		//var table= $(e.target).closest('table');
		$('input.deleted_box').prop('checked',this.checked);
	});

	$('#approved_box_all').click(function(e){
		//var table= $(e.target).closest('table');
		$('input.approved_box').prop('checked',this.checked);
	});

});</script>";
	}

	public function user_approve( $users = array() )
	{
		if ( ! count( $users ) )
			return 0;

		$approved = array();
		foreach ( $users as $user )
			$approved[$user] = update_user_meta( (int) $user, $this->constants['meta_approved_user'], '1' );

		return count( $approved );
	}

	// http://codex.wordpress.org/Function_Reference/wp_delete_user
	public function user_delete( $users = array() )
	{
		if ( ! count( $users ) )
			return 0;

		require_once( ABSPATH.'wp-admin/includes/user.php' );

		$deleted = array();
		$fallback = GNETWORK_SITE_USER_ID ? GNETWORK_SITE_USER_ID : null;

		foreach ( $users as $user ) {

			// $deleted[$user] = wp_delete_user( $user, $fallback );
			$blogs = get_blogs_of_user( (int) $user, true );

			if ( ! empty( $blogs ) ) {
				foreach ( (array) $blogs as $blog_id => $details )
					remove_user_from_blog( $user, $blog_id, $fallback );
			}

			$deleted[$user] = self::wpmu_delete_user( $user );
		}

		return count( $deleted );
	}

	// based on : wpmu_delete_user()
	// without remove_user_from_blog();
	public static function wpmu_delete_user( $id )
	{
		global $wpdb;

		$id = (int) $id;
		$user = new WP_User( $id );

		if ( ! $user->exists() )
			return false;
		/**
		 * Fires before a user is deleted from the network.
		 *
		 * @since MU
		 *
		 * @param int $id ID of the user about to be deleted from the network.
		 */
		do_action( 'wpmu_delete_user', $id );

		$meta = $wpdb->get_col( $wpdb->prepare( "SELECT umeta_id FROM $wpdb->usermeta WHERE user_id = %d", $id ) );
		foreach ( $meta as $mid )
			delete_metadata_by_mid( 'user', $mid );

		$wpdb->delete( $wpdb->users, array( 'ID' => $id ) );

		clean_user_cache( $user );

		do_action( 'deleted_user', $id );

		return true;
	}
}

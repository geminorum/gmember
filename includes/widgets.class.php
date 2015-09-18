<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberWidgets extends gPluginModuleCore
{

	public function setup_actions()
	{
		add_action( 'widgets_init', array( &$this, 'widgets_init' ) );
	}

	public function widgets_init()
	{
		global $gMemberNetwork;

		// WORKING
		// if ( $gMemberNetwork->settings->get( 'store_online', FALSE ) )
		// 	register_widget( 'gMemberWidget_online' );

		register_widget( 'gMemberWidget_login' );
	}
}

class gMemberWidget_online extends WP_Widget
{

	public function __construct()
	{
		parent::__construct( 'gmember_online', __( 'gMember: Online Users', GMEMBER_TEXTDOMAIN ), array(
			'description' => __( 'Ajax powered online users, with cache plugins compatibility.', GMEMBER_TEXTDOMAIN )
		) );
	}

	// DRAFT
	public function widget( $args, $instance )
	{
		global $gMemberNetwork;

		echo '<h3>Online Users</h3>';

		$gMemberNetwork->online->enqueue();
		$gMemberNetwork->online->html();
	}
}

class gMemberWidget_login extends WP_Widget
{

	public function __construct()
	{
		parent::__construct( 'gmember_login', __( 'gMember: Login / Profile', GMEMBER_TEXTDOMAIN ), array(
			'description' => __( 'none-Ajax Login / Profile widget', GMEMBER_TEXTDOMAIN )
			) );
	}

	public function widget( $args, $instance )
	{
		extract( $args );

		if ( ! current_user_can( 'level_0' ) ) {
			$title = ( empty( $instance['title'] ) ? '' : $before_title.esc_html($instance['title']).$after_title );
			$username = esc_html( ( empty( $instance['username'] ) ? __( 'Username', GMEMBER_TEXTDOMAIN ) : $instance['username'] ) );
			$password = esc_html( ( empty( $instance['password'] ) ? __( 'Password', GMEMBER_TEXTDOMAIN ) : $instance['password'] ) );
			$login = esc_attr( ( empty( $instance['login'] ) ? __( 'Log In', GMEMBER_TEXTDOMAIN ) : $instance['login'] ) );
			echo $before_widget.$title;
			?><form name="loginform" id="loginform" action="<?php echo site_url( 'wp-login.php', 'login_post' ) ?>" method="post">
			<p><label><?php echo $username; ?><br />
			<input type="text" name="log" id="user_login" class="input" value="" tabindex="10" style="padding:3px;margin-bottom:3px;width:96%;border:1px solid #D1D1D1;"/></label>
			<br />
			<label><?php echo $password; ?><br />
			<input type="password" name="pwd" id="user_pass" class="input" value="" tabindex="20" style="padding:3px;margin-bottom:7px;width:96%;border:1px solid #D1D1D1;"/></label>

			<?php //do_action('login_form'); ?>
			<br />
			<input type="submit" name="wp-submit" id="wp-submit" value="&nbsp;&nbsp;<?php echo $login; ?>&nbsp;&nbsp;" tabindex="100" 1style="1padding:3px;1margin-bottom:7px;width:245px;border:1px solid #D1D1D1;" />
			</p>
			<input type="hidden" name="redirect_to" value="<?php echo site_url( 'index.php' ); ?>" />
			<input type="hidden" name="testcookie" value="1" />
			</form><?php
			echo $after_widget;
		} else {
			$title = ( empty( $instance['profile'] ) ? '' : $before_title.esc_html( $instance['profile'] ).$after_title );
			$logout = esc_attr( ( empty( $instance['logout'] ) ? __( 'Log Out', 'gmember' ) : $instance['logout'] ) );
			echo $before_widget.$title;
			global $userdata;
			// gMemberHelper::dump( $userdata );
			// if (isset($before_title)) echo $before_title;
			// echo "Welcome : ". $userdata->display_name;
			// if (isset($after_title)) echo $after_title;
			?><form name="logoutform" id="logoutform" action="<?php echo site_url('wp-login.php?action=logout&redirect_to='.site_url('/index.php'), 'logout_post') ?>" method="post">
				<ul><li><strong><?php echo $userdata->display_name;?></strong></li>
				<?php /** echo ($userdata->user_url)?'<li><a href="'.$userdata->user_url.'">your profile</a></li>':''; */?>
				<li><a class="button" href="<?php echo wp_logout_url( site_url( 'index.php' ) ); ?>">&nbsp;&nbsp;<?php echo $logout; ?>&nbsp;&nbsp;</a></li></ul>
			</form><?php
			echo $after_widget;
		}
	}

	public function form( $instance )
	{
		?>

		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Logged-out Title', 'gmember' ); ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if ( isset( $instance['title'] ) ) echo esc_attr( $instance['title'] ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('username'); ?>"><?php _e( 'Username Label', 'gmember' ); ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" value="<?php if ( isset( $instance['username'] ) ) echo esc_attr( $instance['username'] ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('password'); ?>"><?php _e( 'Password Label', 'gmember' ); ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id('password'); ?>" name="<?php echo $this->get_field_name('password'); ?>" value="<?php if ( isset( $instance['password'] ) ) echo esc_attr( $instance['password'] ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('login'); ?>"><?php _e( 'Log-In Button Text', 'gmember' ); ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id('login'); ?>" name="<?php echo $this->get_field_name('login'); ?>" value="<?php if ( isset( $instance['login'] ) ) echo esc_attr( $instance['login'] ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('profile'); ?>"><?php _e( 'Logged-in Title', 'gmember' ); ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id('profile'); ?>" name="<?php echo $this->get_field_name('profile'); ?>" value="<?php if ( isset( $instance['profile'] ) ) echo esc_attr( $instance['profile'] ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('logout'); ?>"><?php _e( 'Log-Out Button Text', 'gmember' ); ?></label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id('logout'); ?>" name="<?php echo $this->get_field_name('logout'); ?>" value="<?php if ( isset( $instance['logout'] ) ) echo esc_attr( $instance['logout'] ); ?>" /></p>

		<?php
	}
}


// http://natko.com/wordpress-ajax-login-without-a-plugin-the-right-way/

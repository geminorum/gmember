<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberMail extends gPluginModuleCore
{

	public function setup_actions()
	{
		add_filter( 'wp_mail', array( &$this, 'wp_mail' ) );

		add_filter( 'wp_mail_content_type', function( $content_type ){
			return 'text/html';
		} );

		add_filter( 'wp_mail_charset', function(){
			return 'UTF-8';
		} );
	}

	public function wp_mail( $mail )
	{
		// 'to', 'subject', 'message', 'headers', 'attachments'
		$mail['message'] = $this->wrap( $mail['message'] );

		return $mail;
	}

	public function wrap( $message, $context = NULL )
	{
		return $message;
	}

	/*
	List of mail related pluggable functions or filters

	Core:
		wp_new_user_notification --> SEE: gNetworkNotify
		wp_password_change_notification --> SEE: gNetworkNotify


	MS:
		wpmu_welcome_notification
		wp_new_blog_notification


	BP:
		bp_activity_at_message_notification
		bp_activity_new_comment_notification

	**/
}

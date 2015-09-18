<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberAvatar extends gPluginModuleCore
{
	// DRAFT
	public static function get( $id, $link = TRUE )
	{
		$html = get_avatar( $id, 40, '', 'avatar' );

		if ( ! $link )
			return $html;

		if ( function_exists( 'bp_core_get_userlink' ) )
			$html = '<a href="'.bp_core_get_user_domain( $id ).'" title="'.bp_core_get_user_displayname( $id ).'">'.$html.'</a>';

		else if ( $user = get_userdata( $id ) )
			$html = '<a href="#" title="'.esc_attr( $user->display_name ).'">'.$html.'</a>'; // FIXME: get user URL

		return $html;
	}
}

<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberImport extends gPluginImportCore
{

	public function network_settings_html( $settings_uri, $sub )
	{
		echo '<form method="post" action="">';

			$attachment_id = self::selectAttachment( gPluginFileHelper::mime( 'csv' ) );
			if ( $attachment_id ) {
				$file_path = gPluginWPHelper::get_attachmnet_path( $attachment_id );

				$data = self::getCSV( $file_path );
				// self::displayCSV( $data['data'], $data['titles'], 'google_contacts' );

				// gnetwork_dump( $data['titles'] );
				gnetwork_dump( $data['data'][186] );
			}

			submit_button();
		echo '</form>';
	}

	public static function getCSV( $file_path, $limit = false, $offset = false )
	{
		if ( file_exists( $file_path ) && is_readable( $file_path ) ) {

			if ( ! class_exists( 'parseCSV' ) )
				require_once( GMEMBER_DIR.'/assets/libs/parsecsv-for-php/parsecsv.lib.php' );

			$csv = new parseCSV();
			$csv->encoding( 'UTF-16', 'UTF-8' );

			if ( $offset )
				$csv->offset = $offset;

			if ( $limit )
				$csv->limit = $limit;

			$csv->auto( $file_path );

			return array(
				'titles' => $csv->titles,
				'data'   => $csv->data,
			);
		}

		return false;

		return array(
			'titles' => array(),
			'data'   => array(),
		);
	}

	public static function displayCSV( $data, $columns = null, $vendor = 'wpdb' )
	{
		$map = self::getCSVMap( $vendor );
	}

	// http://codex.wordpress.org/wp_insert_user
	// http://codex.wordpress.org/wp_create_user

	public static function getCSVMap( $vendor = 'wpdb' )
	{
		return self::getCSVMap_google_contacts();
		return array();

	}

	public static function getCSVMap_google_contacts()
	{
		return array(
			'display_name' => 'Name',
		);
	}

	public static function getCSVMap_wpdb()
	{
		return array();
	}
}

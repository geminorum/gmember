<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gMemberSession
{
	private $session = array();
	private $prefix = 'gmember_';

	public function __construct()
	{
		gPluginSessionHelper::setup_actions();

		if ( empty( $this->session ) )
			add_action( 'plugins_loaded', array( $this, 'init' ), -1 );
		else
			add_action( 'init', array( $this, 'init' ), -1 );
	}

	public function init()
	{
		$this->session = gPluginSession::get_instance();
		return $this->session;
	}

	public function get_id()
	{
		return $this->session->get_id();
	}


	public function get( $key )
	{
		$key = $this->prefix.sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : FALSE;
	}

	public function set( $key, $value )
	{
		$key = $this->prefix.sanitize_key( $key );

		if ( is_array( $value ) )
			$this->session[ $key ] = serialize( $value );
		else
			$this->session[ $key ] = $value;

		return $this->session[ $key ];
	}

}

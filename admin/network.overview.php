<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( function_exists( 'gnetwork_update_notice' ) )
	gnetwork_update_notice( GMEMBER_FILE );

if ( function_exists( 'gnetwork_github_readme' ) )
	gnetwork_github_readme( 'geminorum/gmember' );

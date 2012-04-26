<?php

if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

delete_option( 'wpmandrill' );
delete_option( 'wpmandrill-test' );
delete_transient('mandrill-stats');
?>

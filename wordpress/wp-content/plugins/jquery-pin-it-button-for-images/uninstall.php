<?php


//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

//delete all added options
delete_option( 'jpibfi_selection_options' );
delete_option( 'jpibfi_visual_options' );
delete_option( 'jpibfi_advanced_options' );
delete_option( 'jpibfi_version' );

//delete added metadata from all posts
delete_post_meta_by_key( 'jpibfi_meta' );
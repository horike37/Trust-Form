<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function trust_form_delete_plugin() {

//	$posts = get_posts( array(
//		'numberposts' => -1,
//		'post_type' => 'trust-form',
//		'post_status' => 'any' ) );

//	foreach ( $posts as $post )
//		wp_delete_post( $post->ID, true );

}

trust_form_delete_plugin();

?>
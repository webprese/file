<?php
	/*
	Plugin Name: Redirect
	Plugin URI: http://www.pancak.es/plugins/redirect/
	Description: Simple redirection using Custom Fields.
	Author: Nick Berlette
	Version: 0.8
	Author URI: http://www.pancak.es/
	
	=== How to Use ===
	  1. On the page or post you wish to redirect from, open up the Custom Fields section
	  2. Type in 'redirect' for the key, and then any URL for the value
	  3. Press Add Field, save the post, and you're done!
	
	*/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
	add_action('get_header', 'redirect');
	function redirect () {
		global $post;
		if (is_page() || is_object($post)) {
			if (get_post_meta($post->ID, 'redirect', true)) {
				header('Location: ' . get_post_meta($post->ID, 'redirect', true));
			}
		}
	}
	// yeah, that's the whole plugin.
	// great, aint it?
?>
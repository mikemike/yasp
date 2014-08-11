<?php
/*
Plugin Name: Yet Another Stats Plugin
Version: 0.1-alpha
Description: YASP (Yet Another Stats Plugin) displays some useful numbers about your blog/website on your admin panel dashboard.
Author: Mike Griffiths (mikemike)
Author URI: http://www.mike-griffiths.co.uk
Plugin URI: http://www.mike-griffiths.co.uk
Text Domain: yasp
Domain Path: /languages
*/

/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function yasp_add_dashboard_widgets() {
	wp_add_dashboard_widget(
        'yasp_dashboard_widget',         // Widget slug.
        'YASP (Yet Another Stats Plugin)',         // Title.
        'yasp_dashboard_widget_function' // Display function.
    );	
}
add_action( 'wp_dashboard_setup', 'yasp_add_dashboard_widgets' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function yasp_dashboard_widget_function() {
	// Display whatever it is you want to show.
	echo "Posts: ". _yasp_get_num_published_posts( 'post' ) ."<br>\n";
	echo "Pages: ". _yasp_get_num_published_posts( 'page' ) ."<br>\n";
	echo "Approved comments: ". _yasp_get_num_comments( true ) ."<br>\n";
	echo "Unapproved comment: ". _yasp_get_num_comments( false ) ."<br>\n";
	echo "Active plugins: ". _yasp_get_active_plugins() ."<br>\n";
	echo "Users: ". _yasp_get_num_users() ."<br>\n";
	echo "Active Categories: ". count( get_categories() )."<br>\n";
} 

/**
 * Grabs total number of published posts from database
 *
 * @param string $post_type Post type of the count
 * @return int
 */
function _yasp_get_num_published_posts( $post_type = 'post' ) {
	global $wpdb;
	$count = $wpdb->get_row( 
		$wpdb->prepare( 
			"
			SELECT COUNT(*) as `counter`
			FROM $wpdb->posts
			WHERE post_status = 'publish' 
			AND post_type='%s'
			",
			$post_type
		)
	);
	return $count->counter;
}

/**
 * Grabs total number of approved comments from database
 *
 * @param bool $approved Return approved or not?
 * @return int
 */
function _yasp_get_num_comments( $approved = true ) {
	global $wpdb;
	if($approved){
		$approved_flag = 1;
	} else {
		$approved_flag = 0;
	}
	$count = $wpdb->get_row( 
		$wpdb->prepare( 
			"
			SELECT COUNT(*) as `counter`
			FROM $wpdb->comments
			WHERE comment_approved = %d
			",
			$approved_flag
		)
	);
	return $count->counter;
}

/**
 * Grabs total number of users from database
 *
 * @return int
 */
function _yasp_get_num_users() {
	global $wpdb;
	$count = $wpdb->get_row( 
		"
		SELECT COUNT(*) as `counter`
		FROM $wpdb->users
		"
	);
	return $count->counter;
}

/**
 * Get total number of active plugins
 *
 * @return int
 */
function _yasp_get_active_plugins() {
	global $wpdb;
	$active_plugins = $wpdb->get_row( 
		"
		SELECT * 
		FROM $wpdb->options 
		WHERE option_name = 'active_plugins';
		"
	);
	$active_array = unserialize( $active_plugins->option_value );
	$active_count = count( $active_array );
	return $active_count;
}
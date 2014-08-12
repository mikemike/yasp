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


// Load CSS
function yasp_enqueue_styles($hook) {
    if( 'index.php' != $hook )
        return;
    wp_register_style( 'custom_wp_admin_css', plugins_url( 'assets/css/yasp_dashboard.css' , __FILE__ ), false, '1.0.0' );
    wp_enqueue_style( 'custom_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'yasp_enqueue_styles' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function yasp_dashboard_widget_function() {

	// Display whatever it is you want to show.
	echo '<ul class="items">'."\n";

	// Get a list of post types
	$post_types = get_post_types( '', 'object' );
	$exclude = array(
		'attachment',
		'revision',
		'nav_menu_item'
	);
	foreach($post_types as $post_type){
		if(!in_array($post_type->name, $exclude)){
			echo "<li><a href=\"edit.php?post_type=" . $post_type->name . "\"><strong>" . _yasp_get_num_published_posts( $post_type->name ) . "</strong> " . $post_type->labels->name . "</a></li>\n";
		}
	}

	// Other stats
	echo "<li><a href=\"edit-comments.php?comment_status=approved\"><strong>" . _yasp_get_num_comments( true ) . "</strong> Approved comments</a></li>\n";
	echo "<li><a href=\"edit-comments.php?comment_status=moderated\"><strong>" . _yasp_get_num_comments( false ) . "</strong> Unapproved comments</a></li>\n";
	echo "<li><a href=\"plugins.php\"><strong>" . _yasp_get_active_plugins() . "</strong> Active plugins</a></li>\n";
	echo "<li><a href=\"users.php\"><strong>" . _yasp_get_num_users() . "</strong> Users</a></li>\n";
	echo "<li><a href=\"edit-tags.php?taxonomy=category\"><strong>" . count( get_categories() ). "</strong> Active Categories</a></li>\n";

	// Close the list
	echo "</ul>\n";
	echo '<div class="clear"></div>'."\n";
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
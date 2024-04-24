<?php
/*
Plugin Name: _The Dream Factory Projects
Description: Customize WordPress with powerful, professional and intuitive fields.
Version: 0.1
Author: Nathan Roberts
Author URI: https://nathanroberts.co.uk
Text Domain: dfprojects
*/

define('DFPROJECTS_PATH', plugin_dir_path(__FILE__));

add_action( 'init', function() {
	include_once( DFPROJECTS_PATH . 'acf/acf-fields.php' );
} );

require DFPROJECTS_PATH . '/class-dfp_gallery.php';

new DFP_Gallery();

// Create a post type called projects
function create_post_type_projects()
{
	register_post_type(
		'projects',
		array(
			'labels' => array(
				'name' => __('Projects'),
				'singular_name' => __('Project')
			),
			'public' => true,
			'has_archive' => true,
		)
	);
}
add_action('init', 'create_post_type_projects');

// Create a project type taxonomy
function register_tax_project_categories()
{

	$labels = [
		"name" => __("Categories", "Avada"),
		"singular_name" => __("Category", "Avada"),
	];


	$args = [
		"label" => __("Categories", "Avada"),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => ['slug' => 'project_categories', 'with_front' => true,],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "project_categories",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy("project_categories", ["projects"], $args);
}
add_action('init', 'register_tax_project_categories');

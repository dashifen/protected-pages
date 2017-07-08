<?php

namespace Dashifen\ProtectedPages\Backend;

use Dashifen\ProtectedPages\Includes\Controller;

class Backend {
	
	/**
	 * @var Controller
	 */
	protected $controller;
	
	/**
	 * ProtectedPagesAdmin constructor.
	 *
	 * The $controller parameter is technically optional, but should always
	 * be used except when this object is instantiated within the activator,
	 * deactivator, or uninstallation objects.
	 *
	 * @param Controller $controller
	 */
	public function __construct(Controller $controller = null) {
		$this->controller = $controller;
	}
	
	/**
	 * Enqueues the CSS styles for this plugin
	 *
	 * @return void
	 */
	public function enqueueStyles(): void {
		wp_enqueue_style($this->controller->getPluginName(), plugin_dir_url(__FILE__) . "css/protected-content-admin.css", [], filemtime(plugin_dir_path(__FILE__) . "css/protected-content-admin.css"), "all");
	}
	
	public function registerPostType(): void {
		$labels = [
			"singular_name"         => "Protected Page",
			"name_admin_bar"        => "Protected Page",
			"name"                  => "Protected Pages",
			"menu_name"             => "Protected Pages",
			"archives"              => "Protected Pages",
			"attributes"            => "Protected Page Attributes",
			"parent_item_colon"     => "Parent Page:",
			"all_items"             => "All Protected Pages",
			"add_new_item"          => "Add New Protected Page",
			"add_new"               => "Add New",
			"new_item"              => "New Protected Page",
			"edit_item"             => "Edit Protected Page",
			"update_item"           => "Update Protected Page",
			"view_item"             => "View Protected Page",
			"view_items"            => "View Protected Pages",
			"search_items"          => "Search Protected Pages",
			"not_found"             => "Not Found",
			"not_found_in_trash"    => "Not found in Trash",
			"featured_image"        => "Featured Image",
			"set_featured_image"    => "Set featured image",
			"remove_featured_image" => "Remove featured image",
			"use_featured_image"    => "Use as featured image",
			"insert_into_item"      => "Add to Protected Page",
			"uploaded_to_this_item" => "Uploaded to this Protected Page",
			"items_list"            => "Protected Pages list",
			"items_list_navigation" => "Protected Pages list navigation",
			"filter_items_list"     => "Filter Protected Pages list",
		];
		
		$rewrite = [
			"slug"       => "protected-pages",
			"with_front" => true,
			"pages"      => true,
			"feeds"      => true,
		];
		
		$args = [
			"labels"              => $labels,
			"rewrite"             => $rewrite,
			"capability_type"     => "page",
			"label"               => "Protected Page",
			"description"         => "Content that cannot be seen on this site but may be seen on others.",
			"menu_icon"           => "dashicons-lock",
			"supports"            => [
				"title",
				"editor",
				"excerpt",
				"author",
				"thumbnail",
				"revisions",
				"custom-fields",
				"page-attributes"
			],
			"hierarchical"        => false,
			"show_in_nav_menus"   => false,
			"public"              => true,
			"show_ui"             => true,
			"show_in_menu"        => true,
			"show_in_admin_bar"   => true,
			"can_export"          => true,
			"has_archive"         => true,
			"exclude_from_search" => true,
			"publicly_queryable"  => true,
			"show_in_rest"        => true,
			"menu_position"       => 20,
		];
		
		register_post_type($this->controller->getPostTypeSlug(), $args);
	}
}

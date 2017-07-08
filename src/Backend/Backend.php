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
		wp_enqueue_style($this->controller->getPluginName(), plugin_dir_url(__FILE__) . "css/protected-pages-backend.css", [], filemtime(plugin_dir_path(__FILE__) . "css/protected-pages-backend.css"), "all");
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
			"add_new"               => "Add New Prot. Page",
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
			"show_in_menu"        => false,
			"show_ui"             => true,
			"show_in_admin_bar"   => true,
			"can_export"          => true,
			"exclude_from_search" => true,
			"menu_position"       => 20,
			
			// one might think that, because we don't want our custom
			// posts to be visible on the frontend of this site, we would
			// set the next three flags to false.  but, when that's the
			// case, going to the archive or singular slug for these
			// types sometimes seems loads the index of the site and we
			// don't want that to happen.  so, we leave these true and
			// mess with templates in the Frontend object.
			
			"publicly_queryable"  => true,
			"has_archive"         => true,
			"public"              => true,
			
			// here's where the REST API magic happens.  the show_in_rest
			// flag indicates that this post type should be available via
			// the API, and the rest_base string indicates the route that
			// we want.  so, the API for these posts will be found at the
			// following endpoint:  /wp-json/wp/v2/protected-pages.
			
			"show_in_rest"        => true,
			"rest_base"           => "protected-pages",
		];
		
		register_post_type($this->controller->getPostTypeSlug(), $args);
	}
	
	/**
	 * Slightly alters the Pages menu of the Dashboard.
	 *
	 * @return void
	 */
	public function updatePageLabels(): void {
		$postType = get_post_type_object("page");
		$postType->labels->all_items = "All Unprotected Pages";
		$postType->labels->add_new = "Add New Page";
	}
	
	/**
	 * Adds post type to Pages menu.
	 *
	 * @return void
	 */
	public function addPostTypeToPagesMenu(): void {
		
		
		
		// in our registration of our post type above, we specify that
		// it should not be in the Dashboard's menu.  instead, we want
		// to add it as a submenu of the Pages menu.  this method does
		// that.
		
		$postTypeSlug = $this->controller->getPostTypeSlug();
		$postType = get_post_type_object($postTypeSlug);
		
		add_submenu_page(
			"edit.php?post_type=page",
			$postType->labels->name,
			$postType->labels->all_items,
			"edit_pages",
			"edit.php?post_type=" . $postTypeSlug
		);
		
		add_submenu_page(
			"edit.php?post_type=page",
			$postType->labels->name,
			$postType->labels->add_new,
			"edit_pages",
			"post-new.php?post_type=" . $postTypeSlug
		);
	}
}

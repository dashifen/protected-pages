<?php

namespace Dashifen\ProtectedPages\Backend;

use Dashifen\ProtectedPages\Includes\Controller;
use Dashifen\ProtectedPages\Includes\Activator;
use Dashifen\ProtectedPages\Includes\Deactivator;

class Backend {
	
	/**
	 * @var Controller
	 */
	protected $controller;
	
	/**
	 * ProtectedPagesAdmin constructor.
	 *
	 * @param Controller $controller
	 */
	public function __construct(Controller $controller) {
		$this->controller = $controller;
	}
	
	public function activate(): void {
		$activator = new Activator($this->controller);
		$activator->activate();
	}
	
	public function deactivate(): void {
		$deactivator = new Deactivator($this->controller);
		$deactivator->deactivate();
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
	
	/**
	 * Registers the Protector role
	 *
	 * @return void
	 */
	public function registerProtectorRole(): void {
		
		// the protector role represents a user's account who should
		// have access to the Protected Pages.  in fact, that's all it
		// gets.  the application account for this plugin uses this
		// role.
		
		add_role($this->controller->getProtectorRole(), "Protector", [
			"read_private_pages" => true,
		]);
	}
	
	/**
	 * Removes the Protector role from various <select> elements
	 *
	 * @param array $roles
	 *
	 * @return array
	 */
	public function removeProtectorFromRoleSelector($roles): array {
		
		// not much to say here - we remove the role from our list of
		// $roles that we added in the prior method.
		
		unset($roles[$this->controller->getProtectorRole()]);
		return $roles;
	}
	
	public function removeProtectorFromUserViews($views): array {
		
		// this method does the same thing as the prior one, but we leave
		// it separate in case we need to do something different later.
		// for now, we'll just call that one here.
		
		return $this->removeProtectorFromRoleSelector($views);
	}
	
	/**
	 * Adds the settings page for our plugin to the Dashboard menu
	 *
	 * @return void
	 */
	public function addPostTypeSettings(): void {
		$postTypeSlug = $this->controller->getPostTypeSlug();
		$postType = get_post_type_object($postTypeSlug);
		
		// we need to tell WordPress how to show our settings and
		// how to save them.  we could circumvent this by using the
		// WordPress Settings API, but I've not had much success
		// with it in the past.  so, for now, we'll do things this
		// way.
		
		$showCallback = [$this, "showPostTypeSettings"];
		$saveCallback = [$this, "savePostTypeSettings"];
		
		$hook = add_options_page(
			$postType->labels->singular_name . " Settings",
			$postType->labels->menu_name,
			"manage_options",
			$postTypeSlug,
			$showCallback
		);
		
		add_action("load-$hook", $saveCallback);
	}
	
	public function savePostTypeSettings() {
		$slug = $this->controller->getSettingsSlug();
		
		// if our settings were posted here and the referring nonce is
		// accurate, then we'll proceed.
		
		if (isset($_POST[$slug]) && check_admin_referer("save-$slug")) {
		
		}
	}
	
	public function showPostTypeSettings() {
		require_once(plugin_dir_path(__FILE__) . "partials/settings.php");
	}
}

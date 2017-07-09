<?php

namespace Dashifen\ProtectedPages\Backend;

use Dashifen\ProtectedPages\Includes\Activator;
use Dashifen\ProtectedPages\Includes\Controller;
use Dashifen\ProtectedPages\Includes\Deactivator;
use \WP_Error as WP_Error;
use \WP_User as WP_User;

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
				"page-attributes",
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
			
			"publicly_queryable" => true,
			"has_archive"        => true,
			"public"             => true,
			
			// here's where the REST API magic happens.  the show_in_rest
			// flag indicates that this post type should be available via
			// the API, and the rest_base string indicates the route that
			// we want.  so, the API for these posts will be found at the
			// following endpoint:  /wp-json/wp/v2/protected-pages.
			
			"show_in_rest" => true,
			"rest_base"    => "protected-pages",
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
	 * Removes the Protector role from the user list filtering links
	 *
	 * @param $views
	 *
	 * @return array
	 */
	public function removeProtectorFromUserViews($views): array {
		
		// this method actually has two purposes:  removing the Protector
		// from our view and reducing the count of All users by one (to
		// further hide evidence of the Protector).  the second purpose is
		// easier:  we simply unset it from the list.  for that, we can
		// use the prior method for the moment.
		
		$views = $this->removeProtectorFromRoleSelector($views);
		
		// we can use preg_replace_callback() to reduce the count of All
		// users.  our callback simply grabs the matched number within the
		// string for All users, decrements, and returns it.
		
		$views["all"] = preg_replace_callback("/(\d+)/",
			function($x) {
				return --$x[0];
			},
			$views["all"]);
		
		return $views;
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
	
	/**
	 * Alters WP_User_Query parameters to hide Protectors
	 *
	 * @param array $queryParameters
	 *
	 * @return array
	 */
	public function removeProtectorFromUserQueries(array $queryParameters): array {
		
		// the WP_User_Query object conveniently provides a role__not_in
		// parameter to exclude users from the results.  we can use this to
		// exclude Protectors.
		
		$queryParameters["role__not_in"] = [$this->controller->getProtectorRole()];
		return $queryParameters;
	}
	
	/**
	 * Checks to see if our Protector is logging in and stops them if so
	 *
	 * @param WP_User|WP_Error|null $user
	 * @param string                $username
	 *
	 * @return WP_User|WP_Error|null
	 */
	public function preventProtectorLogin($user, string $username) {
		
		// in here, we want to see if $username matches the name of our
		// Protector.  if so, we want to prevent this login.
		
		$pluginName = $this->controller->getPluginName();
		$protectorId = get_option($pluginName . "-protector", 0);
		
		if ($protectorId !== 0) {
			$protector = new WP_User($protectorId);
			
			// now, if our user's login name and the $username parameter
			// match, then it's out Protector trying to log in.  but, we
			// want to let it in if this is a REST request.  so, if they
			// match and the REST_REQUEST constant is not defined, then
			// we return an error; otherwise, we'll just let WordPress
			// handle things.
			
			if ($protector->user_login === $username && !defined("REST_REQUEST")) {
				
				// if we have a matching username, then we want to return
				// a WP_Error object which'll let the WordPress ecosystem
				// know that there's a problem.  we stole this error from
				// WordPress core.
				
				return new WP_Error('invalid_username',
					'<strong>ERROR</strong>: Invalid username.' .
					' <a href="' . wp_lostpassword_url() . '">' .
					'Lost your password?' .
					'</a>'
				);
			}
		}
		
		return $user;
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
			
			// the only thing we need to check on here is that we have
			// domains in the appropriate format if they've sent us any
			// authorized sites.  it's technically acceptable to send
			// zero sites if you want to cut off access to the Protected
			// Pages.
			
			$settings = $this->controller->getSettings();
			
			// the sites are sent here as a string separated by new lines.
			// we'll break that into an array so that we can look at each
			// of them individually.
			
			$errors = [];
			foreach ($settings as $setting => $value) {
				
				// we want to create three variable method names here:
				// a transformer to make any necessary changes to the
				// data sent to use by the visitor, a validator to be
				// sure it's correct, and a sanitizer to prepare it to
				// be saved in the database.
				
				$temp = ucfirst($setting);
				$transformer = "transform$temp";
				$validator = "validate$temp";
				$sanitizer = "sanitize$temp";
				
				// using variable method calls, we can call the above
				// prepared methods to process the visitor's entries.
				
				$value = $this->{$transformer}($value);
				
				if ($this->{$validator}($value)) {
					$settings[$setting] = $this->{$sanitizer}($value);
				} else {
					$errors[$setting] = true;
				}
			}
			
			// we have two methods below that'll help us show the visitor
			// what we discovered with our validators above.  the one we
			// call is based on whether or not we encountered problems.
			
			if (sizeof($errors) > 0) {
				$this->displayErrors($errors);
			} else {
				$this->displaySuccess();
			}
			
			// regardless of whether these settings are erroneous, we'll
			// save them in the database.  then, we expect the visitor to
			// fix them.  if they don't, then the Frontend object is smart
			// enough to ensure that nothing works.
			
			update_option($this->controller->getSettingsSlug(), $settings);
			unset($_POST);
		}
	}
	
	/**
	 * Displays admin notices about bad settings
	 *
	 * @param array $errors
	 *
	 * @return void
	 */
	private function displayErrors(array $errors): void {
		
		// at the moment, we don't actually need $errors because there's
		// only one field:  authorizedSites.  so, if we're here, then at
		// least one of their entries wasn't a URL.
		
		add_action("admin_notices", function() {
			
			echo <<< MESSAGE
				<div class="notice notice-error">
					<h3>Unable to Save Settings</hd>
					<p>At least one of the sites you entered below does
					not appear to be a valid URL.  Please double-check
					your entries, make the necessary changes, and click
					the button to save them again.</p>
				</div>
MESSAGE;
		
		});
	}
	
	/**
	 * Displays an admin notice about successfully saved settings
	 *
	 * @return void
	 */
	private function displaySuccess(): void {
		add_action("admin_notices", function() {
			
			echo <<< MESSAGE
				<div class="notice notice-success">
					<h3>Settings Saved</h3>
					<p>Your entries have been saved in the database, and
					they've been re-displayed below for your review.</p>
				</div>
MESSAGE;
		
		});
	}
	
	public function showPostTypeSettings() {
		
		// my IDE flags a warning if we try to require the our settings
		// page since it can't resolve the plugins_dir_path() function
		// call.  hence the use of the variable, which the IDE simply
		// ignores.
		
		$file = plugin_dir_path(__FILE__) . "partials/settings.php";
		require_once $file;
	}
	
	/**
	 * @param string $sites
	 *
	 * @return array
	 */
	private function transformAuthorizedSites(string $sites): array {
		
		// when the sites come to us, they're a \n separated string.
		// we want to convert them to an array.  first, we explode(),
		// but that leaves the \n at the end of each site.  we'll
		// get rid of those, too.  finally, we'll use array_filter()
		// to get rid of blanks.
		
		$sites = explode("\n", $sites);
		$sites = array_map("trim", $sites);
		return array_filter($sites);
	}
	
	/**
	 * Ensures that sites entered by visitor are valid URLs
	 *
	 * @param array $sites
	 *
	 * @return bool
	 */
	private function validateAuthorizedSites(array $sites): bool {
		
		// we'll assume that everything is okay until proven otherwise.
		// luckily, we can use the PHP filter_var() function to do most
		// of the work here.  notice that we skip empty sites; likely
		// it's a blank line at the end of the textarea if the visitor
		// hit enter after the last domain.
		
		foreach ($sites as $site) {
			if (!empty($site) && !filter_var($site, FILTER_VALIDATE_URL)) {
				
				// if even one URL is invalid, then the whole entry will
				// need some work.  so, we'll return false here.
				
				return false;
			}
		}
		
		// if we looped over all the sites and all of them validated,
		// then this entry is valid.  we can return true.
		
		return true;
	}
	
	/**
	 * Sanitizes visitor entry for saving in the database.
	 *
	 * @param array $sites
	 *
	 * @return array
	 */
	private function sanitizeAuthorizedSites(array $sites): array {
		
		// we already know that our $sites are valid URLs because the
		// prior method handles that for us.  here, we simply want to
		// ensure that all of our $sites are simply domains with
		// protocols, no file or query string or any of that
		// nonsense.
		
		foreach ($sites as &$site) {
			$site = vsprintf("%s://%s", parse_url($site));
			$site = sanitize_text_field($site);
		}
		
		return $sites;
	}
}

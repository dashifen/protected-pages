<?php

namespace Dashifen\ProtectedPages\Includes;

use Dashifen\ProtectedPages\Frontend\Frontend;
use Dashifen\ProtectedPages\Backend\Backend;

/**
 * Class ProtectedPages
 *
 * This object hooks our plugin into the WordPress actions and filters that
 * it requires to operate.
 *
 * @package Dashifen\ProtectedPages\Includes
 */
class Controller {
	
	/**
	 * @var Loader $loader
	 */
	protected $loader;
	
	/**
	 * @var string $pluginName
	 */
	protected $pluginName = "protected-pages";
	
	/**
	 * @var string $postTypeSlug
	 */
	protected $postTypeSlug = "protected-page";
	
	/**
	 * @var string $protectorRole
	 */
	protected $protectorRole = "protected-pages-protector";
	
	/**
	 * @var string $settingsSlug
	 */
	protected $settingsSlug = "protected-pages-settings";
	
	/**
	 * @var array $settingsDefault
	 */
	protected $settingsDefault = ["authorizedSites" => []];
	
	/**
	 * @var string $version
	 */
	protected $version = PROTECTED_PAGES_VERSION;
	
	/**
	 * ProtectedPages constructor.
	 *
	 * @param bool $init
	 */
	public function __construct(bool $init = true) {
		
		// most of the time, we do want to initialize the rest of our
		// plugin.  but, if we're using this object from within our
		// activator, deactivator, or uninstaller then we don't want
		// to do all that work when it's not necessary.
		
		if ($init) {
			$this->loader = new Loader();
			$this->defineFrontendHooks();
			$this->defineBackendHooks();
		}
	}
	
	/**
	 * Returns the settings for this plugin.
	 *
	 * @return array
	 */
	public function getSettings(): array {
		
		// this method returns our settings -- either the settings in the
		// database or a new set of settings that have been posted to the
		// server.
		
		$settings = !isset($_POST[$this->settingsSlug])
			? get_option($this->settingsSlug, [])
			: $_POST[$this->settingsSlug];
		
		// now, we want to ensure that we have, at minimum, the default
		// settings listed above.  this ensures that we can operate the
		// plugin without errors during the initial setup of its behaviors.
		
		$settings = wp_parse_args($settings, $this->settingsDefault);
		return $settings;
	}
	
	/**
	 * Defines the administrative hooks for this plugin
	 *
	 * @return void
	 */
	private function defineBackendHooks(): void {
		$backend = new Backend($this);
		
		$plugin = sprintf("%s/%s.php", $this->pluginName, $this->pluginName);
		
		$this->loader->addAction("activate_$plugin", $backend, "activate");
		$this->loader->addAction("deactivate_$plugin", $backend, "deactivate");
		$this->loader->addAction("admin_enqueue_scripts", $backend, "enqueueStyles");
		
		// these actions register our post type and manipulate the Dashboard
		// Pages menu to include it.
		
		$this->loader->addAction("init", $backend, "registerPostType");
		$this->loader->addAction("init", $backend, "updatePageLabels");
		$this->loader->addAction("admin_menu", $backend, "addPostTypeToPagesMenu");
		
		// now, we want to create the Protector role and mess around with
		// some other features within the Users menu item to be sure that
		// there's one and only one such user performing that Role.
		
		$this->loader->addAction("init", $backend, "registerProtectorRole");
		$this->loader->addFilter("editable_roles", $backend, "removeProtectorFromRoleSelector");
		$this->loader->addFilter("views_users", $backend, "removeProtectorFromUserViews");
		
		// finally, we'll need a settings page for our plugin.  this is
		// where we specify the list of other URLs from which we can get
		// Protected Pages as well as display the application account
		// information that should be used when doing so.
		
		$this->loader->addAction("admin_menu", $backend, "addPostTypeSettings");
	}
	
	/**
	 * Defines the public hooks for this plugin
	 *
	 * @return void
	 */
	private function defineFrontendHooks(): void {
		$frontend = new Frontend($this);
		
		$this->loader->addFilter("template_include", $frontend, "preventProtectedAccess");
		$this->loader->addFilter("rest_prepare_{$this->postTypeSlug}", $frontend, "confirmPostTypeAccess", 10, 3);
	}
	
	/**
	 * @return string
	 */
	public function getPluginName(): string {
		return $this->pluginName;
	}
	
	/**
	 * @return string
	 */
	public function getPostTypeSlug(): string {
		return $this->postTypeSlug;
	}
	
	/**
	 * @return string
	 */
	public function getProtectorRole(): string {
		return $this->protectorRole;
	}
	
	/**
	 * @return string
	 */
	public function getSettingsSlug(): string {
		return $this->settingsSlug;
	}
	
	/**
	 * @return string
	 */
	public function getVersion(): string {
		return $this->version;
	}
	
	/**
	 * Executes our loader"s run method which connects our plugin to WordPress
	 *
	 * @return void
	 */
	public function run(): void {
		$this->loader->run();
	}
}

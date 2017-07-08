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
	 * @var string $version
	 */
	protected $version = PROTECTED_PAGES_VERSION;
	
	/**
	 * ProtectedPages constructor.
	 */
	public function __construct() {
		$this->loader = new Loader();
		$this->defineFrontendHooks();
		$this->defineBackendHooks();
	}
	
	/**
	 * Defines the administrative hooks for this plugin
	 *
	 * @return void
	 */
	private function defineBackendHooks(): void {
		$backend = new Backend($this);
		
		$this->loader->addAction("init", $backend, "registerPostType");
		$this->loader->addAction("admin_enqueue_scripts", $backend, "enqueueStyles");
	}
	
	/**
	 * Defines the public hooks for this plugin
	 *
	 * @return void
	 */
	private function defineFrontendHooks(): void {
		$frontend = new Frontend($this);
		
		$this->loader->addFilter("template_include", $frontend, "preventProtectedAccess");
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

<?php

namespace Dashifen\ProtectedPages\Includes;

use Dashifen\ProtectedPages\Backend\Activator\Activator;
use Dashifen\ProtectedPages\Backend\Backend;
use Dashifen\ProtectedPages\Backend\Deactivator\Deactivator;
use Dashifen\ProtectedPages\Backend\Uninstaller\Uninstaller;
use Dashifen\ProtectedPages\Frontend\Frontend;
use Dashifen\WPPB\Component\Backend\BackendInterface;
use Dashifen\WPPB\Component\ComponentInterface;
use Dashifen\WPPB\Controller\AbstractController;

class Controller extends AbstractController {
	/**
	 * @var BackendInterface $backend
	 */
	protected $backend;
	
	/**
	 * @var ComponentInterface $frontend
	 */
	protected $frontend;
	
	public function getName(): string {
		return "Protected Pages";
	}
	
	public function getSettingsSlug(): string {
		return "protected-pages-settings";
	}
	
	protected function getDefaultSettings(): array {
		
		// at this time, the only default setting for this plugin is
		// the fact that the authorized sites setting must be an array
		// but it starts out blank.  therefore:
		
		return [
			"authorizedSites" => [],
		];
		
	}
	
	protected function defineBackendHooks(): void {
		$backend = $this->getBackend();
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
		$this->loader->addFilter("users_list_table_query_args", $backend, "removeProtectorFromUserQueries");
		$this->loader->addFilter("authenticate", $backend, "preventProtectorLogin", 100, 2);
		
		// finally, we'll need a settings page for our plugin.  this is
		// where we specify the list of other URLs from which we can get
		// Protected Pages as well as display the application account
		// information that should be used when doing so.
		
		$this->loader->addAction("admin_menu", $backend, "addPostTypeSettings");
	}
	
	public function getBackend(): BackendInterface {
		
		// this is just like the prior method, but we use our Backend
		// object which is a little more complex in its construction.
		// otherwise, it's basically the same as above.
		
		if (is_null($this->backend)) {
			$this->backend = new Backend($this, new Activator($this),
				new Deactivator($this), new Uninstaller($this));
		}
		
		return $this->backend;
	}
	
	public function getFilename(): string {
		
		// we made sure that the sanitized name of our plugin matches the
		// folder and index filename for it.  so, we can return the plugin's
		// filename as follows:
		
		$pluginName = $this->getSanitizedName();
		return sprintf("%s/%s.php", $pluginName, $pluginName);
	}
	
	protected function defineFrontendHooks(): void {
		
		/** @var Backend $backend */
		
		$backend = $this->getBackend();
		$frontend = $this->getFrontend();
		$postTypeSlug = $backend->getPostTypeSlug();
		
		$this->loader->addFilter("template_include", $frontend, "preventProtectedAccess");
		$this->loader->addFilter("rest_prepare_$postTypeSlug", $frontend, "confirmPostTypeAccess", 10, 3);
	}
	
	public function getFrontend(): ComponentInterface {
		
		// if our frontend property has not yet been instantiated, then
		// we do so here and return it.  otherwise, we just return the
		// previously instantiated object.
		
		if (is_null($this->frontend)) {
			$this->frontend = new Frontend($this);
		}
		
		return $this->frontend;
	}
}

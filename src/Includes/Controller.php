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
use Dashifen\WPPB\Controller\ControllerException;
use Dashifen\WPPB\Controller\ControllerTraits\PostStatusesTrait;
use Dashifen\WPPB\Controller\ControllerTraits\RolesTrait;

class Controller extends AbstractController {
	use RolesTrait, PostStatusesTrait;
	
	/**
	 * @var BackendInterface $backend
	 */
	protected $backend;
	
	/**
	 * @var ComponentInterface $frontend
	 */
	protected $frontend;
	
	/**
	 * @return string
	 */
	public function getName(): string {
		return "Protected Pages";
	}
	
	/**
	 * @return string
	 */
	public function getSettingsSlug(): string {
		return "protected-pages-settings";
	}
	
	/**
	 * @return array
	 */
	public function getRoleSlugs(): array {
		return ["protector"];
	}
	
	/**
	 * @param string $role
	 *
	 * @return string
	 * @throws ControllerException
	 */
	public function getRoleName(string $role): string {
		$names = $this->getRoleNames();
		if (!in_array($role, array_keys($names))) {
			throw new ControllerException("Unknown role: $role.");
		}
		
		return $names[$role];
	}
	
	/**
	 * @return array
	 */
	public function getRoleNames(): array {
		return ["protector" => "Protector"];
	}
	
	/**
	 * @param string|null $role
	 *
	 * @return array
	 * @throws ControllerException
	 */
	public function getRoleCapabilities(string $role = null): array {
		$caps = [
			"protector" => [
				"read_protected_pages" => true,
			],
		];
		
		// if we don't have a $role, we just return all capabilities
		// and let the calling scope figure it out.
		
		if (is_null($role)) {
			return $caps;
		}
		
		// otherwise, we want to double-check that $role is actually a
		// legitimate one for this plugin and, if so, we return its
		// capabilities specifically.  otherwise, we throw a tantrum.
		
		if (!in_array($role, array_keys($caps))) {
			throw new ControllerException("Unknown role: $role.");
		}
		
		return $caps[$role];
	}
	
	/**
	 * @return array
	 */
	public function getPostStatuses(): array {
		return ["protected"];
	}
	
	/**
	 * @return string
	 */
	public function getFilename(): string {
		
		// we made sure that the sanitized name of our plugin matches the
		// folder and index filename for it.  so, we can return the plugin's
		// filename as follows:
		
		$pluginName = $this->getSanitizedName();
		return sprintf("%s/%s.php", $pluginName, $pluginName);
	}
	
	/**
	 * @return array
	 */
	protected function getDefaultSettings(): array {
		
		// at this time, the only default setting for this plugin is
		// the fact that the authorized sites setting must be an array
		// but it starts out blank.  therefore:
		
		return [
			"authorizedSites" => [],
		];
		
	}
	
	/**
	 * @param string|null $status
	 *
	 * @return array
	 * @throws ControllerException
	 */
	protected function getPostStatusArguments(string $status = null): array {
		$args = [
			"protected" => [
				"label"       => "Protected",
				"label_count" => _n_noop('Protected <span class="count">(%s)</span>', 'Protected <span class="count">(%s)</span>'),
				"public"      => false,
				"internal"    => true,
			],
		];
		
		// like the method to get role capabilities above, if we don't
		// have a specified $status, then we're happy to return the $args
		// array entirely.
		
		if (is_null($status)) {
			return $args;
		}
		
		// otherwise, as long as $status is a key in $args, we'll return
		// those data without the rest.  if $status is unknown, we throw
		// a tantrum.
		
		if (!in_array($status, array_keys($args))) {
			throw new ControllerException("Unknown status: $status.");
		}
		
		return $args[$status];
	}
	
	/**
	 * @return void
	 */
	protected function defineBackendHooks(): void {
		$backend = $this->getBackend();
		$this->loader->addAction("admin_enqueue_scripts", $backend, "enqueueScripts");
		
		// now, we want to mess around with some of the ways that the
		// Protector role we described above would be displayed within
		// WordPress core.
		
		$this->loader->addFilter("editable_roles", $backend, "removeProtectorFromRoleSelector");
		$this->loader->addFilter("views_users", $backend, "removeProtectorFromUserViews");
		$this->loader->addFilter("users_list_table_query_args", $backend, "removeProtectorFromUserQueries");
		$this->loader->addFilter("authenticate", $backend, "preventProtectorLogin", 100, 2);
		
		// finally, we'll need a settings page for our plugin.  this is
		// where we specify the list of other URLs from which we can get
		// Protected Pages as well as display the application account
		// information that should be used when doing so.
		
		$this->loader->addAction("admin_menu", $backend, "addPluginSettings");
	}
	
	/**
	 * @return BackendInterface
	 */
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
	
	/**
	 * @return void
	 */
	protected function defineFrontendHooks(): void {
		$frontend = $this->getFrontend();
		$this->loader->addFilter("template_include", $frontend, "preventProtectedAccess");
//		$this->loader->addFilter("rest_prepare_$postTypeSlug", $frontend, "confirmPostTypeAccess", 10, 3);
	}
	
	/**
	 * @return ComponentInterface
	 */
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

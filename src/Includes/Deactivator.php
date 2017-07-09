<?php

namespace Dashifen\ProtectedPages\Includes;

/**
 * Class Deactivator
 *
 * @package Dashifen\ProtectedPages\Includes
 */
class Deactivator {
	/**
	 * @var Controller $controller
	 */
	protected $controller;
	
	/**
	 * Activator constructor.
	 *
	 * @param Controller $controller
	 */
	public function __construct(Controller $controller) {
		$this->controller = $controller;
	}
	
	/**
	 * the method called to execute behaviors needed to deactivate the plugin.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		
		// when deactivating our plugin, we want to remove the Protector
		// user from the site and then delete the options related to that
		// user's identity.
		
		$pluginName = $this->controller->getPluginName();
		$userId = get_option($pluginName . "-protector");
		
		if ($userId !== false) {
			wp_delete_user($userId);
		}
		
		remove_role($this->controller->getProtectorRole());
		delete_option($pluginName . "-protector-password");
		delete_option($pluginName . "-protector");
	}
}

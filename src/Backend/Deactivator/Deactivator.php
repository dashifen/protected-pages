<?php

namespace Dashifen\ProtectedPages\Backend\Deactivator;

use Dashifen\ProtectedPages\Backend\Backend;
use Dashifen\WPPB\Component\Backend\Deactivator\AbstractDeactivator;

/**
 * Class Deactivator
 *
 * @package Dashifen\ProtectedPages\Includes
 */
class Deactivator extends AbstractDeactivator {
	
	/**
	 * the method called to execute behaviors needed to deactivate the plugin.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		
		// when deactivating our plugin, we want to remove the Protector
		// user from the site and then delete the options related to that
		// user's identity.
		
		/** @var Backend $backend */
		
		$backend = $this->controller->getBackend();
		$pluginName = $this->controller->getSanitizedName();
		
		if (($userId = get_option($pluginName . "-protector")) !== false) {
			wp_delete_user($userId);
		}
		
		delete_option($pluginName . "-protector-password");
		delete_option($pluginName . "-protector");
		remove_role($backend->getRoleSlug());
	}
}

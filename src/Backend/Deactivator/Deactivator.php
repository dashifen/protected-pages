<?php

namespace Dashifen\ProtectedPages\Backend\Deactivator;

use Dashifen\ProtectedPages\Backend\Backend;
use Dashifen\WPPB\Component\Backend\Deactivator\AbstractDeactivator;
use Dashifen\WPPB\Controller\ControllerException;

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
		$this->removeProtector();
	}
	
	/**
	 * @return void
	 * @throws ControllerException
	 */
	private function removeProtector(): void {
		// when deactivating our plugin, we want to remove the Protector
		// user from the site and then delete the options related to that
		// user's identity.
		
		/** @var Backend $backend */
		
		if (!method_exists($this->controller, "getRoleSlugs")) {
			throw new ControllerException("Missing method: getRoleSlugs.",
				ControllerException::MISSING_METHOD);
		}
		
		$roles = $this->controller->getRoleSlugs();
		$pluginName = $this->controller->getSanitizedName();
		foreach ($roles as $role) {
			$protectorSetting = $pluginName . "-" . $role;
			
			if (($userId = get_option($protectorSetting)) !== false) {
				wp_delete_user($userId);
			}
			
			delete_option($protectorSetting . "-password");
			delete_option($protectorSetting);
			remove_role($role);
		}
	}
}

<?php

namespace Dashifen\ProtectedPages\Backend\Activator;

use Dashifen\WPPB\Component\Backend\Activator\AbstractActivator;
use Dashifen\WPPB\Controller\ControllerException;

/**
 * Class Activator
 *
 * @package Dashifen\ProtectedPages\Includes
 */
class Activator extends AbstractActivator {
	
	/**
	 * the method called to execute behaviors necessary on plugin activation.
	 *
	 * @return void
	 */
	public function activate(): void {
		$this->createProtector();
	}
	
	/**
	 * @return void
	 * @throws ControllerException
	 */
	private function createProtector(): void {
		if (!method_exists($this->controller, "getRoleSlugs")) {
			throw new ControllerException("Missing method: getRolesSlugs.",
				ControllerException::MISSING_METHOD);
		}
		
		$roles = $this->controller->getRoleSlugs();
		$pluginName = $this->controller->getSanitizedName();
		
		foreach ($roles as $role) {
			$password = wp_generate_password(28, true);
			
			$userId = wp_insert_user([
				"role"       => $role,
				"user_login" => sprintf("%s-%s-%d", $pluginName, $role, time()),
				"user_pass"  => $password,
			]);
			
			if (is_wp_error($userId)) {
				die($userId->get_error_message());
			}
			
			add_option($pluginName . "-" . $role . "-password", $password);
			add_option($pluginName . "-" . $role, $userId);
		}
	}
}

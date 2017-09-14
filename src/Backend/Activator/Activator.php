<?php

namespace Dashifen\ProtectedPages\Backend\Activator;

use Dashifen\ProtectedPages\Backend\Backend;
use Dashifen\WPPB\Component\Backend\Activator\AbstractActivator;

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
		$this->updatePermalinks();
		$this->createProtector();
	}
	
	/**
	 * @return void
	 */
	private function updatePermalinks(): void {
		
		/** @var Backend $plugin */
		
		$plugin = $this->controller->getBackend();
		$plugin->registerPostType();
		flush_rewrite_rules();
	}
	
	/**
	 * @return void
	 */
	private function createProtector(): void {
		/** @var Backend $backend */
		
		$backend = $this->controller->getBackend();
		$pluginName = $this->controller->getSanitizedName();
		$protectorRole = $backend->getRoleName();
		$password = wp_generate_password(28, true);
		
		$user = [
			"role"       => $protectorRole,
			"user_login" => $pluginName . "-gatekeeper",
			"user_pass"  => $password,
		];
		
		$userId = wp_insert_user($user);
		add_option($pluginName . "-protector-password", $password);
		add_option($pluginName . "-protector", $userId);
	}
}

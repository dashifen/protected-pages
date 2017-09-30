<?php

namespace Dashifen\ProtectedPages\Backend\Activator;

use Dashifen\ProtectedPages\Backend\Backend;
use Dashifen\WPPB\Component\Backend\Activator\AbstractActivator;
use Dashifen\WPPB\Component\Backend\BackendTraits\PostTypeTrait;

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
		
		$username = $pluginName . "-gatekeeper-" . time();
		
		$user = [
			"role"       => $protectorRole,
			"user_login" => $username,
			"user_pass"  => $password,
		];
		
		$userId = wp_insert_user($user);
		
		if (is_wp_error($userId)) {
			die($userId->get_error_message());
		}
		
		add_option($pluginName . "-protector-password", $password);
		add_option($pluginName . "-protector", $userId);
	}
}

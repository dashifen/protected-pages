<?php

namespace Dashifen\ProtectedPages\Includes;

use Dashifen\ProtectedPages\Backend\Backend;

/**
 * Class Activator
 *
 * @package Dashifen\ProtectedPages\Includes
 */
class Activator {
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
		$plugin = new Backend($this->controller);
		$plugin->registerPostType();
		flush_rewrite_rules();
	}
	
	/**
	 * @return void
	 */
	private function createProtector(): void {
		$pluginName = $this->controller->getPluginName();
		$protectorRole = $this->controller->getProtectorRole();
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

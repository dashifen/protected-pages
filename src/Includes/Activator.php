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
	 * the method called to execute behaviors necessary on plugin activation.
	 *
	 * @return void
	 */
	public function activate(): void {
		
		// during activation, the thing we need to do is register our post
		// type and then flush the rewrite rules.
		
		$plugin = new Backend();
		$plugin->registerPostType();
		flush_rewrite_rules();
	}
}

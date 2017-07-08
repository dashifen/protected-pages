<?php

namespace Dashifen\ProtectedPages\Includes;

use Dashifen\ProtectedPages\Frontend\Frontend;
use Dashifen\ProtectedPages\Backend\Backend;

/**
 * Class Loader
 *
 * @package Dashifen\ProtectedPages\Includes
 */
class Loader {
	
	/**
	 * @var Hook[]
	 */
	protected $actions = [];
	
	/**
	 * @var Hook[]
	 */
	protected $filters = [];
	
	/**
	 * Add a new action to the collection to be linked to WordPress.
	 *
	 * @param string           $hook
	 * @param Frontend|Backend $component
	 * @param string           $method
	 * @param int              $priority
	 * @param int              $acceptedArgs
	 *
	 * @return void
	 */
	public function addAction(string $hook, $component, string $method, int $priority = 10, int $acceptedArgs = 1): void {
		$this->actions[] = new Hook(...func_get_args());
	}
	
	/**
	 * Add a new filter to the collection to be linked to WordPress.
	 *
	 * @param string           $hook
	 * @param Frontend|Backend $component
	 * @param string           $method
	 * @param int              $priority
	 * @param int              $acceptedArgs
	 *
	 * @return void
	 */
	public function addFilter(string $hook, $component, string $method, int $priority = 10, int $acceptedArgs = 1): void {
		$this->filters[] = new Hook(...func_get_args());
	}
	
	/**
	 * Links our defined actions and filters to WordPress
	 *
	 * @return void
	 */
	public function run(): void {
		$functions = [
			"add_filter" => $this->filters,
			"add_action" => $this->actions,
		];
		
		foreach ($functions as $function => $hooks) {
			/** @var Hook[] $hooks */
			
			foreach ($hooks as $hook) {
				
				// for each of our hooks, we"ll call either add_filter()
				// or add_action() using a variable function.  this helps
				// avoid needing two loops that do the same thing here.
				
				$function(
					$hook->getHook(),
					$hook->getCallable(),
					$hook->getPriority(),
					$hook->getAcceptedArgs()
				);
			}
		}
	}
}

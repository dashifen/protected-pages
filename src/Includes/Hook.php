<?php

namespace Dashifen\ProtectedPages\Includes;

use Dashifen\ProtectedPages\Backend\Backend;
use Dashifen\ProtectedPages\Frontend\Frontend;
use Dashifen\ProtectedPages\Includes\Exception as ProtectedPagesException;

/**
 * Class Hook
 *
 * Encapsulates information relating to a WordPress action or filter
 * and the callback that should be executed within this plugin when it
 * is encountered.
 *
 * @package Dashifen\ProtectedPages\Includes
 */
class Hook {
	
	/**
	 * @var string
	 */
	protected $hook;
	
	/**
	 * @var callable
	 */
	protected $callable;
	
	/**
	 * @var int
	 */
	protected $priority;
	
	/**
	 * @var int
	 */
	protected $acceptedArgs;
	
	/**
	 * ProtectedPagesAction constructor.
	 *
	 * @param string   $hook
	 * @param          $component
	 * @param string   $method
	 * @param int      $priority
	 * @param int      $acceptedArgs
	 *
	 * @throws Exception
	 */
	public function __construct(string $hook, $component, string $method, int $priority = 10, int $acceptedArgs = 1) {
		
		// our $component must be either or public or admin object.  we"ll
		// test for those here and throw an exception if it"s not of those
		// types.
		
		$isPublic = $component instanceof Frontend;
		$isAdmin = $component instanceof Backend;
		
		if (!$isPublic && !$isAdmin) {
			throw new ProtectedPagesException(
				"ProtectedPagesHook requires instance of public or administrative plugin object.",
				Exception::UNKNOWN_PLUGIN_OBJECT
			);
		}
		
		// next, now that we know our $component is one of our objects, we
		// want to double-check that the $method is one of its methods and
		// that we can call it from this scope.  if we can"t, then WordPress
		// won"t be able to do so either.
		
		$callable = [$component, $method];
		
		if (!is_callable($callable)) {
			throw new ProtectedPagesException(
				get_class($component) . " does not contain method named $method.",
				Exception::UNKNOWN_METHOD
			);
		}
		
		$this->setHook($hook);
		$this->setCallable($callable);
		$this->setPriority($priority);
		$this->setAcceptedArgs($acceptedArgs);
	}
	
	/**
	 * @return string
	 */
	public function getHook(): string {
		return $this->hook;
	}
	
	/**
	 * @param string $hook
	 *
	 * @return void
	 */
	public function setHook(string $hook): void {
		$this->hook = $hook;
	}
	
	/**
	 * @return callable
	 */
	public function getCallable(): callable {
		return $this->callable;
	}
	
	/**
	 * @param callable $callable
	 *
	 * @return void
	 */
	public function setCallable(callable $callable): void {
		$this->callable = $callable;
	}
	
	/**
	 * @return int
	 */
	public function getPriority(): int {
		return $this->priority;
	}
	
	/**
	 * @param int $priority
	 *
	 * @return void
	 */
	public function setPriority(int $priority): void {
		$this->priority = $priority;
	}
	
	/**
	 * @return int
	 */
	public function getAcceptedArgs(): int {
		return $this->acceptedArgs;
	}
	
	/**
	 * @param int $acceptedArgs
	 *
	 * @return void
	 */
	public function setAcceptedArgs(int $acceptedArgs): void {
		$this->acceptedArgs = $acceptedArgs;
	}
}

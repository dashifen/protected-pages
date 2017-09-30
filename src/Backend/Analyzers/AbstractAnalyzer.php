<?php

namespace Dashifen\ProtectedPages\Backend\Analyzers;

use Dashifen\WPPB\Component\Backend\BackendInterface;

abstract class AbstractAnalyzer {
	/**
	 * @var BackendInterface $backend
	 */
	protected $backend;
	
	/**
	 * Analyzer constructor.
	 *
	 * @param BackendInterface $backend
	 */
	public function __construct(BackendInterface $backend) {
		$this->backend = $backend;
	}
	
	/**
	 * @param        $value
	 * @param string $method
	 *
	 * @return mixed
	 */
	protected function analyze($value, string $method) {
		return method_exists($this, $method)
			? $this->{$method}($value)
			: $value;
	}
}

<?php

namespace Dashifen\ProtectedPages\Backend\Analyzers\Transformer;

use Dashifen\ProtectedPages\Backend\Analyzers\AbstractAnalyzer;

class Transformer extends AbstractAnalyzer {
	/**
	 * @param mixed  $value
	 * @param string $method
	 *
	 * @return mixed
	 */
	public function transform($value, string $method) {
		return $this->analyze($value, $method);
	}
	
	/**
	 * @param string $sites
	 *
	 * @return array
	 */
	protected function authorizedSites(string $sites): array {
		
		// when the sites come to us, they're a \n separated string.
		// we want to convert them to an array.  first, we explode(),
		// but that leaves the \n at the end of each site.  we'll
		// get rid of those, too.  finally, we'll use array_filter()
		// to get rid of blanks.
		
		$sites = explode("\n", $sites);
		$sites = array_map("trim", $sites);
		return array_filter($sites);
	}
}

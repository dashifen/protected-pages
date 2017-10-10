<?php

namespace Dashifen\ProtectedPages\Backend\Analyzers\Validator;

use Dashifen\ProtectedPages\Backend\Analyzers\AbstractAnalyzer;

class Validator extends AbstractAnalyzer {
	/**
	 * @param mixed  $value
	 * @param string $method
	 *
	 * @return bool
	 */
	public function validate($value, string $method): bool {
		
		// unlike our other analyzers, we want to force this one
		// to return a Boolean value.  so, we'll cast the results
		// of our analysis as a bool, though as you can see from
		// the methods below, they should already be of that
		// type anyway; this is a just-in-case sort of thing.
		
		return (bool)$this->analyze($value, $method);
	}
	
	/**
	 * Ensures that sites entered by visitor are valid URLs
	 *
	 * @param array $sites
	 *
	 * @return bool
	 */
	protected function authorizedSites(array $sites): bool {
		
		// we'll assume that everything is okay until proven otherwise.
		// luckily, we can use the PHP filter_var() function to do most
		// of the work here.  notice that we skip empty sites; likely
		// it's a blank line at the end of the textarea if the visitor
		// hit enter after the last domain.
		
		foreach ($sites as $site) {
			if (!empty($site) && !filter_var($site, FILTER_VALIDATE_URL)) {
				
				// if even one URL is invalid, then the whole entry will
				// need some work.  so, we'll return false here.
				
				return false;
			}
		}
		
		// if we looped over all the sites and all of them validated,
		// then this entry is valid.  we can return true.
		
		return true;
	}
}

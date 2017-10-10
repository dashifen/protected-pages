<?php

namespace Dashifen\ProtectedPages\Backend\Analyzers\Sanitizer;

use Dashifen\ProtectedPages\Backend\Analyzers\AbstractAnalyzer;

class Sanitizer extends AbstractAnalyzer {
	/**
	 * @param mixed  $value
	 * @param string $method
	 *
	 * @return mixed
	 */
	public function sanitize($value, string $method) {
		return $this->analyze($value, $method);
	}
	
	/**
	 * Sanitizes visitor entry for saving in the database.
	 *
	 * @param array $sites
	 *
	 * @return array
	 */
	protected function authorizedSites(array $sites): array {
		
		// we already know that our $sites are valid URLs because the
		// prior method handles that for us.  here, we simply want to
		// ensure that all of our $sites are simply domains with
		// protocols, no file or query string or any of that
		// nonsense.
		
		foreach ($sites as &$site) {
			$site = vsprintf("%s://%s", parse_url($site));
			$site = sanitize_text_field($site);
		}
		
		return $sites;
	}
}

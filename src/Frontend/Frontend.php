<?php

namespace Dashifen\ProtectedPages\Frontend;

use Dashifen\ProtectedPages\Includes\Controller;

class Frontend {
	
	/**
	 * @var Controller
	 */
	protected $controller;
	
	/**
	 * ProtectedPagesPublic constructor.
	 *
	 * The $controller parameter is technically optional, but should always
	 * be used except when this object is instantiated within the activator,
	 * deactivator, or uninstallation objects.
	 *
	 * @param Controller $controller
	 */
	public function __construct(Controller $controller = null) {
		$this->controller = $controller;
	}
	
	/**
	 * @param string $template
	 *
	 * @return string
	 */
	public function preventProtectedAccess(string $template): string {
		$postType = $this->controller->getPostTypeSlug();
		
		// to truly make this content "protected" (for our definition of
		// protected) we need to be sure that it cannot be displayed by
		// WordPress on this site, only via the sites specified in the
		// plugin"s settings.  so, if we"re visiting the archive of or
		// a singular post without our type, we switch the template for
		// the 404.  NOTE: this does not prevent the API from displaying
		// our protected content.
		
		if (is_post_type_archive($postType) || is_singular($postType)) {
			$template = locate_template("404.php");
		}
		
		return $template;
	}
}

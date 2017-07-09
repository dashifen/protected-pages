<?php

namespace Dashifen\ProtectedPages\Frontend;

use Dashifen\ProtectedPages\Includes\Controller;
use WP_Error as WP_Error;
use WP_Post as WP_Post;
use WP_REST_Request as WP_REST_Request;
use WP_REST_Response as WP_REST_Response;
use WP_User as WP_User;

class Frontend {
	
	/**
	 * @var Controller
	 */
	protected $controller;
	
	/**
	 * ProtectedPagesPublic constructor.
	 *
	 * @param Controller $controller
	 */
	public function __construct(Controller $controller) {
		$this->controller = $controller;
	}
	
	/**
	 * @param string $template
	 *
	 * @return string
	 */
	public function preventProtectedAccess(string $template): string {
		
		// to truly make this content "protected" (for our definition of
		// protected) we need to be sure that it cannot be displayed by
		// WordPress on this site, only via the sites specified in the
		// plugin"s settings.  so, if we"re visiting the archive of or
		// a singular post without our type, we switch the template for
		// the 404.  NOTE: this does not prevent the API from displaying
		// our protected content.
		
		if ($this->isProtected()) {
			$template = locate_template("404.php");
		}
		
		return $template;
	}
	
	private function isProtected() {
		
		// to identify whether or not this is a protected page (or the
		// archive thereof) we can simply grab the post type slug from
		// our Controller and then use the appropriate WordPress functions
		// that determine what sort of query has been performed.
		
		$postType = $this->controller->getPostTypeSlug();
		return is_post_type_archive($postType) || is_singular($postType);
	}
	
	/**
	 * @param WP_REST_Response $response
	 * @param WP_Post          $post
	 * @param WP_REST_Request  $request
	 *
	 * @return WP_REST_Response
	 */
	public function confirmPostTypeAccess(WP_REST_Response $response, WP_Post $post, WP_REST_Request $request) {
		
		// this method filters our $response parameter creating an error
		// response when either (a) no one is logged in or (b) when the person
		// who is logged in cannot read private pages.
		
		// TODO: create custom capability for reading protected pages
		
		if (!is_user_logged_in() || !current_user_can("read_private_pages")) {
			
			// if we're in here, then we need to send an error response.
			// ordinarily, we'd send a 401 Unauthorized message in this sort
			// of situation, but we're pretending the protected pages don't
			// exist when someone shouldn't know about them.  therefore, we
			// send a 404 Not Found error here.
			
			$response = new WP_REST_Response([
				"code"    => "rest_cannot_read",
				"message" => "Sorry, we could not find that post.",
				"data"    => [
					"status" => 404,
				],
			]);
		}
		
		return $response;
	}
}

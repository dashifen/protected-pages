<?php

namespace Dashifen\ProtectedPages\Frontend;

use Dashifen\WPPB\Component\AbstractComponent;
use WP_Post as WP_Post;
use WP_REST_Request as WP_REST_Request;
use WP_REST_Response as WP_REST_Response;

class Frontend extends AbstractComponent {
	
	/**
	 * @param string $template
	 *
	 * @return string
	 */
	protected function preventProtectedAccess(string $template): string {
		/**
		 * get_queried_object() doesn't always return a WP_Post, but we'll
		 * test it using instanceof to confirm.
		 *
		 * @var WP_Post $post
		 */
		$post = get_queried_object();
		
		if ($post instanceof WP_Post
			&& $post->post_type === "page"
			&& $this->isProtected($post)
		) {
			
			// if we're in here, then our $post is a WP_Post object,
			// it's post type is "page," and it's to be protected.  so,
			// we want to switch our template to the 404 file so that
			// it doesn't appear to exist on this site.  the REST API
			// is how we access such things.
			
			$template = locate_template("404.php");
		}
		
		return $template;
	}
	
	/**
	 * @param WP_Post|null $post
	 *
	 * @return bool
	 */
	private function isProtected(WP_Post $post): bool {
		
		// to know if a post is protected, we access its _protected
		// meta value.  if that's truth-y, then it's protected.  we
		// actually store 1 and 0 in the database for the meta value
		// so we cast it to a Boolean as we return.
		
		$protected = get_post_meta($post->ID, "_protected", true);
		return (bool)$protected;
	}
	
	/**
	 * @param WP_REST_Response $response
	 * @param WP_Post          $post
	 * @param WP_REST_Request  $request
	 *
	 * @return WP_REST_Response
	 */
	protected function confirmPageAccess(
		WP_REST_Response $response,
		WP_Post $post,
		WP_REST_Request $request
	) {
		if ($this->isProtected($post)) {
			
			// this method filters our $response parameter creating an error
			// response when either (a) no one is logged in, (b) when the
			// person who is logged in cannot read protected pages, (c) when
			// our request isn't from an authorized domain.
			
			$userLoggedIn = is_user_logged_in();
			$userCanReadProtectedPages = current_user_can("read_protected_pages");
			$siteIsAuthorized = $this->isSiteAuthorized($request);
			
			
			if (!$userLoggedIn || !$userCanReadProtectedPages || !$siteIsAuthorized) {
				
				// if we're in here, then we need to send an error response.
				// ordinarily, we'd send a 401 Unauthorized message in this
				// sort of situation, but we're pretending the protected pages
				// don't exist when someone shouldn't know about them.
				// therefore, we send a 404 Not Found error here.
				
				$response = new WP_REST_Response([
					"code"    => "rest_cannot_read",
					"message" => "Sorry, we couldn't find that post.",
					"data"    => [
						"status"         => 404,
						/*"userLoggedIn"   => $userLoggedIn ? 1 : 0,
						"userCanRead"    => $userCanReadProtectedPages ? 1 : 0,
						"siteAuthorized" => $siteIsAuthorized ? 1 : 0,
						"request" => serialize($request),*/
					],
				]);
			}
		}
		
		return $response;
	}
	
	/**
	 * Given a request, determines if it's domain is authorized
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 */
	private function isSiteAuthorized(WP_REST_Request $request): bool {
		
		// checking to see if our request originated from an authorized
		// site is easy:  we get the Origin: header and see if it's in
		// the authorizedSites array in our settings.
		
		return in_array($request->get_header("Origin"),
			$this->controller->getSetting("authorizedSites"));
	}
}

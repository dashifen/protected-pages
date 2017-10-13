<?php

namespace Dashifen\ProtectedPages\Backend;

use Dashifen\ProtectedPages\Backend\Analyzers\Sanitizer\Sanitizer;
use Dashifen\ProtectedPages\Backend\Analyzers\Transformer\Transformer;
use Dashifen\ProtectedPages\Backend\Analyzers\Validator\Validator;
use Dashifen\WPPB\Component\Backend\AbstractBackend;
use Dashifen\WPPB\Component\Backend\Activator\ActivatorInterface;
use Dashifen\WPPB\Component\Backend\Deactivator\DeactivatorInterface;
use Dashifen\WPPB\Component\Backend\Uninstaller\UninstallerInterface;
use Dashifen\WPPB\Controller\ControllerException;
use Dashifen\WPPB\Controller\ControllerInterface;
use Dashifen\WPPB\Loader\Hook\Hook;
use WP_Error as WP_Error;
use WP_Query as WP_Query;
use WP_Post as WP_Post;
use WP_User as WP_User;

class Backend extends AbstractBackend {
	/**
	 * @var string $pluginUrl
	 */
	protected $pluginUrl;
	
	/**
	 * @var string $pluginPath
	 */
	protected $pluginPath;
	
	/**
	 * Backend constructor.
	 *
	 * @param ControllerInterface  $controller
	 * @param ActivatorInterface   $activator
	 * @param DeactivatorInterface $deactivator
	 * @param UninstallerInterface $uninstaller
	 */
	public function __construct(
		ControllerInterface $controller,
		ActivatorInterface $activator,
		DeactivatorInterface $deactivator,
		UninstallerInterface $uninstaller
	) {
		parent::__construct($controller, $activator, $deactivator, $uninstaller);
		$this->pluginPath = untrailingslashit(plugin_dir_path(__FILE__));
		$this->pluginUrl = untrailingslashit(plugin_dir_url(__FILE__));
	}
	
	/**
	 * @return void
	 */
	protected function addAdminJs() {
		$js = "/Assets/protected-pages.min.js";
		$timestamp = filemtime($this->pluginPath . $js);
		wp_enqueue_script("protected-pages", $this->pluginUrl . $js, ["jquery"], $timestamp);
	}
	
	/**
	 * Switches a page's status to protected when it should be but isn't.
	 *
	 * @param string  $newStatus
	 * @param string  $oldStatus
	 * @param WP_Post $post
	 */
	protected function updateProtectedPageStatus(string $newStatus, string $oldStatus, WP_Post $post) {
		
		// when page undergoes a state transition in the database, we want to
		// update it's _protected meta value if and only if the hidden input
		// we add to the DOM on the page editor is present.  if it is present,
		// we update that meta value to its value.  the check for the existence
		// of the field prevents it from being altered when someone uses the
		// Quick Editor to change a protected page's information, for example.
		
		if ($post->post_type === "page" && isset($_POST["hidden-protected-status"])) {
			update_post_meta($post->ID, "_protected", $_POST["hidden-protected-status"]);
		}
	}
	
	/**
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	protected function addHiddenProtectedField(WP_Post $post): void {
		$protected = get_post_meta($post->ID, "_protected", true);
		
		// this is an action, not a filter, so we can't return this new
		// input element.  instead, we echo it and it'll be added to the
		// DOM as a part of the publish meta box.
		
		$input = '<input type="hidden" name="hidden-protected-status"
			id="hidden-protected-status" value="%s">';
		
		echo sprintf($input, $protected);
	}
	
	/**
	 * @param array   $postStates
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	protected function filterPostStates(array $postStates, WP_Post $post): array {
		
		// if the post status is protected, we want the word "Protected" to
		// be displayed in the states for this post.  that way, it's clear
		// on screen what's protected and what's not.
		
		$protected = get_post_meta($post->ID, "_protected", true);
		
		if ($protected) {
			$postStates["protected-page"] = "Protected";
		}
		
		return $postStates;
	}
	
	/**
	 * @param array $views
	 *
	 * @return array
	 */
	protected function filterPageViews(array $views): array {
		
		// here we want to add a protected view to our list of the default
		// views which are passed here.  this is somewhat difficult since we
		// need to filter based on a meta value when loading protected posts
		// so the URL that we construct here is a little rough.  we can start,
		// however, with the "all" link and build onto it.
		
		$posts = get_posts([
			"meta_key"       => "_protected",
			"meta_value"     => 1,
			"posts_per_page" => -1,
			"post_type"      => "page",
		]);
		
		$views["protected"] = sprintf(
			'<a href="%s"> Protected <span class="count">(%d)</span></a>',
			"edit.php?post_type=page&protected=1",
			sizeof($posts)
		);
		
		return $views;
	}
	
	/**
	 * @param WP_Query $query
	 *
	 * @return void
	 */
	protected function alterPageQuery(WP_Query $query): void {
		if (get_current_screen()->id === "edit-page" && ($_GET["protected"] ?? false)) {
			
			// if we're in here, then we're on the listing of our pages
			// and the visitor has requested only the protected one.  we'll
			// slightly alter our $query parameter so that that's all they
			// receive.
			
			$query->set("meta_key", "_protected");
			$query->set("meta_value", 1);
		}
	}
	
	/**
	 * Removes the Protector role from the user list filtering links
	 *
	 * @param $views
	 *
	 * @return array
	 */
	protected function removeProtectorFromUserViews($views): array {
		
		// this method actually has two purposes:  removing the Protector
		// from our view and reducing the count of All users by one (to
		// further hide evidence of the Protector).  the second purpose is
		// easier:  we simply unset it from the list.  for that, we can
		// use the prior method for the moment.
		
		$views = $this->removeProtectorFromRoleSelector($views);
		
		// we can use preg_replace_callback() to reduce the count of All
		// users.  our callback simply grabs the matched number within the
		// string for All users, decrements, and returns it.
		
		$views["all"] = preg_replace_callback("/(\d+)/",
			function ($x) {
				return --$x[0];
			},
			$views["all"]);
		
		return $views;
	}
	
	/**
	 * Removes the Protector role from various <select> elements
	 *
	 * @param array $roles
	 *
	 * @return array
	 * @throws ControllerException
	 */
	protected function removeProtectorFromRoleSelector($roles): array {
		
		// not much to say here - we ues the controller to get the
		// slug for the role added by this plugin and then remove it
		// from $roles.  even though there's only one such role, we
		// get an array from getRoleSlugs() here.
		
		if (!method_exists($this->controller, "getRoleSlugs")) {
			throw new ControllerException("Missing method: getRoleSlugs",
				ControllerException::MISSING_METHOD);
		}
		
		$removeThese = $this->controller->getRoleSlugs();
		foreach ($removeThese as $removeThis) {
			unset($roles[$removeThis]);
		}
		
		return $roles;
	}
	
	/**
	 * Alters WP_User_Query parameters to hide Protectors
	 *
	 * @param array $queryParameters
	 *
	 * @return array
	 * @throws ControllerException
	 */
	protected function removeProtectorFromUserQueries(array $queryParameters): array {
		
		// the WP_User_Query object conveniently provides a role__not_in
		// parameter to exclude users from the results.  we can use this to
		// exclude Protectors.
		
		if (!method_exists($this->controller, "getRoleSlugs")) {
			throw new ControllerException("Missing method: getRoleSlugs",
				ControllerException::MISSING_METHOD);
		}
		
		$queryParameters["role__not_in"] = $this->controller->getRoleSlugs();
		return $queryParameters;
	}
	
	/**
	 * Checks to see if our Protector is logging in and stops them if so
	 *
	 * @param WP_User|WP_Error|null $user
	 * @param string                $username
	 *
	 * @return WP_User|WP_Error|null
	 */
	protected function preventProtectorLogin($user, string $username) {
		
		// in here, we want to see if $username matches the name of our
		// Protector.  if so, we want to prevent this login.
		
		$pluginName = $this->controller->getName();
		$protectorId = get_option($pluginName . "-protector", 0);
		
		if ($protectorId !== 0) {
			$protector = new WP_User($protectorId);
			
			// now, if our user's login name and the $username parameter
			// match, then it's out Protector trying to log in.  but, we
			// want to let it in if this is a REST request.  so, if they
			// match and the REST_REQUEST constant is not defined, then
			// we return an error; otherwise, we'll just let WordPress
			// handle things.
			
			if ($protector->user_login === $username && !defined("REST_REQUEST")) {
				
				// if we have a matching username, then we want to return
				// a WP_Error object which'll let the WordPress ecosystem
				// know that there's a problem.  we stole this error from
				// WordPress core.
				
				return new WP_Error('invalid_username',
					'<strong>ERROR</strong>: Invalid username.' .
					' <a href="' . wp_lostpassword_url() . '">' .
					'Lost your password?' .
					'</a>'
				);
			}
		}
		
		return $user;
	}
	
	/**
	 * Adds the settings page for our plugin to the Dashboard menu
	 *
	 * @return void
	 */
	protected function addPluginSettings(): void {
		$pluginName = $this->controller->getName();
		
		// we need to tell WordPress how to show our settings and
		// how to save them.  we could circumvent this by using the
		// WordPress Settings API, but I've not had much success
		// with it in the past.  so, for now, we'll do things this
		// way.
		
		$showHook = $this->getShowSettingsHook();
		$hook = add_options_page("$pluginName Settings", $pluginName,
			"manage_options", $this->controller->getSanitizedName(),
			[$this, $showHook->getHandler()]);
		
		// now that WordPress knows how to show our settings, we'd
		// better tell it how to save them.  this could all be avoided
		// by using the WP Settings API, but I can so very, very
		// rarely get it to work.
		
		$saveHook = $this->getSaveSettingsHook($hook);
		add_action("load-$hook", [$this, $saveHook->getHandler()]);
		
		// now, WordPress knows how to do what it needs, so we just have
		// to attach these hooks to our own internal list of expected
		// actions.
		
		$this->attachHook($showHook);
		$this->attachHook($saveHook);
	}
	
	/**
	 * @return Hook
	 */
	private function getShowSettingsHook(): Hook {
		
		// this method creates and returns a new Hook object that tells
		// WordPress and this object how to show our plugin settings.
		// in this case, our hook is somewhat hard to know.  because
		// we're using the add_options_page() function to hide the
		// actual hook used.  but, careful analysis of WP lets us know
		// that we can use the get_plugin_page_hookname() function to
		// identify it.
		
		$hook = get_plugin_page_hookname(
			$this->controller->getSanitizedName(),
			"options-general.php"
		);
		
		return new Hook($hook, $this, "showPluginSettings");
	}
	
	/**
	 * @param string $settingsHook
	 *
	 * @return Hook
	 */
	private function getSaveSettingsHook(string $settingsHook): Hook {
		
		// like the prior method, this one returns a Hook object that is
		// used to save our page settings.  this time, though, the WP hook
		// to which we attach our behavior is easy:  it gets passed here
		// from the calling scope!
		
		return new Hook($settingsHook, $this, "savePluginSettings");
	}
	
	protected function savePluginSettings() {
		$slug = $this->controller->getSettingsSlug();
		
		// if our settings were posted here and the referring nonce is
		// accurate, then we'll proceed.
		
		if (isset($_POST[$slug]) && check_admin_referer("save-$slug")) {
			
			// in a perfect world, we'd inject these dependencies into our
			// plugin, but this is not that world (at least, not at this
			// time).  so, we'll just instantiate our analyzers here and
			// use 'em below.
			
			$validator = new Validator($this);
			$sanitizer = new Sanitizer($this);
			$transformer = new Transformer($this);
			
			// the only thing we need to check on here is that we have
			// domains in the appropriate format if they've sent us any
			// authorized sites.  it's technically acceptable to send
			// zero sites if you want to cut off access to the Protected
			// Pages.
			
			$settings = $this->controller->getSettings();
			
			// the sites are sent here as a string separated by new lines.
			// we'll break that into an array so that we can look at each
			// of them individually.
			
			$errors = [];
			foreach ($settings as $setting => $value) {
				// using variable method calls, we can call the above
				// prepared analyzers to process the visitor's entries.
				
				$value = $transformer->transform($value, $setting);
				
				if ($validator->validate($value, $setting)) {
					$settings[$setting] = $sanitizer->sanitize($value, $setting);
				} else {
					$errors[$setting] = true;
				}
			}
			
			// we have two methods below that'll help us show the visitor
			// what we discovered with our validators above.  the one we
			// call is based on whether or not we encountered problems.
			
			if (sizeof($errors) === 0) {
				$this->displaySuccess();
			} else {
				$this->displayErrors();
			}
			
			// regardless of whether these settings are erroneous, we'll
			// save them in the database.  then, we expect the visitor to
			// fix them.  if they don't, then the Frontend object is smart
			// enough to ensure that nothing works.
			
			update_option($this->controller->getSettingsSlug(), $settings);
			unset($_POST);
		}
	}
	
	/**
	 * Displays an admin notice about successfully saved settings
	 *
	 * @return void
	 */
	private function displaySuccess(): void {
		add_action("admin_notices", function () {
			echo <<< MESSAGE
				<div class="notice notice-success">
					<h3>Settings Saved</h3>
					<p>Your entries have been saved in the database, and
					they've been re-displayed below for your review.</p>
				</div>
MESSAGE;
		});
	}
	
	/**
	 * Displays admin notices about bad settings
	 *
	 * @return void
	 */
	private function displayErrors(): void {
		add_action("admin_notices", function () {
			echo <<< MESSAGE
				<div class="notice notice-error">
					<h3>Unable to Save Settings</hd>
					<p>At least one of the sites you entered below does
					not appear to be a valid URL.  Please double-check
					your entries, make the necessary changes, and click
					the button to save them again.</p>
				</div>
MESSAGE;
		});
	}
	
	protected function showPluginSettings() {
		
		// my IDE flags a warning if we try to require the our settings
		// page since it can't resolve the plugins_dir_path() function
		// call.  hence the use of the variable, which the IDE simply
		// ignores.
		
		$file = plugin_dir_path(__FILE__) . "Settings.php";
		require_once $file;
	}
}

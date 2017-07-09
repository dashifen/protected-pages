<?php

// this file is included within the scope of the ../Backend.php object.
// therefore, it has access to the protected properties of that object,
// specifically the $controller object which we use below.

$settings = $this->controller->getSettings();
$pluginName = $this->controller->getPluginName();
$settingsSlug = $this->controller->getSettingsSlug();

// we'll want to display the username and password for our Protector
// below.  we can get the user information fairly easily, but the
// user_pass property of WP_User objects is encrypted.  luckily, we
// stored the plaintext version of our Protector's password in the
// database.  ideal? no; but if someone hacks the database to get to
// it, the content its protecting would likely be visible to them
// anyway.

$user = new \WP_User(get_option($pluginName . "-protector"));
$password = get_option($pluginName . "-protector-password");

// the list of authorized sites is supposed to be stored as an array
// in our database,  but the textarea below will want to show them as
// a string.  so, if it is an array, we'll join it into a string here
// separating domains by new lines.

$authorizedSites = is_array($settings["authorizedSites"])
	? join("\n", $settings["authorizedSites"])
	: $settings["authorizedSites"]; ?>

<div class="wrap">
	<h2>Protected Page Settings</h2>
	
	<p>To access Protected Pages, you need to specify the domains on
	which it's acceptable to share their content.  Enter those domains
	in the field below, one per line.  For example, if you wanted to
	make your Protected Pages available to Disney, you'd enter
	<code>http://www.disney.com</code>.</p>
	
	<form method="post">
		<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="authorizedSites">Authorized Sites</label>
				</th>
				<td>
					<textarea id="authorizedSites" name="<?= $settingsSlug ?>[authorizedSites]" rows="10" cols="60" aria-required="true" required><?= $authorizedSites ?></textarea>
				</td>
			</tr>
		</tbody>
		</table>
		
		<p class="submit">
			<button type="submit" class="button button-primary">
				Save Changes
			</button>
		</p>
		
		<?php wp_nonce_field("save-" . $settingsSlug) ?>
	</form>
	
	<h3>REST API Account Settings</h3>
	<p>When requesting Protected Pages via the REST API, you'll need to
	specify the following username and password.  Remember: if you want to
	protected this password, you must use HTTPS when transmitting it back
	to this site.  Otherwise, someone may be able to capture it during
	transmission and use it to access your Protected Pages.</p>
	
	<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">Username</th>
			<td><?= $user->user_login ?></td>
		</tr>
		<tr>
			<th scope="row">Password</th>
			<td><?= get_option($pluginName . "-protector-password", "foo"); ?></td>
		</tr>
	</tbody>
	</table>
</div>

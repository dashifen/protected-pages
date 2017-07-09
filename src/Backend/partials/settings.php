<?php

// this file is included within the scope of the ../Backend.php object.
// therefore, it has access to the protected properties of that object,
// specifically the $controller object which we use below.

$settings = $this->controller->getSettings();
$pluginName = $this->controller->getPluginName();
$user = new \WP_User(get_option($pluginName . "-protector"));
$password = get_option($pluginName . "-protector-password");

// the list of authorized sites is stored as an array in our database,
// but the textarea below will want to show them as a string.  so, we'll
// join that array into one here separating domains by new lines.

$authorizedSites = join("\n", $settings["authorizedSites"]); ?>

<div class="wrap">
	<h2>Protected Page Settings</h2>
	
	<p>To access Protected Pages, you need to specify the domains on which it's
	acceptable to share their content.  Enter those domains in the field below,
	one per line, and click the Save Changes button when you're done.</p>
	
	<form method="post">
	<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="authorizedSites">Authorized Sites</label>
			</th>
			<td>
				<textarea id="authorizedSites" name="authorizedSites" rows="10" cols="60" aria-required="true" required><?= $authorizedSites ?></textarea>
			</td>
		</tr>
	</tbody>
	</table>
	
	<p class="submit">
		<button type="submit" class="button button-primary">Save Changes</button>
	</p>
	
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

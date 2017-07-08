<?php

namespace Dashifen\ProtectedPages;

use Dashifen\ProtectedPages\Includes\Activator;
use Dashifen\ProtectedPages\Includes\Deactivator;
use Dashifen\ProtectedPages\Includes\Uninstaller;
use Dashifen\ProtectedPages\Includes\Controller;

// this is the file that actually controls our plugin.  first, we define
// our activation, deactivation, and uninstallation hooks.  each of these
// has an object in the Includes folder that encapsulates what happens
// when our plugin"s state changes with respect to the rest of the
// WordPress site.

register_activation_hook(__FILE__, function() {
	$activator = new Activator();
	$activator->activate();
});

register_deactivation_hook(__FILE__, function() {
	$deactivator = new Deactivator();
	$deactivator->deactivate();
});

register_uninstall_hook(__FILE__, function() {
	$uninstaller = new Uninstaller();
	$uninstaller->uninstall();
});

// now, we want to instantiate the controller object for our plugin.
// this object defines all of the actions and filters that we want to
// mess with, and then it"s run() method actually connects various
// plugin object methods as callbacks for those hooks.

$ProtectedPagesPlugin = new Controller();
$ProtectedPagesPlugin->run();
